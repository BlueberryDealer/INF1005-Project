<?php
// Role Manager class for authentication based on roles
class RoleManager {
    
    private SessionManager $session;
    
    // Define the two roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    
    // Constructor - receives SessionManager to check who is logged in
    public function __construct(SessionManager $session) {
        $this->session = $session;
    }
    
    // Check if user is logged in
    public function isAuthenticated(): bool {
        return $this->session->isAuthenticated();
    }
    
    // Check if user is an Admin
    public function isAdmin(): bool {
        if (!$this->session->isAuthenticated()) {
            return false;
        }
        return $this->session->getRole() === self::ROLE_ADMIN;
    }
    
    // Check if user is a normal User
    public function isUser(): bool {
        if (!$this->session->isAuthenticated()) {
            return false;
        }
        return $this->session->getRole() === self::ROLE_USER;
    }
    
    
    // These methods stop access and redirect if user is not allowed
    
    // Require user to be an Admin. If not admin, redirect them 
    public function requireAdmin(string $redirectUrl = '/pages/unauthorized.php'): bool {
        if (!$this->isAdmin()) {
            header("Location: $redirectUrl");
            exit;
        }
        return true;
    }
    
    
    // Require user to be logged in. If not logged in, redirect them
    public function requireAuthenticated(string $redirectUrl = '/pages/unauthorized.php'): bool {
        if (!$this->session->isAuthenticated()) {
            header("Location: $redirectUrl");
            exit;
        }
        return true;
    }
    

    // Get current user's role
    public function getRole(): ?string {
        if (!$this->session->isAuthenticated()) {
            return null;
        }
        return $this->session->getRole();
    }
    
    // Get current user's ID
    public function getUserId(): ?int {
        return $this->session->getUserId();
    }
    
    // Get current user's data
    public function getUserData(): ?array {
        return $this->session->getUserData();
    }
    
    /**
     * Convert role code to readable name
     * 'admin' becomes 'Administrator'
     * 'user' becomes 'User'
     */
    public static function getRoleDisplayName(string $role): string {
        return match($role) {
            'admin' => 'Administrator',
            'user' => 'User',
            default => 'Unknown'
        };
    }
}
