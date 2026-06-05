<?php

declare(strict_types=1);

namespace IdM\Domain;

use IdM\Http\ApiException;

final class StatusTransition
{
    public static function assertUserOrServiceAccount(string $from, string $to): void
    {
        $allowed = [
            'PENDING' => ['ACTIVE'],
            'ACTIVE' => ['DISABLED', 'LOCKED'],
            'LOCKED' => ['ACTIVE'],
            'DISABLED' => ['ACTIVE'],
        ];

        self::assertAllowed($from, $to, $allowed, 'status transition');
    }

    public static function assertAccessPolicy(string $from, string $to): void
    {
        $allowed = [
            'DRAFT' => ['ACTIVE'],
            'ACTIVE' => ['RETIRED'],
        ];

        self::assertAllowed($from, $to, $allowed, 'access policy transition');
    }

    public static function assertTokenReference(string $from, string $to): void
    {
        $allowed = [
            'ACTIVE' => ['REVOKED', 'EXPIRED'],
        ];

        self::assertAllowed($from, $to, $allowed, 'token reference transition');
    }

    /** @param array<string, list<string>> $allowed */
    private static function assertAllowed(string $from, string $to, array $allowed, string $label): void
    {
        if (!isset($allowed[$from]) || !in_array($to, $allowed[$from], true)) {
            throw new ApiException(
                'VALIDATION_ERROR',
                sprintf('Invalid %s from %s to %s', $label, $from, $to),
                400
            );
        }
    }
}
