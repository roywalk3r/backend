<?php
require_once __DIR__ . '/env.php';
// Database configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'nananom'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_PORT', '3306');

// Application settings
define('APP_NAME', env('APP_NAME', 'Nananom Farms'));
define('APP_URL', env('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME'])));

// Security settings
define('SESSION_TIMEOUT', env('SESSION_TIMEOUT', 3600)); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Pagination
define('RECORDS_PER_PAGE', 10);

// Environment helper function
// function env($key, $default = null) {
//     return $_ENV[$key] ?? $default;
// }

function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>