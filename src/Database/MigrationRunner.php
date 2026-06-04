<?php

declare(strict_types=1);

namespace Idm\Database;

use PDO;

final class MigrationRunner
{
    private PDO $pdo;
    private string $migrationDir;

    public function __construct(PDO $pdo, string $migrationDir)
    {
        $this->pdo = $pdo;
        $this->migrationDir = $migrationDir;
    }

    public function run(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS idm_schema_migrations (migration VARCHAR(180) PRIMARY KEY, appliedAt DATETIME NOT NULL)');
        $files = glob($this->migrationDir . '/*.sql') ?: [];
        sort($files);

        foreach ($files as $file) {
            $name = basename($file);
            $exists = $this->pdo->prepare('SELECT migration FROM idm_schema_migrations WHERE migration = :migration');
            $exists->execute(['migration' => $name]);
            if ($exists->fetch()) {
                continue;
            }

            $this->pdo->exec((string) file_get_contents($file));
            $insert = $this->pdo->prepare('INSERT INTO idm_schema_migrations (migration, appliedAt) VALUES (:migration, UTC_TIMESTAMP())');
            $insert->execute(['migration' => $name]);
        }
    }
}
