<?php

declare(strict_types=1);

use IdM\Autoloader;
use IdM\Infrastructure\Config;
use IdM\Infrastructure\Database;

$rootDir = __DIR__ . '/..';

require $rootDir . '/src/Autoloader.php';
Autoloader::register($rootDir . '/src');

try {
    $config = Config::load(Config::resolvePath($rootDir));
    new Database($config);
    echo 'DB CONNECTIE OK';
} catch (Throwable $exception) {
    echo $exception->getMessage();
    exit(1);
}
