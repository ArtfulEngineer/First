<?php
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/config/Session.php';
require_once __DIR__ . '/config/CSRF.php';

Config::load(__DIR__ . '/.env');
Session::init();

// Backward compatibility wrapper functions
function generateToken(): string {
    return CSRF::generateToken();
}

function verifyToken(?string $token): bool {
    return !empty($token) && CSRF::verifyToken($token);
}
