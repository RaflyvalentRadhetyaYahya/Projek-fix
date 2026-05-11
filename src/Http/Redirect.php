<?php

declare(strict_types=1);

namespace App\Http;

final class Redirect
{
    public static function to(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
