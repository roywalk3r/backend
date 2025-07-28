<?php
return function ($conn) {
    $sql = "
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    if ($conn->query($sql)) {
        echo "✅ Table 'migrations' created or already exists.\n";
    } else {
        echo "❌ Failed to create 'migrations' table: " . $conn->error . "\n";
    }
};
