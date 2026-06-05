<?php

declare(strict_types=1);

use IdM\Infrastructure\Correlation;
use IdM\Infrastructure\Uuid;

test('Correlation resolves supplied valid header', function (): void {
    $expected = Uuid::v4();
    $actual = Correlation::resolve($expected);
    if ($actual !== strtolower($expected)) {
        throw new RuntimeException('correlation header not preserved');
    }
});

test('Correlation generates UUID when header missing', function (): void {
    $actual = Correlation::resolve(null);
    if (!Uuid::isValid($actual)) {
        throw new RuntimeException('generated correlation id invalid');
    }
});
