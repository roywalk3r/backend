<?php
return function($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    )";
    if ($conn->query($sql) === TRUE) {
        echo "Created 'roles' table successfully\n";
    } else {
        echo "Error creating 'roles': " . $conn->error . "\n";
    }
};
