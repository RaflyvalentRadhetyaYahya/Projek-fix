<?php

declare(strict_types=1);

namespace App\Auth;

final class Role
{
    public const ADMIN = 'admin';
    public const DOSEN = 'dosen';
    public const KAPRODI = 'kaprodi';
    public const MAHASISWA = 'mahasiswa';
    public const PERUSAHAAN = 'perusahaan';

    /** @return list<string> */
    public static function allowed(): array
    {
        return [self::ADMIN, self::DOSEN, self::KAPRODI, self::MAHASISWA, self::PERUSAHAAN];
    }

    public static function normalize(string $role): string
    {
        $role = strtolower(trim($role));
        return in_array($role, self::allowed(), true) ? $role : self::ADMIN;
    }
}
