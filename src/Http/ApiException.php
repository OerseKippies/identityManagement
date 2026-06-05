<?php

declare(strict_types=1);

namespace IdM\Http;

final class ApiException extends \RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $statusCode
    ) {
        parent::__construct($message);
    }
}
