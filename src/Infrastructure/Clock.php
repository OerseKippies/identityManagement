<?php

declare(strict_types=1);

namespace IdM\Infrastructure;

final class Clock
{
    public function nowUtc(): string
    {
        return gmdate('Y-m-d H:i:s');
    }

    public function nowIso8601(): string
    {
        return gmdate('Y-m-d\TH:i:s\Z');
    }
}
