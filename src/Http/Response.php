<?php

declare(strict_types=1);

namespace IdM\Http;

use IdM\Infrastructure\Clock;

final class Response
{
    /** @param array<string, string> $headers */
    public function __construct(
        public readonly int $statusCode,
        public readonly mixed $body = null,
        public readonly array $headers = []
    ) {
    }

    public static function json(int $statusCode, mixed $data, string $correlationId, array $extraHeaders = []): self
    {
        return new self($statusCode, $data, array_merge([
            'Content-Type' => 'application/json',
            'X-Correlation-Id' => $correlationId,
            'X-Api-Version' => 'v1',
        ], $extraHeaders));
    }

    public static function noContent(string $correlationId): self
    {
        return new self(204, null, [
            'X-Correlation-Id' => $correlationId,
            'X-Api-Version' => 'v1',
        ]);
    }

    public static function error(
        string $errorCode,
        string $message,
        int $statusCode,
        string $correlationId,
        ?Clock $clock = null
    ): self {
        $clock ??= new Clock();

        return self::json($statusCode, [
            'error' => [
                'errorCode' => $errorCode,
                'errorMessage' => $message,
                'correlationId' => $correlationId,
                'timestamp' => $clock->nowIso8601(),
            ],
        ], $correlationId);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($this->body !== null) {
            echo json_encode($this->body, JSON_THROW_ON_ERROR);
        }
    }
}
