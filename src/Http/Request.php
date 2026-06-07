<?php

declare(strict_types=1);

namespace IdM\Http;

final class Request
{
    /** @param array<string, string> $headers */
    /** @param array<string, mixed> $query */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $headers,
        public readonly ?array $body,
        public readonly string $correlationId,
        public readonly array $query = []
    ) {
    }

    public static function fromGlobals(string $correlationId): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $queryString = parse_url($uri, PHP_URL_QUERY);
        $query = [];
        if (is_string($queryString) && $queryString !== '') {
            parse_str($queryString, $query);
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = is_string($value) ? $value : '';
            }
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = (string) $_SERVER['CONTENT_TYPE'];
        }

        $rawBody = file_get_contents('php://input');
        $body = null;
        if ($rawBody !== false && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        return new self($method, $path, $headers, $body, $correlationId, $query);
    }

    public function header(string $name): ?string
    {
        $key = strtolower($name);

        return $this->headers[$key] ?? null;
    }
}
