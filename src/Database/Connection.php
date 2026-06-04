<?php

declare(strict_types=1);

namespace Idm\Database;

use PDO;

final class Connection
{
    public static function create(array $config): PDO
    {
        $database = $config['database'] ?? [];
        $dsn = $database['dsn'] ?? 'mysql:host=127.0.0.1;dbname=idm;charset=utf8mb4';
        $username = $database['username'] ?? '';
        $password = $database['password'] ?? '';

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
