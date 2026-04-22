<?php

require_once __DIR__ . '/Config.php';

class PasswordValidator {
    private string $password;
    private array $errors = [];

    public function __construct(string $password) {
        $this->password = $password;
    }

    public function validate(): bool {
        $this->errors = [];
        
        $minLength = Config::get('PASSWORD_MIN_LENGTH', 8);
        
        if (strlen($this->password) < $minLength) {
            $this->errors[] = "Password must be at least {$minLength} characters";
        }

        if (!preg_match('/[a-z]/', $this->password)) {
            $this->errors[] = "Password must contain lowercase letters";
        }

        if (!preg_match('/[A-Z]/', $this->password)) {
            $this->errors[] = "Password must contain uppercase letters";
        }

        if (!preg_match('/[0-9]/', $this->password)) {
            $this->errors[] = "Password must contain numbers";
        }

        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/]/', $this->password)) {
            $this->errors[] = "Password must contain special characters (!@#$%^&*...)";
        }

        return empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getErrorsString(): string {
        return implode(', ', $this->errors);
    }

    public static function getStrength(string $password): int {
        $strength = 0;
        if (strlen($password) >= 8) $strength++;
        if (strlen($password) >= 12) $strength++;
        if (preg_match('/[a-z]/', $password)) $strength++;
        if (preg_match('/[A-Z]/', $password)) $strength++;
        if (preg_match('/[0-9]/', $password)) $strength++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;
        return min($strength, 5);
    }
}
