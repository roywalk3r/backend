<?php
/**
 * Environment Configuration
 * 
 * This file handles environment variables and configuration settings
 * for the Nananom Farms application.
 */

// Load environment variables from .env file if it exists
function loadEnvFile($filePath) {
    if (!file_exists($filePath)) {
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes if present
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
            $value = $matches[1];
        }
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
}

// Load .env file from the root directory
loadEnvFile(__DIR__ . '/../.env');

/**
 * Get environment variable with optional default value
 * 
 * @param string $key Environment variable key
 * @param mixed $default Default value if key doesn't exist
 * @return mixed Environment variable value or default
 */
function env($key, $default = null) {
    // Check $_ENV first
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }
    
    // Check $_SERVER
    if (array_key_exists($key, $_SERVER)) {
        return $_SERVER[$key];
    }
    
    // Check getenv()
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    
    return $default;
}

// Set default configuration values
$_ENV = array_merge([
    // Database Configuration
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'backend',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_PORT' => '3306',
    
    // Mail Configuration
    'MAIL_HOST' => 'localhost',
    'MAIL_PORT' => '1025',
    'MAIL_AUTH' => false,
    'MAIL_USERNAME' => '',
    'MAIL_PASSWORD' => '',
    'MAIL_ENCRYPTION' => '',
    'MAIL_FROM_ADDRESS' => 'noreply@nananom.com',
    'MAIL_FROM_NAME' => 'Nananom Farms',
    
    // Admin Configuration
    'ADMIN_EMAIL' => 'admin@nananom.com',
    
    // Application Configuration
    'APP_NAME' => 'Nananom Farms',
    'APP_URL' => 'http://localhost',
    'APP_ENV' => 'development',
    'APP_DEBUG' => 'true',
    
    // Email Notifications
    'SEND_LOGIN_NOTIFICATIONS' => 'true',
    'SEND_WELCOME_EMAILS' => 'true',
    'SEND_BOOKING_CONFIRMATIONS' => 'true',
    'SEND_ENQUIRY_ACKNOWLEDGMENTS' => 'true',
    'SEND_ADMIN_NOTIFICATIONS' => 'true',
    
], $_ENV);

// Convert string boolean values to actual booleans
function envBool($key, $default = false) {
    $value = env($key, $default);
    if (is_bool($value)) {
        return $value;
    }
    return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
}

// Convert string numeric values to integers
function envInt($key, $default = 0) {
    return (int) env($key, $default);
}

// Convert string numeric values to floats
function envFloat($key, $default = 0.0) {
    return (float) env($key, $default);
}
?>