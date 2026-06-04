<?php

declare(strict_types=1);

namespace Idm\Http;

use Idm\Support\Clock;
use Throwable;

final class ErrorResponse
{
    public static function send(string $code, string $message, string $correlationId, int $status): void
    {
        Response::json([
            'error' => [
                'errorCode' => $code,
                'errorMessage' => $message,
                'correlationId' => $correlationId,
                'timestamp' => Clock::now(),
            ],
        ], $status);
    }

    public static function fromThrowable(Throwable $error, string $correlationId): void
    {
        self::send('INTERNAL_ERROR', $error->getMessage(), $correlationId, 500);
    }
}
