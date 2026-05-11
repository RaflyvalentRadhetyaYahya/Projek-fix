<?php

declare(strict_types=1);

namespace App\Auth;

use App\Http\Redirect;
use App\Support\BaseUrl;

final class Guard
{
    public static function requireRole(string $role): void
    {
        $role = Role::normalize($role);

        $flag = $role . '_logged_in';
        $ok = !empty($_SESSION[$flag]);

        if ($ok) {
            // Pastikan session punya mapping profil yang diperlukan
            $profileOk = true;
            if ($role === Role::DOSEN && empty($_SESSION['dosen_id'])) {
                $profileOk = false;
            }
            if ($role === Role::KAPRODI && (empty($_SESSION['kaprodi_id']) || empty($_SESSION['prodi_id']))) {
                $profileOk = false;
            }
            if ($role === Role::MAHASISWA && empty($_SESSION['mahasiswa_id'])) {
                $profileOk = false;
            }

            if (!$profileOk) {
                $_SESSION = [];
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_destroy();
                }
                Redirect::to(BaseUrl::fromGlobals() . '/login.php?role=' . urlencode($role) . '&error=profile');
            }

            return;
        }

        Redirect::to(BaseUrl::fromGlobals() . '/login.php?role=' . urlencode($role));
    }
}
