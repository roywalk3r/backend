<?php

return function($conn) {
   $sql = "CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        subject VARCHAR(255),
        message TEXT NOT NULL,
        service_id INT,
        booking_id INT,
        status ENUM('new', 'reviewed', 'published') DEFAULT 'new',
        is_featured BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (service_id) REFERENCES services(id),
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
    )";

    if ($conn->query($sql) === TRUE) {
        echo "✅ Created 'feedback' table\n";
    } else {
        echo "❌ Error creating 'feedback': " . $conn->error . "\n";
         }
};