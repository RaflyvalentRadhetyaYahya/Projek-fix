<?php

declare(strict_types=1);

namespace App\Auth;

use App\Database\Schema;
use App\Http\Redirect;
use App\Support\BaseUrl;
use App\Support\HttpClient;
use PDO;
use RuntimeException;
use Throwable;

final class GoogleOAuth
{
    /** @param array{client_id:string,client_secret:string,redirect_uri:string} $google */
    public function __construct(
        private readonly PDO $pdo,
        private readonly array $google,
    ) {
    }

    public function start(): never
    {
        if (empty($this->google['client_id'])) {
            http_response_code(500);
            echo 'Google OAuth belum dikonfigurasi. Isi GOOGLE_OAUTH_CLIENT_ID dan GOOGLE_OAUTH_CLIENT_SECRET.';
            exit;
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $redirectUri = $this->google['redirect_uri'] ?: (BaseUrl::fromGlobals() . '/auth/google_callback.php');

        $params = [
            'client_id' => $this->google['client_id'],
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        Redirect::to($authUrl);
    }

    public function handleCallback(): never
    {
        $error = $_GET['error'] ?? null;
        if ($error) {
            echo 'Login dibatalkan: ' . htmlspecialchars((string)$error);
            echo '<br><a href="' . htmlspecialchars(BaseUrl::fromGlobals() . '/login.php') . '">Kembali</a>';
            exit;
        }

        $code = (string)($_GET['code'] ?? '');
        $state = (string)($_GET['state'] ?? '');

        if (empty($code) || empty($state) || empty($_SESSION['oauth_state']) || !hash_equals((string)$_SESSION['oauth_state'], $state)) {
            http_response_code(400);
            echo 'State tidak valid. Silakan ulangi login.';
            echo '<br><a href="' . htmlspecialchars(BaseUrl::fromGlobals() . '/login.php') . '">Kembali</a>';
            exit;
        }

        unset($_SESSION['oauth_state']);

        $redirectUri = $this->google['redirect_uri'] ?: (BaseUrl::fromGlobals() . '/auth/google_callback.php');

        try {
            [, $tokenResp] = HttpClient::postForm('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $this->google['client_id'],
                'client_secret' => $this->google['client_secret'],
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            $tokenJson = json_decode($tokenResp, true);
            if (!is_array($tokenJson) || empty($tokenJson['access_token'])) {
                throw new RuntimeException('Gagal mendapatkan access_token dari Google.');
            }
            $accessToken = (string)$tokenJson['access_token'];

            $profile = HttpClient::getJson('https://openidconnect.googleapis.com/v1/userinfo', $accessToken);
            if (($profile['_http_code'] ?? 0) >= 400) {
                throw new RuntimeException('Gagal mengambil profil akun Google.');
            }

            $email = (string)($profile['email'] ?? '');
            $emailVerified = (bool)($profile['email_verified'] ?? false);
            $sub = (string)($profile['sub'] ?? '');
            $name = (string)($profile['name'] ?? '');
            $picture = (string)($profile['picture'] ?? '');

            if (empty($email) || !$emailVerified) {
                throw new RuntimeException('Email Google tidak valid / belum terverifikasi.');
            }

            $hasEmailCol = Schema::hasColumn($this->pdo, 'users', 'email');
            $hasOauthProviderCol = Schema::hasColumn($this->pdo, 'users', 'oauth_provider');
            $hasOauthSubCol = Schema::hasColumn($this->pdo, 'users', 'oauth_sub');
            $hasOauthPictureCol = Schema::hasColumn($this->pdo, 'users', 'oauth_picture');

            if ($hasEmailCol) {
                $stmt = $this->pdo->prepare('SELECT * FROM users WHERE (email = ? OR username = ?) LIMIT 1');
                $stmt->execute([$email, $email]);
            } else {
                $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
                $stmt->execute([$email]);
            }
            $user = $stmt->fetch();

            $role = $user ? Role::normalize((string)($user['role'] ?? Role::ADMIN)) : '';

            // Admin tidak boleh login via SSO
            if ($role === Role::ADMIN) {
                throw new RuntimeException('Admin tidak dapat login menggunakan SSO. Gunakan login manual.');
            }

            // Jika user belum terdaftar: hanya auto-provision untuk email non-kampus sebagai perusahaan
            if (!$user) {
                $domain = DomainPolicy::emailDomain($email);
                if ($domain === 'polije.ac.id' || $domain === 'student.polije.ac.id') {
                    throw new RuntimeException('Akun belum terdaftar. Hubungi admin untuk didaftarkan sesuai role.');
                }
                $role = Role::PERUSAHAAN;
            }

            // Validasi domain sesuai role (role ditentukan dari DB)
            $requiredDomain = DomainPolicy::roleDomainRule($role);
            if ($requiredDomain && DomainPolicy::emailDomain($email) !== $requiredDomain) {
                throw new RuntimeException('Email tidak sesuai. Role ' . $role . ' wajib menggunakan @' . $requiredDomain);
            }

            if (!$user) {
                $randomPass = md5(bin2hex(random_bytes(16)));
                if ($hasEmailCol) {
                    $ins = $this->pdo->prepare('INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)');
                    $ins->execute([$email, $randomPass, Role::PERUSAHAAN, $email]);
                } else {
                    $ins = $this->pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                    $ins->execute([$email, $randomPass, Role::PERUSAHAAN]);
                }
                $userId = (int)$this->pdo->lastInsertId();

                $namaPerusahaan = $name ?: ('Mitra ' . explode('@', $email)[0]);
                $alamat = '';
                $kontak = $name ?: null;

                $insP = $this->pdo->prepare('INSERT INTO perusahaan (user_id, nama_perusahaan, alamat, email, kontak_person, kuota_magang) VALUES (?, ?, ?, ?, ?, 0)');
                $insP->execute([$userId, $namaPerusahaan, $alamat, $email, $kontak]);

                $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            }

            try {
                $updates = [];
                $params = [];

                if ($hasEmailCol && empty($user['email'])) {
                    $updates[] = 'email = ?';
                    $params[] = $email;
                }
                if ($hasOauthProviderCol) {
                    $updates[] = 'oauth_provider = ?';
                    $params[] = 'google';
                }
                if ($hasOauthSubCol) {
                    $updates[] = 'oauth_sub = ?';
                    $params[] = $sub;
                }
                if ($hasOauthPictureCol) {
                    $updates[] = 'oauth_picture = ?';
                    $params[] = $picture;
                }

                if ($updates) {
                    $params[] = $user['id'];
                    $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
                    $u = $this->pdo->prepare($sql);
                    $u->execute($params);
                }
            } catch (Throwable) {
                // optional columns; ignore
            }

            SessionMapper::setRoleSession($this->pdo, is_array($user) ? $user : []);
            Redirect::to(BaseUrl::fromGlobals() . '/' . self::loginRedirectForRole($role));
        } catch (Throwable $e) {
            http_response_code(400);
            echo 'Gagal login: ' . htmlspecialchars($e->getMessage());
            echo '<br><a href="' . htmlspecialchars(BaseUrl::fromGlobals() . '/login.php') . '">Kembali</a>';
            exit;
        }
    }

    private static function loginRedirectForRole(string $role): string
    {
        $role = Role::normalize($role);
        return match ($role) {
            Role::ADMIN => 'admin/beranda.php',
            Role::KAPRODI => 'kaprodi/dashboard.php',
            Role::DOSEN => 'dosen/dashboard.php',
            Role::MAHASISWA => 'mahasiswa/beranda.php',
            Role::PERUSAHAAN => 'perusahaan/beranda.php',
            default => 'login.php',
        };
    }
}
