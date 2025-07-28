<?php
require_once 'config/config.php';
require_once 'config/database.php';
use App\Auth;
echo "<h2>Login Debug Information</h2>";

$conn = getDbConnection();

// Test database connection
echo "<h3>Database Connection Test</h3>";
if ($conn) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Check if users table exists
echo "<h3>Users Table Check</h3>";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "✅ Users table exists<br>";
} else {
    echo "❌ Users table does not exist<br>";
    exit;
}

// Check admin user
echo "<h3>Admin User Check</h3>";
$admin_email = 'admin@nananomfarms.com';
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, status, failed_attempts, created_at FROM users WHERE email = ?");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo "✅ Admin user found<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    echo "Status: " . $user['status'] . "<br>";
    echo "Failed Attempts: " . $user['failed_attempts'] . "<br>";
    echo "Created: " . $user['created_at'] . "<br>";
} else {
    echo "❌ Admin user not found<br>";
    exit;
}

// Test password verification
echo "<h3>Password Verification Test</h3>";
$test_password = 'admin123';
$stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if ($user_data) {
    echo "Password hash from database: " . substr($user_data['password'], 0, 50) . "...<br>";
    
    if (password_verify($test_password, $user_data['password'])) {
        echo "✅ Password verification successful<br>";
    } else {
        echo "❌ Password verification failed<br>";
        
        // Try to update the password
        echo "<h4>Updating Password...</h4>";
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $new_hash, $admin_email);
        
        if ($update_stmt->execute()) {
            echo "✅ Password updated successfully<br>";
            echo "New hash: " . substr($new_hash, 0, 50) . "...<br>";
            
            // Test again
            if (password_verify($test_password, $new_hash)) {
                echo "✅ New password verification successful<br>";
            } else {
                echo "❌ New password verification still failed<br>";
            }
        } else {
            echo "❌ Failed to update password<br>";
        }
    }
} else {
    echo "❌ Could not retrieve password hash<br>";
}

// Test Auth class
echo "<h3>Auth Class Test</h3>";
require_once 'classes/Auth.php';
$auth = new Auth();
$login_result = $auth->login($admin_email, $test_password);

echo "Login result: " . ($login_result['success'] ? '✅ Success' : '❌ Failed') . "<br>";
echo "Message: " . $login_result['message'] . "<br>";

if ($login_result['success']) {
    echo "User data: <pre>" . print_r($login_result['user'], true) . "</pre>";
}

$conn->close();
?>