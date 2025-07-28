<?php
namespace App;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Authentication Class
 * 
 * Handles user authentication, session management, and security features
 * including login attempts tracking, password hashing, and role-based access control.
 * 
 * @author Nananom Farms Development Team
 * @version 1.0
 */
class Auth {
    private $conn;
    private $sessionTimeout = 3600; // 1 hour in seconds
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 900; // 15 minutes in seconds
    
    /**
     * Constructor - Initialize database connection and start session
     */
    public function __construct() {
        $this->conn = getMysqliConnection();
        $this->startSecureSession();
    }
    
    /**
     * Start a secure session with proper configuration
     */
    private function startSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure secure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
            
            // Regenerate session ID periodically for security
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } elseif (time() - $_SESSION['created'] > 1800) { // 30 minutes
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }
    
    /**
     * Authenticate user login
     * 
     * @param string $email User email address
     * @param string $password User password
     * @param bool $rememberMe Whether to set remember me cookie
     * @return array Result array with success status and message
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            // Input validation
            if (empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Email and password are required'
                ];
            }
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format'
                ];
            }
            
            // Check for account lockout
            if ($this->isAccountLocked($email)) {
                return [
                    'success' => false,
                    'message' => 'Account temporarily locked due to too many failed attempts. Please try again later.'
                ];
            }
            
            // Prepare and execute user lookup query
            $stmt = $this->conn->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.email = ? AND u.is_active = 1
            ");
            
            if (!$stmt) {
                throw new Exception("Database prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Clear failed attempts on successful login
                    $this->clearFailedAttempts($email);
                    
                    // Set secure session data
                    $this->setUserSession($user);
                    
                    // Update last login timestamp
                    $this->updateLastLogin($user['id']);
                    
                    // Handle remember me functionality
                    if ($rememberMe) {
                        $this->setRememberMeToken($user['id']);
                    }
                    
                    // Log successful login
                    $this->logActivity($user['id'], 'login', 'users', $user['id']);
                    
                    return [
                        'success' => true,
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user['id'],
                            'name' => $user['first_name'] . ' ' . $user['last_name'],
                            'email' => $user['email'],
                            'role' => $user['role_name']
                        ]
                    ];
                } else {
                    // Record failed attempt
                    $this->recordFailedAttempt($email);
                    return [
                        'success' => false,
                        'message' => 'Invalid email or password'
                    ];
                }
            } else {
                // Record failed attempt even for non-existent users (prevent enumeration)
                $this->recordFailedAttempt($email);
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ];
        }
    }
    
    /**
     * Set user session data securely
     * 
     * @param array $user User data from database
     */
    private function setUserSession($user) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role_name'];
        $_SESSION['login_time'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Logout user and clean up session
     * 
     * @return array Result array with success status
     */
    public function logout() {
        try {
            // Log logout activity
            if (isset($_SESSION['user_id'])) {
                $this->logActivity($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id']);
            }
            
            // Clear remember me cookie and token
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
                if (isset($_SESSION['user_id'])) {
                    $this->clearRememberMeToken($_SESSION['user_id']);
                }
            }
            
            // Destroy session
            session_unset();
            session_destroy();
            
            return [
                'success' => true,
                'message' => 'Logged out successfully'
            ];
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Logout failed'
            ];
        }
    }
    
    /**
     * Check if user is currently logged in
     * 
     * @return bool True if user is logged in and session is valid
     */
    public function isLoggedIn() {
        // Check basic session data
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
            return false;
        }
        
        // Check session timeout
        if (time() - $_SESSION['login_time'] > $this->sessionTimeout) {
            $this->logout();
            return false;
        }
        
        // Check IP address consistency (basic session hijacking protection)
        if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
            error_log("Session IP mismatch for user ID: " . $_SESSION['user_id']);
            $this->logout();
            return false;
        }
        
        // Update last activity time
        $_SESSION['login_time'] = time();
        
        return true;
    }
    
    /**
     * Require authentication - redirect if not logged in
     */
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: index.php');
            exit;
        }
    }
    
    /**
     * Get current authenticated user data
     * 
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->conn->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ? AND u.is_active = 1
            ");
            
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                unset($user['password']); // Remove password from response
                return $user;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if user has specific role
     * 
     * @param string $role Role name to check
     * @return bool True if user has the role
     */
    public function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }
    
    /**
     * Check if user has specific permission
     * 
     * @param string $permission Permission to check
     * @return bool True if user has permission
     */
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $role = $_SESSION['user_role'] ?? '';
        
        // Admin has all permissions
        if ($role === 'admin') {
            return true;
        }
        
        // Define role-based permissions
        $permissions = [
            'support_agent' => [
                'view_enquiries', 'edit_enquiries', 'assign_enquiries',
                'view_bookings', 'edit_bookings', 'assign_bookings',
                'view_feedback', 'view_services'
            ],
            'customer' => [
                'view_profile', 'edit_profile', 'create_booking', 'create_enquiry'
            ]
        ];
        
        return isset($permissions[$role]) && in_array($permission, $permissions[$role]);
    }
    
    /**
     * Generate and validate CSRF tokens
     * 
     * @return string CSRF token
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool True if token is valid
     */
    public function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Record failed login attempt
     * 
     * @param string $email Email address of failed attempt
     */
    private function recordFailedAttempt($email) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt = $this->conn->prepare("
                INSERT INTO login_attempts (username, ip_address, user_agent) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("sss", $email, $ip, $userAgent);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Record failed attempt error: " . $e->getMessage());
        }
    }
    
    /**
     * Clear failed login attempts for user
     * 
     * @param string $email Email address to clear attempts for
     */
    private function clearFailedAttempts($email) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM login_attempts WHERE username = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Clear failed attempts error: " . $e->getMessage());
        }
    }
    
    /**
     * Check if account is locked due to failed attempts
     * 
     * @param string $email Email address to check
     * @return bool True if account is locked
     */
    private function isAccountLocked($email) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as attempts 
                FROM login_attempts 
                WHERE username = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->bind_param("si", $email, $this->lockoutDuration);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['attempts'] >= $this->maxLoginAttempts;
        } catch (Exception $e) {
            error_log("Check account lock error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set remember me token for user
     * 
     * @param int $userId User ID
     */
    private function setRememberMeToken($userId) {
        try {
            $token = bin2hex(random_bytes(32));
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedToken, $userId);
            $stmt->execute();
            
            // Set secure cookie
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', isset($_SERVER['HTTPS']), true);
        } catch (Exception $e) {
            error_log("Set remember me token error: " . $e->getMessage());
        }
    }
    
    /**
     * Clear remember me token for user
     * 
     * @param int $userId User ID
     */
    private function clearRememberMeToken($userId) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Clear remember me token error: " . $e->getMessage());
        }
    }
    
    /**
     * Update user's last login timestamp
     * 
     * @param int $userId User ID
     */
    private function updateLastLogin($userId) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
    
    /**
     * Log user activity for audit trail
     * 
     * @param int $userId User ID
     * @param string $action Action performed
     * @param string $tableName Table affected
     * @param int $recordId Record ID affected
     * @param array $oldValues Old values (optional)
     * @param array $newValues New values (optional)
     */
    private function logActivity($userId, $action, $tableName, $recordId, $oldValues = null, $newValues = null) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt = $this->conn->prepare("
                INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
            $newValuesJson = $newValues ? json_encode($newValues) : null;
            
            $stmt->bind_param("ississss", $userId, $action, $tableName, $recordId, $oldValuesJson, $newValuesJson, $ip, $userAgent);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Log activity error: " . $e->getMessage());
        }
    }
}
?>