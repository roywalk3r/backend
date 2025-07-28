<?php
namespace App;

/**
 * Security Helper Class
 * 
 * Provides security utilities for input validation, XSS protection,
 * and other security-related functions.
 * 
 * @author Nananom Farms Development Team
 * @version 1.0
 */
class SecurityHelper {
    
    /**
     * Sanitize input to prevent XSS attacks
     * 
     * @param string $input Input string to sanitize
     * @return string Sanitized string
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email address
     * 
     * @param string $email Email to validate
     * @return bool True if valid email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (basic validation)
     * 
     * @param string $phone Phone number to validate
     * @return bool True if valid phone number
     */
    public static function validatePhone($phone) {
        return preg_match('/^[\+]?[0-9\s\-$$$$]{10,20}$/', $phone);
    }
    
    /**
     * Generate secure random password
     * 
     * @param int $length Password length
     * @return string Generated password
     */
    public static function generateSecurePassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return array Validation result with success and message
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Rate limiting check
     * 
     * @param string $key Unique key for rate limiting
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $timeWindow Time window in seconds
     * @return bool True if within rate limit
     */
    public static function checkRateLimit($key, $maxAttempts = 10, $timeWindow = 3600) {
        $filename = sys_get_temp_dir() . '/rate_limit_' . md5($key);
        
        if (!file_exists($filename)) {
            file_put_contents($filename, json_encode(['count' => 1, 'time' => time()]));
            return true;
        }
        
        $data = json_decode(file_get_contents($filename), true);
        
        if (time() - $data['time'] > $timeWindow) {
            // Reset counter
            file_put_contents($filename, json_encode(['count' => 1, 'time' => time()]));
            return true;
        }
        
        if ($data['count'] >= $maxAttempts) {
            return false;
        }
        
        $data['count']++;
        file_put_contents($filename, json_encode($data));
        return true;
    }
    
    /**
     * Clean filename for safe file uploads
     * 
     * @param string $filename Original filename
     * @return string Cleaned filename
     */
    public static function cleanFilename($filename) {
        // Remove any path information
        $filename = basename($filename);
        
        // Remove special characters except dots and hyphens
        $filename = preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $filename);
        
        // Limit length
        if (strlen($filename) > 100) {
            $filename = substr($filename, 0, 100);
        }
        
        return $filename;
    }
    
    /**
     * Validate file upload
     * 
     * @param array $file $_FILES array element
     * @param array $allowedTypes Allowed MIME types
     * @param int $maxSize Maximum file size in bytes
     * @return array Validation result
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) { // 5MB default
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'message' => 'Invalid file upload'];
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'message' => 'No file uploaded'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'message' => 'File too large'];
            default:
                return ['success' => false, 'message' => 'Upload error'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File too large'];
        }
        
        if (!empty($allowedTypes)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $allowedTypes)) {
                return ['success' => false, 'message' => 'File type not allowed'];
            }
        }
        
        return ['success' => true, 'message' => 'File is valid'];
    }
}
?>