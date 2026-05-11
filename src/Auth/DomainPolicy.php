<?php

declare(strict_types=1);

namespace App\Auth;

final class DomainPolicy
{
    public static function roleDomainRule(string $role): ?string
    {
        $role = Role::normalize($role);
        return match ($role) {
            Role::DOSEN, Role::KAPRODI => 'polije.ac.id',
            Role::MAHASISWA => 'student.polije.ac.id',
            default => null,
        };
    }

    public static function emailDomain(string $email): string
    {
        $atPos = strrpos($email, '@');
        if ($atPos === false) {
            return '';
        }
        return strtolower(substr($email, $atPos + 1));
    }
}
