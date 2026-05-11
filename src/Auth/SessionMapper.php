<?php

declare(strict_types=1);

namespace App\Auth;

use PDO;

final class SessionMapper
{
    /** @param array<string,mixed> $user */
    public static function setRoleSession(PDO $pdo, array $user): void
    {
        $role = Role::normalize((string)($user['role'] ?? Role::ADMIN));

        $_SESSION['user_id'] = (int)($user['id'] ?? 0);
        $_SESSION['role'] = $role;

        foreach (['admin','kaprodi','dosen','mahasiswa','perusahaan'] as $r) {
            unset($_SESSION[$r . '_logged_in']);
        }

        $_SESSION[$role . '_logged_in'] = true;

        if ($role === Role::DOSEN) {
            $stmt = $pdo->prepare('SELECT id FROM dosen WHERE user_id = ? LIMIT 1');
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();
            if ($row) {
                $_SESSION['dosen_id'] = (int)$row['id'];
            }
        }

        if ($role === Role::KAPRODI) {
            $stmt = $pdo->prepare('SELECT id, prodi_id FROM kaprodi WHERE user_id = ? LIMIT 1');
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();
            if ($row) {
                $_SESSION['kaprodi_id'] = (int)$row['id'];
                $_SESSION['prodi_id'] = (int)$row['prodi_id'];
            }
        }

        if ($role === Role::MAHASISWA) {
            $stmt = $pdo->prepare('SELECT id, nim, prodi_id FROM mahasiswa WHERE user_id = ? LIMIT 1');
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();
            if ($row) {
                $_SESSION['mahasiswa_id'] = (int)$row['id'];
                $_SESSION['nim'] = $row['nim'];
                $_SESSION['prodi_id'] = (int)$row['prodi_id'];
            }
        }

        if ($role === Role::PERUSAHAAN) {
            $stmt = $pdo->prepare('SELECT id, nama_perusahaan FROM perusahaan WHERE user_id = ? LIMIT 1');
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();
            if ($row) {
                $_SESSION['perusahaan_id'] = (int)$row['id'];
                $_SESSION['nama_perusahaan'] = $row['nama_perusahaan'];
            }
        }
    }
}
