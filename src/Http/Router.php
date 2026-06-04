<?php

declare(strict_types=1);

namespace Idm\Http;

use InvalidArgumentException;
use RuntimeException;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:callable}> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => rtrim($pattern, '/') ?: '/',
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }

            $params = $this->match($route['pattern'], $request->path);
            if ($params === null) {
                continue;
            }

            ($route['handler'])($request, $params);
            return;
        }

        ErrorResponse::send('NOT_FOUND', 'Route not found', $request->correlationId, 404);
    }

    private function match(string $pattern, string $path): ?array
    {
        $names = [];
        $regex = preg_replace_callback('/\{([a-zA-Z][a-zA-Z0-9_]*)\}/', static function (array $matches) use (&$names): string {
            $names[] = $matches[1];
            return '([^/]+)';
        }, $pattern);

        if ($regex === null || preg_match('#^' . $regex . '$#', $path, $matches) !== 1) {
            return null;
        }

        array_shift($matches);
        return array_combine($names, $matches) ?: [];
    }

    public static function handle(callable $callback, Request $request): void
    {
        try {
            $callback();
        } catch (InvalidArgumentException $error) {
            ErrorResponse::send('VALIDATION_ERROR', $error->getMessage(), $request->correlationId, 400);
        } catch (RuntimeException $error) {
            $status = $error->getCode();
            $status = in_array($status, [403, 404, 409], true) ? $status : 500;
            $code = match ($status) {
                403 => 'FORBIDDEN',
                404 => 'NOT_FOUND',
                409 => 'CONFLICT',
                default => 'INTERNAL_ERROR',
            };
            ErrorResponse::send($code, $error->getMessage(), $request->correlationId, $status);
        }
    }
}
