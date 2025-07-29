<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';

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
    
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $rememberMe = isset($input['rememberMe']) && $input['rememberMe'];
    
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
        exit;
    }
    
    $auth = new App\Auth();
    $result = $auth->login($email, $password, $rememberMe);
    
    if ($result['success']) {
        // Ensure user is a customer
        if (!isset($result['user']['role']) || $result['user']['role'] !== 'customer') {
            echo json_encode([
                'success' => false,
                'message' => 'Access denied. Customer account required.'
            ]);
            exit;
        }
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Customer login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Login failed. Please try again.'
    ]);
}
?>