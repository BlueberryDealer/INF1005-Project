<?php

 // CSRF Token Manager
 
class CSRFToken {
    
    private static $sessionKey = 'csrf_token';
    private static $sessionTime = 'csrf_token_time';
    private static $tokenLength = 32; // 256 bits
    private static $tokenExpiry = 3600; // 1 hour
    
    // Generata new csrf token & store in session
    public static function generate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        try {
            // Generate random bytes and convert to hex
            $token = bin2hex(random_bytes(self::$tokenLength));
            
            // Store in session with timestamp
            $_SESSION[self::$sessionKey] = $token;
            $_SESSION[self::$sessionTime] = time();
            
            return $token;
            
        } catch (Exception $e) {
            error_log("Token generation error: " . $e->getMessage());
            return null;
        }
    }
    
    
    // Get current token or generate if missing   
    public static function get() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Return existing valid token
        if (!empty($_SESSION[self::$sessionKey]) && self::isValid($_SESSION[self::$sessionKey])) {
            return $_SESSION[self::$sessionKey];
        }
        
        // Generate new token
        return self::generate();
    }
    
    // Validate a token
    public static function validate($providedToken, $consume = false) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // 1. Check if token is provided
        if (empty($providedToken)) {
            self::logFailure('Empty token');
            return false;
        }
        
        // 2. Check if session token exists
        if (empty($_SESSION[self::$sessionKey])) {
            self::logFailure('No token in session');
            return false;
        }
        
        // 3. Timing-safe comparison 
        if (!hash_equals($_SESSION[self::$sessionKey], $providedToken)) {
            self::logFailure('Token mismatch');
            return false;
        }
        
        // 4. Check expiration
        if (!isset($_SESSION[self::$sessionTime])) {
            self::logFailure('Token timestamp missing');
            return false;
        }
        
        $age = time() - $_SESSION[self::$sessionTime];
        if ($age > self::$tokenExpiry) {
            self::logFailure('Token expired');
            return false;
        }
        
        // 5. Optionally consume token (single-use)
        if ($consume) {
            self::invalidate();
        }
        
        return true;
    }
    
    
    //Check if token is valid without consuming   
    public static function isValid($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($token) || empty($_SESSION[self::$sessionKey])) {
            return false;
        }
        
        if (!hash_equals($_SESSION[self::$sessionKey], $token)) {
            return false;
        }
        
        if (!isset($_SESSION[self::$sessionTime])) {
            return false;
        }
        
        $age = time() - $_SESSION[self::$sessionTime];
        return $age <= self::$tokenExpiry;
    }
    
    
    // Invalidate/consume token
    public static function invalidate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION[self::$sessionKey]);
        unset($_SESSION[self::$sessionTime]);
    }
    
    
    // Regenerate token (for privilege escalation, etc.)
    public static function regenerate() {
        self::invalidate();
        return self::generate();
    }
    
    // Get HTML hidden field
    public static function field($name = 'csrf_token') {
        $token = self::get();
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }
    
    
    // Get token as JSON (for AJAX)
    public static function json($key = 'csrf_token') {
        return json_encode([
            $key => self::get()
        ]);
    }
    
    
    // Log validation failures   
    private static function logFailure($reason) {
        $log = date('Y-m-d H:i:s') . ' | ' . $reason . ' | IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
        error_log($log, 3, '/tmp/csrf_failures.log');
    }
    
    
    // Get token info (debugging) 
    public static function info() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return [
            'exists' => !empty($_SESSION[self::$sessionKey]),
            'age' => isset($_SESSION[self::$sessionTime]) ? time() - $_SESSION[self::$sessionTime] : null,
            'expired' => isset($_SESSION[self::$sessionTime]) ? (time() - $_SESSION[self::$sessionTime]) > self::$tokenExpiry : null,
            'max_age' => self::$tokenExpiry
        ];
    }
}
