<?php

declare(strict_types=1);

namespace Idm\Support;

final class Clock
{
    public static function now(): string
    {
        return gmdate('Y-m-d\TH:i:s\Z');
    }

    public static function dbNow(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}
