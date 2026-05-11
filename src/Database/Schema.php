<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

final class Schema
{
    public static function hasColumn(PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $stmt->execute([$column]);
        return (bool) $stmt->fetch();
    }
}
