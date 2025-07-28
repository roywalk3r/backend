<?php
return function($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS enquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        subject VARCHAR(255),
        message TEXT NOT NULL,
        service_id INT,
        status ENUM('new', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        assigned_to INT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (service_id) REFERENCES services(id),
        FOREIGN KEY (assigned_to) REFERENCES users(id)
    )";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Created 'enquiries' table\n";
    } else {
        echo "Error creating 'enquiries': " . $conn->error . "\n";
    }
};