<?php
// Database connection wrapper
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/config/Database.php';

Config::load(__DIR__ . '/.env');

try {
    $conn = Database::getInstance();
} catch (RuntimeException $e) {
    http_response_code(500);
    die("Database Error: " . htmlspecialchars($e->getMessage()));
}

