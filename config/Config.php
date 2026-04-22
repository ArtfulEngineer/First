<?php

class Config {
    /**
     * Internal configuration storage
     * @var array<string, mixed>
     */
    private static array $config = [];

    /**
     * Load environment variables from a file
     *
     * @param string $envFilePath Path to the .env file
     */
    public static function load(string $envFilePath): void {
        if (!file_exists($envFilePath)) {
            return;
        }

        $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Remove inline comments
            $line = preg_replace('/\s+#.*$/', '', $line);

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value);

            // Remove surrounding quotes
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Convert types
            $value = self::parseValue($value);

            // Store in config
            self::$config[$key] = $value;

            // Also set in environment
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    /**
     * Parse string values into proper types
     *
     * @param string $value
     * @return mixed
     */
    private static function parseValue(string $value): mixed {
        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => is_numeric($value) ? $value + 0 : $value
        };
    }

    /**
     * Get a configuration value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed {
        return self::$config[$key] ?? $_ENV[$key] ?? $default;
    }

    /**
     * Set a configuration value
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, mixed $value): void {
        self::$config[$key] = $value;
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }

    /**
     * Check if a configuration key exists
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool {
        return array_key_exists($key, self::$config) || array_key_exists($key, $_ENV);
    }
}
