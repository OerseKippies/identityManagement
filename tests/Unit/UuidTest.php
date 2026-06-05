<?php

declare(strict_types=1);

use IdM\Infrastructure\Uuid;

test('Uuid::v4 generates valid UUID v4', function (): void {
    $uuid = Uuid::v4();
    if (!Uuid::isValid($uuid)) {
        throw new RuntimeException('generated UUID is invalid');
    }
});

test('Uuid::isValid rejects invalid values', function (): void {
    if (Uuid::isValid('not-a-uuid')) {
        throw new RuntimeException('invalid UUID accepted');
    }
});
