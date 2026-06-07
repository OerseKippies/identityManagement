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

    public static function resolvePath(string $rootDir): string
    {
        $configPath = $rootDir . '/config/config.php';
        if (is_file($configPath)) {
            return $configPath;
        }

        $examplePath = $rootDir . '/config/config.example.php';
        if (is_file($examplePath)) {
            return $examplePath;
        }

        throw new \RuntimeException('Configuration file not found under ' . $rootDir . '/config');
    }

    public static function load(string $path): self
    {
        if (!is_file($path)) {
            throw new \RuntimeException('Configuration file not found: ' . $path);
        }

        /** @var array<string, mixed> $values */
        $values = require $path;
        $values = self::mergeVersioEnv($values, dirname($path));

        return new self($values);
    }

    /** @param array<string, mixed> $values */
    private static function mergeVersioEnv(array $values, string $configDir): array
    {
        $envPath = $configDir . '/env.versio';
        if (!is_file($envPath)) {
            return $values;
        }

        $env = parse_ini_file($envPath, false, INI_SCANNER_RAW);
        if (!is_array($env)) {
            return $values;
        }

        if (isset($env['IDM_DB_HOST']) && is_string($env['IDM_DB_HOST'])) {
            $values['database']['host'] = $env['IDM_DB_HOST'];
        }
        if (isset($env['IDM_DB_PORT'])) {
            $values['database']['port'] = (int) $env['IDM_DB_PORT'];
        }
        if (isset($env['IDM_DB_NAME']) && is_string($env['IDM_DB_NAME'])) {
            $values['database']['dbname'] = $env['IDM_DB_NAME'];
        }
        if (isset($env['IDM_DB_USER']) && is_string($env['IDM_DB_USER'])) {
            $values['database']['username'] = $env['IDM_DB_USER'];
        }
        if (isset($env['IDM_DB_PASSWORD']) && is_string($env['IDM_DB_PASSWORD'])) {
            $values['database']['password'] = $env['IDM_DB_PASSWORD'];
        }
        if (isset($env['IDM_API_KEY']) && is_string($env['IDM_API_KEY']) && $env['IDM_API_KEY'] !== '') {
            $values['api']['api_key'] = $env['IDM_API_KEY'];
        }

        return $values;
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
