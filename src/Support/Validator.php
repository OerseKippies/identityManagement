<?php

declare(strict_types=1);

namespace Idm\Support;

use InvalidArgumentException;

final class Validator
{
    public static function requireFields(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }
    }

    public static function uuid(string $value, string $field = 'id'): void
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException("Invalid UUID for {$field}");
        }
    }

    public static function oneOf(string $value, array $allowed, string $field): void
    {
        if (!in_array($value, $allowed, true)) {
            throw new InvalidArgumentException("Invalid {$field}: {$value}");
        }
    }

    public static function email(string $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Invalid email');
        }
    }
}
