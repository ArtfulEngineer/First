<?php

require_once __DIR__ . '/Config.php';

class Session {
    private const TIMEOUT_KEY = 'session_timeout';

    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            self::checkTimeout();
        }
    }

    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function destroy(): void {
        session_destroy();
        $_SESSION = [];
    }

    public static function isAuthenticated(): bool {
        return self::has('admin') && !empty(self::get('admin'));
    }

    private static function checkTimeout(): void {
        $timeout = Config::get('SESSION_TIMEOUT', 3600);
        
        if (!isset($_SESSION[self::TIMEOUT_KEY])) {
            $_SESSION[self::TIMEOUT_KEY] = time();
            return;
        }

        if (time() - $_SESSION[self::TIMEOUT_KEY] > $timeout) {
            self::destroy();
            header('Location: admin_login.php?expired=1');
            exit;
        }

        $_SESSION[self::TIMEOUT_KEY] = time();
    }

    public static function regenerate(): void {
        session_regenerate_id(true);
    }
}
