<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

final class PdoFactory
{
    /** @param array{host:string,dbname:string,username:string,password:string} $db */
    public static function make(array $db): PDO
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $db['host'], $db['dbname']);
        $pdo = new PDO($dsn, $db['username'], $db['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }
}
