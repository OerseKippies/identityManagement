<?php

declare(strict_types=1);

namespace IdM\Infrastructure;

use PDO;
use PDOException;

final class Database
{
    private PDO $pdo;

    public function __construct(Config $config)
    {
        $host = $config->getString('database.host');
        $port = (int) $config->get('database.port', 3306);
        $dbname = $config->getString('database.dbname');
        $username = $config->getString('database.username');
        $password = $config->getString('database.password');
        $charset = $config->getString('database.charset', 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $dbname, $charset);

        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new \RuntimeException('Database connection failed: ' . $exception->getMessage(), 0, $exception);
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}
