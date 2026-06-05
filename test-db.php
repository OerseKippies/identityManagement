<?php

$config = require 'config/config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['database']['host']};port={$config['database']['port']};dbname={$config['database']['dbname']}",
        $config['database']['username'],
        $config['database']['password']
    );

    echo "DB CONNECTIE OK";
} catch (Exception $e) {
    echo $e->getMessage();
}