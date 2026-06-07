<?php

declare(strict_types=1);

use IdM\Application;
use IdM\Autoloader;
use IdM\Http\Request;
use IdM\Infrastructure\Config;
use IdM\Infrastructure\Correlation;

$rootDir = dirname(__DIR__, 2);

require $rootDir . '/src/Autoloader.php';
Autoloader::register($rootDir . '/src');

$configPath = Config::resolvePath($rootDir);

$correlationId = Correlation::resolve($_SERVER['HTTP_X_CORRELATION_ID'] ?? null);
$request = Request::fromGlobals($correlationId);
$request = normalizePath($request);

$application = new Application($configPath);
$application->handle($request)->send();

function normalizePath(Request $request): Request
{
    $path = $request->path;

    if (str_starts_with($path, '/public/api')) {
        $path = substr($path, strlen('/public/api')) ?: '/';
    }

    if (!str_starts_with($path, '/v1') && $path !== '/health') {
        if (str_starts_with($path, '/api/v1')) {
            $path = substr($path, strlen('/api'));
        }
    }

    return new Request($request->method, $path, $request->headers, $request->body, $request->correlationId, $request->query);
}
