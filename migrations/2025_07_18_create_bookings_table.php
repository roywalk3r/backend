<?php
return function($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20),
        service_id INT NOT NULL,
        booking_date DATE NOT NULL,
        booking_time TIME,
        duration INT DEFAULT 60,
        status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
        total_amount DECIMAL(10,2),
        payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (service_id) REFERENCES services(id)
    )";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Created 'bookings' table\n";
    } else {
        echo " Error creating 'bookings': " . $conn->error . "\n";
    }
};