<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';

class Admin {
    private string $username;
    private mysqli $db;

    public function __construct(string $username) {
        $this->db = Database::getInstance();
        $this->username = trim($username);
    }

    public static function getCurrentAdmin(): ?self {
        if (!Session::isAuthenticated() || !Session::has('admin')) {
            return null;
        }
        return new self(Session::get('admin'));
    }

    public function getUsername(): string {
        return $this->username;
    }

    private function prepare(string $query): mysqli_stmt {
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db->error);
        }
        return $stmt;
    }

    public function exists(): bool {
        $stmt = $this->prepare("SELECT id FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $this->username);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function verifyPassword(string $password): bool {
        $stmt = $this->prepare("SELECT password FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $this->username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            return false;
        }

        $row = $result->fetch_assoc();

        // Prevent timing attacks
        if (!isset($row['password'])) {
            return false;
        }

        return password_verify($password, $row['password']);
    }

    public function changePassword(string $newPassword): bool {
        // Enforce strong password policy
        if (!$this->isStrongPassword($newPassword)) {
            throw new Exception("Password must be at least 8 characters long and include letters and numbers.");
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $this->prepare("
            UPDATE admin 
            SET password = ?, updated_at = NOW() 
            WHERE username = ?
        ");

        $stmt->bind_param("ss", $hashed, $this->username);

        return $stmt->execute();
    }

    public function getInfo(): array {
        $stmt = $this->prepare("
            SELECT id, username, created_at, updated_at 
            FROM admin 
            WHERE username = ? 
            LIMIT 1
        ");

        $stmt->bind_param("s", $this->username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return [];
        }

        return $result->fetch_assoc();
    }

    public function updateUsername(string $newUsername): bool {
        $newUsername = trim($newUsername);

        if (strlen($newUsername) < 3) {
            throw new Exception("Username must be at least 3 characters.");
        }

        $stmt = $this->prepare("
            UPDATE admin 
            SET username = ?, updated_at = NOW() 
            WHERE username = ?
        ");

        $stmt->bind_param("ss", $newUsername, $this->username);

        $success = $stmt->execute();

        if ($success) {
            $this->username = $newUsername;
            Session::set('admin', $newUsername);
        }

        return $success;
    }

    private function isStrongPassword(string $password): bool {
        return strlen($password) >= 8 &&
               preg_match('/[A-Za-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }
}