<?php
use App\Auth\DomainPolicy;
use App\Auth\Role;
use App\Auth\SessionMapper;
use App\Database\Schema;
use App\Http\Redirect;

require_once __DIR__ . '/../config.php';

function allowed_roles(): array
{
    return class_exists(Role::class) ? Role::allowed() : ['admin', 'kaprodi', 'dosen', 'mahasiswa', 'perusahaan'];
}

function normalize_role(?string $role): string
{
    if (class_exists(Role::class)) {
        return Role::normalize((string) $role);
    }
    $role = strtolower(trim((string) $role));
    return in_array($role, allowed_roles(), true) ? $role : 'admin';
}

function db_has_column(PDO $pdo, string $table, string $column): bool
{
    if (class_exists(Schema::class)) {
        return Schema::hasColumn($pdo, $table, $column);
    }
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);
    return (bool) $stmt->fetch();
}

function redirect_to(string $url): never
{
    if (class_exists(Redirect::class)) {
        Redirect::to($url);
    }
    header('Location: ' . $url);
    exit;
}

function login_redirect_for_role(string $role): string
{
    return match ($role) {
        'admin' => 'admin/beranda.php',
        'kaprodi' => 'kaprodi/dashboard.php',
        'dosen' => 'dosen/dashboard.php',
        'mahasiswa' => 'mahasiswa/beranda.php',
        'perusahaan' => 'perusahaan/beranda.php',
        default => 'login.php',
    };
}

function set_role_session(PDO $pdo, array $userRow): void
{
    if (class_exists(SessionMapper::class)) {
        SessionMapper::setRoleSession($pdo, $userRow);
        return;
    }

    // Fallback: keep previous behavior if autoload not available
    $role = $userRow['role'] ?? '';
    $_SESSION['user_id'] = $userRow['id'] ?? null;
    $_SESSION['role'] = $role;
    $_SESSION['username'] = $userRow['username'] ?? null;
    $_SESSION['user_email'] = $userRow['email'] ?? null;
    $_SESSION[$role . '_logged_in'] = true;
}

function role_domain_rule(string $role): ?string
{
    if (class_exists(DomainPolicy::class)) {
        return DomainPolicy::roleDomainRule($role);
    }
    return match ($role) {
        'dosen', 'kaprodi' => 'polije.ac.id',
        'mahasiswa' => 'student.polije.ac.id',
        default => null,
    };
}

function email_domain(string $email): string
{
    if (class_exists(DomainPolicy::class)) {
        return DomainPolicy::emailDomain($email);
    }
    $parts = explode('@', strtolower(trim($email)));
    return $parts[1] ?? '';
}
