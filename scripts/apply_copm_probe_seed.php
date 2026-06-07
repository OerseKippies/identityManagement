<?php

declare(strict_types=1);

use IdM\Autoloader;
use IdM\Infrastructure\Config;
use IdM\Infrastructure\Database;

$rootDir = dirname(__DIR__);

require $rootDir . '/src/Autoloader.php';
Autoloader::register($rootDir . '/src');

$config = Config::load(Config::resolvePath($rootDir));
$pdo = (new Database($config))->pdo();
$migrationId = '002_copm_probe_seed';

$statement = $pdo->prepare('SELECT migrationId FROM idm_schema_migrations WHERE migrationId = :migrationId LIMIT 1');
$statement->execute(['migrationId' => $migrationId]);
if ($statement->fetch() !== false) {
    fwrite(STDOUT, "Probe seed {$migrationId} already applied.\n");
    exit(0);
}

$sql = file_get_contents($rootDir . '/migrations/002_copm_probe_seed.sql');
if ($sql === false) {
    fwrite(STDERR, "Unable to read probe seed SQL.\n");
    exit(1);
}

$pdo->exec($sql);

$insert = $pdo->prepare('INSERT INTO idm_schema_migrations (migrationId, appliedAt) VALUES (:migrationId, :appliedAt)');
$insert->execute([
    'migrationId' => $migrationId,
    'appliedAt' => gmdate('Y-m-d H:i:s'),
]);

fwrite(STDOUT, "Probe seed {$migrationId} applied.\n");
