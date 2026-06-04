<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

\Idm\Bootstrap::autoload($root);

$configFile = $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
if (!is_file($configFile)) {
    $configFile = $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.example.php';
}

$config = require $configFile;
\Idm\Bootstrap::run($config, $root);
