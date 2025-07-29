<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
use App\SecurityHelper;
// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../classes/EnquiryManager.php';
require_once '../classes/SecurityHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }
    
    // Validate required fields
    $required_fields = ['name', 'email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit;
        }
    }
    
    // Sanitize inputs
    $input['name'] = SecurityHelper::sanitizeInput($input['name']);
    $input['email'] = SecurityHelper::sanitizeInput($input['email']);
    $input['subject'] = SecurityHelper::sanitizeInput($input['subject']);
    $input['message'] = SecurityHelper::sanitizeInput($input['message']);
    $input['phone'] = isset($input['phone']) ? SecurityHelper::sanitizeInput($input['phone']) : '';
    
    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Validate message length
    if (strlen($input['message']) < 10) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message must be at least 10 characters long']);
        exit;
    }
    
    if (strlen($input['message']) > 1000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message must be less than 1000 characters']);
        exit;
    }
    
    $enquiryManager = new \App\EnquiryManager();
    $result = $enquiryManager->createEnquiry($input);
    
    if ($result['success']) {
        http_response_code(201);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Enquiry API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred. Please try again later.'
    ]);
}
?>