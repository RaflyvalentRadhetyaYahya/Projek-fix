<?php
// config.php
$autoload = __DIR__ . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

// Minimal .env loader (tanpa dependency).
// - Format: KEY=value
// - Mendukung value dengan kutip tunggal/dobel
// - Tidak menimpa env yang sudah ada
function load_dotenv(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if (!is_array($lines)) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $eqPos = strpos($line, '=');
        if ($eqPos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $eqPos));
        $value = trim(substr($line, $eqPos + 1));

        if ($key === '' || preg_match('/\s/', $key)) {
            continue;
        }

        // Strip quotes
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        // Do not override existing
        $already = getenv($key);
        if ($already !== false && $already !== '') {
            continue;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

load_dotenv(__DIR__ . '/.env');

$host = 'localhost';
$dbname = 'db_magang';
$username = 'root';
$password = ''; // Default laragon root password is empty

// Google OAuth2 / SSO settings
// Isi sesuai kredensial dari Google Cloud Console (OAuth Client ID)
// Catatan: redirect URI harus terdaftar di Google Console.
$GOOGLE_OAUTH = [
    // Set via environment variables (recommended)
    'client_id' => getenv('GOOGLE_OAUTH_CLIENT_ID') ?: '',
    'client_secret' => getenv('GOOGLE_OAUTH_CLIENT_SECRET') ?: '',
    // Jika kosong, aplikasi akan auto-generate dari host saat runtime.
    // Redirect yang benar biasanya mengarah ke: /auth/google_callback.php
    'redirect_uri' => getenv('GOOGLE_OAUTH_REDIRECT_URI') ?: '',
];

function app_base_url(): string
{
    if (class_exists(\App\Support\BaseUrl::class)) {
        return \App\Support\BaseUrl::fromGlobals();
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $basePath = rtrim(dirname($scriptName), '/');
    if ($basePath === '/' || $basePath === '.') {
        $basePath = '';
    }
    return $scheme . '://' . $host . $basePath;
}

try {
    if (class_exists(\App\Database\PdoFactory::class)) {
        $pdo = \App\Database\PdoFactory::make([
            'host' => $host,
            'dbname' => $dbname,
            'username' => $username,
            'password' => $password,
        ]);
    } else {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Note: Do not expose actual connection error details in production
    die("Koneksi Database Gagal: " . $e->getMessage());
}

