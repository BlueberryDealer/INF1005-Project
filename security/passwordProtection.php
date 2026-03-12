<?php
/**
 * PasswordManager Class
 * Password hashing and verification
 */
class PasswordManager {
    
    // Hash a password securely
    public static function hash(string $password): string|false {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $hashedPassword !== false ? $hashedPassword : false;
    }
    
    // Verify password against hash. Used during login
    public static function verify(string $password, string $hash): bool {
        if (empty($password) || empty($hash)) {
            return false;
        }
        
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password is strong
     * Requirements:
     * - >8 characters
     * - Uppercase letter
     * - Lowercase letter
     * - Number
     * - Special character
     */
    public static function isStrong(string $password): bool {
        if (strlen($password) < 8) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/[0-9]/', $password)) return false;
        if (!preg_match('/[!@#$%^&*()]/', $password)) return false;
        
        return true;
    }
    
    // Get password strength feedback. Tells user what's missing.
    public static function getStrengthFeedback(string $password): array {
        $feedback = [];
        
        if (strlen($password) < 8) {
            $feedback[] = "At least 8 characters required.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $feedback[] = "Add uppercase letters (A-Z).";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $feedback[] = "Add lowercase letters (a-z).";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $feedback[] = "Add numbers (0-9).";
        }
        if (!preg_match('/[!@#$%^&*()]/', $password)) {
            $feedback[] = "Add special characters (!@#$%^&*).";
        }

        return empty($feedback) ? ['Password is strong!'] : $feedback;
    }
    
}
