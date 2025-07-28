<?php
return function($conn) {
  $sql = "CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(100),
        price DECIMAL(10,2),
        duration VARCHAR(50),
        availability ENUM('available', 'unavailable', 'seasonal') DEFAULT 'available',
        image_url VARCHAR(500),
        features JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "✅ Created 'services' table\n";
    } else {
        echo "❌ Error creating 'services': " . $conn->error . "\n";
    }
};