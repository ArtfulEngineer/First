<?php

require_once __DIR__ . '/Session.php';

class CSRF {
    private const TOKEN_KEY = 'csrf_token';
    private const TOKEN_LIFETIME = 3600;

    public static function generateToken(): string {
        if (!Session::has(self::TOKEN_KEY)) {
            self::createToken();
        }

        // Rotate token if expired
        if (self::isTokenExpired()) {
            self::createToken();
        }

        return Session::get(self::TOKEN_KEY);
    }

    public static function verifyToken(string $token): bool {
        if (!Session::has(self::TOKEN_KEY)) {
            return false;
        }

        $sessionToken = Session::get(self::TOKEN_KEY);
        
        // Use hash_equals to prevent timing attacks
        return hash_equals($sessionToken, $token);
    }

    private static function createToken(): void {
        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_KEY, $token);
        Session::set(self::TOKEN_KEY . '_time', time());
    }

    private static function isTokenExpired(): bool {
        $createdAt = Session::get(self::TOKEN_KEY . '_time', 0);
        return (time() - $createdAt) > self::TOKEN_LIFETIME;
    }

    public static function rotateToken(): void {
        self::createToken();
    }
}
