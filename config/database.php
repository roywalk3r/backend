<?php
require_once __DIR__ . '/env.php';

function getMysqliConnection() {
    static $connection = null;
    
    if ($connection === null) {
        $host = env('DB_HOST', 'localhost');
        $database = env('DB_NAME', 'nananom');
        $username = env('DB_USER', 'root');
        $password = env('DB_PASS', '');
        $port = env('DB_PORT', 3306);
        
        try {
            $connection = new mysqli($host, $username, $password, $database, $port);
            
            if ($connection->connect_error) {
                throw new Exception("Connection failed: " . $connection->connect_error);
            }
            
            $connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $connection;
}

function testDatabaseConnection() {
    try {
        $conn = getMysqliConnection();
        return [
            'success' => true,
            'message' => 'Database connection successful',
            'server_info' => $conn->server_info
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
?>