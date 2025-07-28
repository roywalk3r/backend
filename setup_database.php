<?php
require_once 'config/env.php';
require_once 'config/database.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Nananom Farms Database Setup</h1>";

try {
    $conn = getMysqliConnection();
    
    // Create database if it doesn't exist
    $dbName = env('DB_NAME', 'nananom');
    $conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($dbName);
    
    echo "<p>✅ Database created/connected successfully</p>";
    
    // Create roles table
    $sql = "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    echo "<p>✅ Roles table created</p>";
    
    // Insert default roles
    $conn->query("INSERT IGNORE INTO roles (id, name, description) VALUES 
        (1, 'admin', 'System Administrator'),
        (2, 'support_agent', 'Customer Support Agent'),
        (3, 'customer', 'Regular Customer')");
    echo "<p>✅ Default roles inserted</p>";
    
    // Create users table with consistent schema
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        role_id INT DEFAULT 3,
        is_active BOOLEAN DEFAULT TRUE,
        remember_token VARCHAR(255),
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id)
    )";
    $conn->query($sql);
    echo "<p>✅ Users table created</p>";
    
    // Create services table
    $sql = "CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(100) DEFAULT 'general',
        price DECIMAL(10,2) DEFAULT 0.00,
        unit VARCHAR(50) DEFAULT 'piece',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    echo "<p>✅ Services table created</p>";
    
    // Create enquiries table
    $sql = "CREATE TABLE IF NOT EXISTS enquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
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
    $conn->query($sql);
    echo "<p>✅ Enquiries table created</p>";
    
    // Create bookings table
    $sql = "CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        service_id INT NOT NULL,
        booking_date DATE NOT NULL,
        booking_time TIME DEFAULT '09:00:00',
        quantity INT DEFAULT 1,
        total_amount DECIMAL(10,2) DEFAULT 0.00,
        status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (service_id) REFERENCES services(id)
    )";
    $conn->query($sql);
    echo "<p>✅ Bookings table created</p>";
    
    // Create feedback table
    $sql = "CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        message TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    echo "<p>✅ Feedback table created</p>";
    
    // Create login_attempts table
    $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45),
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_attempted_at (attempted_at)
    )";
    $conn->query($sql);
    echo "<p>✅ Login attempts table created</p>";
    
    // Create activity_logs table
    $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(100) NOT NULL,
        table_name VARCHAR(100),
        record_id INT,
        old_values JSON,
        new_values JSON,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $conn->query($sql);
    echo "<p>✅ Activity logs table created</p>";
    
    // Insert sample services
    $services = [
        ['Organic Vegetable Farming', 'Complete organic vegetable farming services including soil preparation, planting, and harvesting', 'Farming', 500.00, 'service'],
        ['Crop Consultation', 'Expert consultation on crop selection, farming techniques, and pest management', 'Consultation', 150.00, 'hour'],
        ['Soil Testing', 'Comprehensive soil analysis and recommendations for optimal crop growth', 'Testing', 75.00, 'test'],
        ['Irrigation Setup', 'Design and installation of efficient irrigation systems', 'Installation', 800.00, 'project'],
        ['Harvest Management', 'Professional harvesting and post-harvest handling services', 'Management', 300.00, 'service']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO services (name, description, category, price, unit) VALUES (?, ?, ?, ?, ?)");
    foreach ($services as $service) {
        $stmt->bind_param("sssds", $service[0], $service[1], $service[2], $service[3], $service[4]);
        $stmt->execute();
    }
    echo "<p>✅ Sample services inserted</p>";
    
    // Check if admin user exists
    $result = $conn->query("SELECT id FROM users WHERE email = 'admin@nananomfarms.com'");
    if ($result->num_rows == 0) {
        // Create admin user
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role_id, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $first_name = 'System';
        $last_name = 'Administrator';
        $email = 'admin@nananomfarms.com';
        $role_id = 1;
        $is_active = 1;
        
        $stmt->bind_param("ssssii", $first_name, $last_name, $email, $password_hash, $role_id, $is_active);
        
        if ($stmt->execute()) {
            echo "<p>✅ Admin user created successfully</p>";
            echo "<p><strong>Admin Login Details:</strong></p>";
            echo "<p>Email: admin@nananomfarms.com</p>";
            echo "<p>Password: admin123</p>";
        } else {
            echo "<p>❌ Error creating admin user: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p>ℹ️ Admin user already exists</p>";
    }
    
    echo "<h2>✅ Database setup completed successfully!</h2>";
    echo "<p><a href='admin/index.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>