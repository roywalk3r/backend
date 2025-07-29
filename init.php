<?php
/**
 * Initialization file
 * 
 * This file should be included at the beginning of all major entry points
 * to ensure proper database setup and configuration
 */

// Start output buffering to prevent header issues
use App\DatabaseChecker;
ob_start();

// Set error reporting for development (adjust for production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Ensure logs directory exists
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Include database checker
require_once __DIR__ . '/classes/DatabaseChecker.php';

// Get current file name
$currentFile = basename($_SERVER['SCRIPT_NAME']);

// Check database setup (will redirect if needed)
DatabaseChecker::requireDatabase($currentFile);

// Clean output buffer
ob_end_clean();
?>