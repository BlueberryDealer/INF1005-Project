<?php
/**
 * SessionManager Class
 * Handles user login, logout, and session storage
 * Keeps track of who is logged in
 */
class SessionManager {
    
    // Session timeout - user logs out after 1 hour of inactivity
    private const SESSION_TIMEOUT = 3600; // 3600 seconds
    
    // Constructor - Start session when object is created
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            if (!@session_start()) { // Try to start session, suppress warnings
                // If session fails
                echo "Cannot start session. Please try again.";
                exit; // Stop execution so the app doesn't break
            }
        }
    }
    
    /**
     * Create a new session after successful login
     * Store user info in $_SESSION to remember they're logged in
     */
    public function createSession(
        int $userId,
        string $lname,
        string $role,
        string $email
    ): bool {
        try {
            // Create new session ID to prevent session hijacking
            session_regenerate_id(true);
            
            // Store user info in session
            $_SESSION['user_id'] = $userId;
            $_SESSION['lname'] = $lname;
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $email;
            $_SESSION['created_at'] = time(); // Record when session started
            
            return true;
        } catch (\Exception $e) {
            error_log("Session creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user is logged in 
     * Also checks if session has timed out
     */
    public function isAuthenticated(): bool {
        // Check if user_id exists in session
        if (empty($_SESSION['user_id'])) {
            return false;
        }
        
        // Check if session has timed out
        if (!isset($_SESSION['created_at'])) {
            return false;
        }
        
        // Calculate how long session has been active
        $sessionAge = time() - $_SESSION['created_at'];
        
        // If session older than timeout, log them out
        if ($sessionAge > self::SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    
    // Get the current user's ID
    public function getUserId(): ?int {
        if (!$this->isAuthenticated()) {
            return null;
        }
        return $_SESSION['user_id'];
    }
    
    // Get the current user's lname
    public function getlname(): ?string {
        if (!$this->isAuthenticated()) {
            return null;
        }
        return $_SESSION['lname'];
    }
    
    // Get the current user's email
    public function getEmail(): ?string {
        if (!$this->isAuthenticated()) {
            return null;
        }
        return $_SESSION['email'];
    }
    
    // Get the current user's role (admin or user)
    public function getRole(): ?string {
        if (!$this->isAuthenticated()) {
            return null;
        }
        return $_SESSION['role'];
    }
    
    // Get all user data at once
    public function getUserData(): ?array {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'lname' => $_SESSION['lname'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    
    /**
     * Refresh session timeout on each page view
     * This keeps user logged in as long as they're using the site
     */
    public function refreshSession(): void {
        if ($this->isAuthenticated()) {
            $_SESSION['created_at'] = time();
        }
    }
    
    // Log out the user. Clears all session data and destroys the session
    public function logout(): void {
        // Clear all session data
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
    }
}
