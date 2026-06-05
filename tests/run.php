<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require $rootDir . '/src/Autoloader.php';
IdM\Autoloader::register($rootDir . '/src');

/** @var list<callable> $tests */
$tests = [];

function test(string $name, callable $callback): void
{
    global $tests;
    $tests[] = function () use ($name, $callback): void {
        $callback();
        fwrite(STDOUT, "[PASS] {$name}\n");
    };
}

require __DIR__ . '/Unit/UuidTest.php';
require __DIR__ . '/Unit/CorrelationTest.php';
require __DIR__ . '/Unit/StatusTransitionTest.php';
require __DIR__ . '/Unit/ErrorResponseTest.php';

$failed = 0;
foreach ($tests as $case) {
    try {
        $case();
    } catch (Throwable $exception) {
        $failed++;
        fwrite(STDERR, '[FAIL] ' . $exception->getMessage() . PHP_EOL);
    }
}

if ($failed > 0) {
    fwrite(STDERR, "{$failed} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, 'All unit tests passed.' . PHP_EOL);
