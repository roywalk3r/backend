<?php
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once __DIR__ . '/../classes/DatabaseChecker.php';
use App\DatabaseChecker;

// Check if setup is required
if (!DatabaseChecker::isDatabaseConnected()) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'System setup required. Please contact administrator.'
    ]);
    exit;
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $firstName = trim($input['firstName'] ?? '');
    $lastName = trim($input['lastName'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $password = $input['password'] ?? '';
    $confirmPassword = $input['confirmPassword'] ?? '';
    $agreeTerms = isset($input['agreeTerms']) && $input['agreeTerms'];
    $newsletter = isset($input['newsletter']) && $input['newsletter'];
    
    // Validate input
    $errors = [];
    
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($password)) $errors[] = 'Password is required';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (!$agreeTerms) {
        $errors[] = 'You must agree to the terms and conditions';
    }
    
    // Validate phone number
    $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
    if (strlen($cleanPhone) < 10) {
        $errors[] = 'Invalid phone number';
    }
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $errors)
        ]);
        exit;
    }
    
    $db = getMysqliConnection();

    $auth = new App\Auth();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND role_id = 3");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'An account with this email already exists'
        ]);
        exit;
    }
    
    // Create customer account
    $result = $auth->registerCustomer([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => $phone,
        'password' => $password,
        'newsletter' => $newsletter
    ]);
    
    if ($result['success']) {
        // Log registration
        error_log("New customer registered: {$email}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Please login with your credentials.',
            'user_id' => $result['user_id']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Registration failed'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Customer registration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during registration'
    ]);
}
?>