<?php

declare(strict_types=1);

namespace IdM\Infrastructure;

final class Config
{
    /** @var array<string, mixed> */
    private array $values;

    /** @param array<string, mixed> $values */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public static function load(string $path): self
    {
        if (!is_file($path)) {
            throw new \RuntimeException('Configuration file not found: ' . $path);
        }

        /** @var array<string, mixed> $values */
        $values = require $path;

        return new self($values);
    }

    public function getString(string $key, ?string $default = null): string
    {
        $value = $this->get($key, $default);
        if (!is_string($value)) {
            throw new \RuntimeException('Expected string config value for ' . $key);
        }

        return $value;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);

        return (bool) $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $current = $this->values;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
