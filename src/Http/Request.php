<?php

declare(strict_types=1);

namespace Idm\Http;

use Idm\Support\Uuid;

final class Request
{
    public string $method;
    public string $path;
    public array $body;
    public array $query;
    public string $correlationId;

    public function __construct(string $method, string $path, array $body, array $query, string $correlationId)
    {
        $this->method = strtoupper($method);
        $this->path = $path === '' ? '/' : $path;
        $this->body = $body;
        $this->query = $query;
        $this->correlationId = $correlationId;
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $raw = file_get_contents('php://input') ?: '';
        $body = [];

        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            $body = is_array($decoded) ? $decoded : [];
        }

        $correlationId = $_SERVER['HTTP_X_CORRELATION_ID'] ?? Uuid::v4();

        return new self($_SERVER['REQUEST_METHOD'] ?? 'GET', rtrim($path, '/') ?: '/', $body, $_GET, $correlationId);
    }
}
