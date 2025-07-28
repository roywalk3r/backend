<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response

require_once 'config/env.php';

function respond($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    if (!isset($_POST['action'])) {
        respond(false, 'No action specified');
    }
    
    $action = $_POST['action'];
    
    if ($action === 'test_connection') {
        $host = $_POST['db_host'] ?? 'localhost';
        $port = $_POST['db_port'] ?? 3306;
        $user = $_POST['db_user'] ?? 'root';
        $pass = $_POST['db_pass'] ?? '';
        $dbname = $_POST['db_name'] ?? 'nananom';
        
        try {
            $conn = new mysqli($host, $user, $pass, null, $port);
            
            if ($conn->connect_error) {
                respond(false, "Connection failed: " . $conn->connect_error);
            }
            
            // Try to create database
            $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $conn->select_db($dbname);
            
            respond(true, "Database connection successful! Database '$dbname' is ready.");
            
        } catch (Exception $e) {
            respond(false, "Connection failed: " . $e->getMessage());
        }
        
    } elseif ($action === 'install') {
        $host = $_POST['db_host'] ?? 'localhost';
        $port = $_POST['db_port'] ?? 3306;
        $user = $_POST['db_user'] ?? 'root';
        $pass = $_POST['db_pass'] ?? '';
        $dbname = $_POST['db_name'] ?? 'nananom';
        
        $admin_first_name = $_POST['admin_first_name'] ?? 'System';
        $admin_last_name = $_POST['admin_last_name'] ?? 'Administrator';
        $admin_email = $_POST['admin_email'] ?? 'admin@nananomfarms.com';
        $admin_password = $_POST['admin_password'] ?? 'admin123';
        
        $steps = [];
        
        try {
            // Connect to database
            $conn = new mysqli($host, $user, $pass, null, $port);
            if ($conn->connect_error) {
                respond(false, "Database connection failed: " . $conn->connect_error);
            }
            
            // Create database
            $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $conn->select_db($dbname);
            $steps[] = "Database '$dbname' created/connected successfully";
            
            // Create roles table
            $sql = "CREATE TABLE IF NOT EXISTS roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $conn->query($sql);
            $steps[] = "Roles table created";
            
            // Insert default roles
            $conn->query("INSERT IGNORE INTO roles (id, name, description) VALUES 
                (1, 'admin', 'System Administrator'),
                (2, 'support_agent', 'Customer Support Agent'),
                (3, 'customer', 'Regular Customer')");
            $steps[] = "Default roles inserted";
            
            // Create users table
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
            $steps[] = "Users table created";
            
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
            $steps[] = "Services table created";
            
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
            $steps[] = "Enquiries table created";
            
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
                assigned_to INT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (service_id) REFERENCES services(id)
            )";
            $conn->query($sql);
            $steps[] = "Bookings table created";
            
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
            $steps[] = "Feedback table created";
            
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
            $steps[] = "Login attempts table created";
            
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
            $steps[] = "Activity logs table created";
            
            // Insert sample services
            $services = [
                ['Organic Vegetable Farming', 'Complete organic vegetable farming services', 'Farming', 500.00, 'service'],
                ['Crop Consultation', 'Expert consultation on crop selection and farming techniques', 'Consultation', 150.00, 'hour'],
                ['Soil Testing', 'Comprehensive soil analysis and recommendations', 'Testing', 75.00, 'test'],
                ['Irrigation Setup', 'Design and installation of efficient irrigation systems', 'Installation', 800.00, 'project'],
                ['Harvest Management', 'Professional harvesting and post-harvest handling services', 'Management', 300.00, 'service']
            ];
            
            $stmt = $conn->prepare("INSERT IGNORE INTO services (name, description, category, price, unit) VALUES (?, ?, ?, ?, ?)");
            foreach ($services as $service) {
                $stmt->bind_param("sssds", $service[0], $service[1], $service[2], $service[3], $service[4]);
                $stmt->execute();
            }
            $steps[] = "Sample services inserted";
            
            // Create admin user
            $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT IGNORE INTO users (first_name, last_name, email, password, role_id, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $role_id = 1;
            $is_active = 1;
            $stmt->bind_param("ssssii", $admin_first_name, $admin_last_name, $admin_email, $password_hash, $role_id, $is_active);
            
            if ($stmt->execute()) {
                $steps[] = "Admin user created successfully";
            } else {
                $steps[] = "Admin user already exists or creation failed";
            }
            
            // Update .env file
            $envContent = "DB_HOST=$host\n";
            $envContent .= "DB_USER=$user\n";
            $envContent .= "DB_PASS=$pass\n";
            $envContent .= "DB_NAME=$dbname\n";
            $envContent .= "DB_PORT=$port\n";
            
            if (file_put_contents('.env', $envContent)) {
                $steps[] = "Environment configuration saved";
            }
            
            respond(true, "Installation completed successfully!", $steps);
            
        } catch (Exception $e) {
            respond(false, "Installation failed: " . $e->getMessage());
        }
    } else {
        respond(false, 'Invalid action');
    }
    
} catch (Exception $e) {
    respond(false, "Error: " . $e->getMessage());
}
?>