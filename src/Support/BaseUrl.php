<?php

declare(strict_types=1);

namespace App\Support;

final class BaseUrl
{
    public static function fromGlobals(): string
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');

        // Tentukan root aplikasi dengan memotong segmen folder "modul" yang dikenal.
        $knownSegments = ['/auth/', '/admin/', '/dosen/', '/kaprodi/', '/mahasiswa/', '/perusahaan/'];
        $basePath = null;
        foreach ($knownSegments as $seg) {
            $pos = strpos($scriptName, $seg);
            if ($pos !== false) {
                $basePath = substr($scriptName, 0, $pos);
                break;
            }
        }
        if ($basePath === null) {
            $basePath = rtrim(dirname($scriptName), '/');
        }
        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }

        return $scheme . '://' . $host . $basePath;
    }
}
