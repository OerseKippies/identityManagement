<?php

declare(strict_types=1);

use IdM\Autoloader;
use IdM\Infrastructure\Config;
use IdM\Infrastructure\Database;

$rootDir = dirname(__DIR__);

require $rootDir . '/src/Autoloader.php';
Autoloader::register($rootDir . '/src');

$configPath = Config::resolvePath($rootDir);

$config = Config::load($configPath);
$database = new Database($config);
$pdo = $database->pdo();

$migrationId = '001_initial_schema';

try {
    $statement = $pdo->prepare('SELECT migrationId FROM idm_schema_migrations WHERE migrationId = :migrationId LIMIT 1');
    $statement->execute(['migrationId' => $migrationId]);
    if ($statement->fetch() !== false) {
        fwrite(STDOUT, "Migration {$migrationId} already applied.\n");
        exit(0);
    }
} catch (\PDOException) {
    // Table may not exist yet; continue with migration file.
}

$sql = file_get_contents($rootDir . '/migrations/001_initial_schema.sql');
if ($sql === false) {
    fwrite(STDERR, "Unable to read migration SQL.\n");
    exit(1);
}

$pdo->exec($sql);

$insert = $pdo->prepare('INSERT INTO idm_schema_migrations (migrationId, appliedAt) VALUES (:migrationId, :appliedAt)');
$insert->execute([
    'migrationId' => $migrationId,
    'appliedAt' => gmdate('Y-m-d H:i:s'),
]);

fwrite(STDOUT, "Migration {$migrationId} applied.\n");
