<?php
return function($conn) {
     $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45),
        success BOOLEAN DEFAULT FALSE,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_attempted_at (attempted_at)
    )";
    if ($conn->query($sql) === TRUE) {
        echo "Created 'users' table\n";
    } else {
        echo "Error creating 'users': " . $conn->error . "\n";
    }
};