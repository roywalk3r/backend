<?php
return function($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        role VARCHAR(50) DEFAULT 'customer',
        role_id INT DEFAULT 3,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        failed_attempts INT DEFAULT 0,
        last_failed_attempt TIMESTAMP NULL,
        last_login TIMESTAMP NULL,
        email_verified BOOLEAN DEFAULT FALSE,
        email_verification_token VARCHAR(255),
        password_reset_token VARCHAR(255),
        password_reset_expires TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id)
    )";
    if ($conn->query($sql) === TRUE) {
        echo "Created 'users' table\n";
    } else {
        echo "Error creating 'users': " . $conn->error . "\n";
    }
};