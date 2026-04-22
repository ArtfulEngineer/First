<?php

require_once __DIR__ . '/Config.php';

class Database {
    private static ?mysqli $instance = null;
    private mysqli $connection;

    private function __construct() {
        // Config is already loaded at app bootstrap, but ensure it's loaded
        $this->connection = new mysqli(
            Config::get('DB_HOST', 'localhost'),
            Config::get('DB_USER', 'root'),
            Config::get('DB_PASS', ''),
            Config::get('DB_NAME', 'ai_solutions')
        );

        if ($this->connection->connect_error) {
            throw new RuntimeException("Database connection failed: " . $this->connection->connect_error);
        }

        $this->connection->set_charset("utf8mb4");
    }

    public static function getInstance(): mysqli {
        if (self::$instance === null) {
            $db = new self();
            self::$instance = $db->connection;
        }
        return self::$instance;
    }

    public function __clone() {}
    public function __wakeup() {}
}
