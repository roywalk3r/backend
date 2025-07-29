<?php
namespace App;
require_once __DIR__ . '/../config/env.php';

/**
 * Database Checker Class
 * 
 * Checks database connection and setup status
 * Redirects to setup if database is not configured
 * 
 * @author Nananom Farms Development Team
 * @version 1.0
 */
class DatabaseChecker {
    
    /**
     * Check if database connection is available and configured
     * 
     * @return array Result with success status and message
     */
    public static function checkConnection() {
        try {
            // Check if database configuration exists
            $host = env('DB_HOST');
            $database = env('DB_NAME');
            $username = env('DB_USER');
            
            if (empty($host) || empty($database) || empty($username)) {
                return [
                    'success' => false,
                    'message' => 'Database configuration missing',
                    'redirect' => 'web_setup.php'
                ];
            }
            
            // Try to connect to database
            $connection = new \mysqli(
                $host,
                $username,
                env('DB_PASS', ''),
                $database,
                env('DB_PORT', 3306)
            );
            
            if ($connection->connect_error) {
                return [
                    'success' => false,
                    'message' => 'Database connection failed: ' . $connection->connect_error,
                    'redirect' => 'web_setup.php'
                ];
            }
            
            // Check if essential tables exist
            $requiredTables = ['users', 'roles', 'services', 'bookings', 'enquiries'];
            $missingTables = [];
            
            foreach ($requiredTables as $table) {
                $result = $connection->query("SHOW TABLES LIKE '$table'");
                if ($result->num_rows === 0) {
                    $missingTables[] = $table;
                }
            }
            
            if (!empty($missingTables)) {
                return [
                    'success' => false,
                    'message' => 'Missing database tables: ' . implode(', ', $missingTables),
                    'redirect' => 'web_setup.php'
                ];
            }
            
            $connection->close();
            
            return [
                'success' => true,
                'message' => 'Database connection successful'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Database check failed: ' . $e->getMessage(),
                'redirect' => 'web_setup.php'
            ];
        }
    }
    
    /**
     * Check database setup and redirect if needed
     * Call this function at the beginning of pages that require database access
     * 
     * @param string $currentFile Current file name to avoid redirect loops
     */
    public static function requireDatabase($currentFile = '') {
        // Skip check for setup files
        $setupFiles = ['web_setup.php', 'setup_handler.php', 'setup_database.php'];
        
        if (in_array($currentFile, $setupFiles)) {
            return;
        }
        
        $check = self::checkConnection();
        
        if (!$check['success']) {
            // Determine the correct path to setup file
            $setupPath = 'web_setup.php';
            
            // If we're in a subdirectory, adjust the path
            if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
                $setupPath = '../web_setup.php';
            } elseif (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                $setupPath = '../web_setup.php';
            } elseif (strpos($_SERVER['REQUEST_URI'], '/public/') !== false) {
                $setupPath = '../web_setup.php';
            }
            
            // For API calls, return JSON error instead of redirect
            if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                header('Content-Type: application/json');
                http_response_code(503);
                echo json_encode([
                    'success' => false,
                    'message' => 'Database not configured. Please run setup first.',
                    'setup_required' => true
                ]);
                exit;
            }
            
            // For regular pages, redirect to setup
            header("Location: $setupPath");
            exit;
        }
    }
    
    /**
     * Check if setup is completed
     * 
     * @return bool True if setup is completed
     */
    public static function isSetupCompleted() {
        $check = self::checkConnection();
        return $check['success'] && !empty($check['message']);
    }
}
?>