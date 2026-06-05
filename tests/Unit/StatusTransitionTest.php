<?php

declare(strict_types=1);

use IdM\Domain\StatusTransition;
use IdM\Http\ApiException;

test('User PENDING to ACTIVE is allowed', function (): void {
    StatusTransition::assertUserOrServiceAccount('PENDING', 'ACTIVE');
});

test('User DISABLED to LOCKED is rejected', function (): void {
    try {
        StatusTransition::assertUserOrServiceAccount('DISABLED', 'LOCKED');
        throw new RuntimeException('invalid transition accepted');
    } catch (ApiException $exception) {
        if ($exception->errorCode !== 'VALIDATION_ERROR') {
            throw $exception;
        }
    }
});

test('AccessPolicy DRAFT to ACTIVE is allowed', function (): void {
    StatusTransition::assertAccessPolicy('DRAFT', 'ACTIVE');
});
