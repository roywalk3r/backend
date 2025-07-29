<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../classes/FeedbackManager.php';
require_once '../classes/SecurityHelper.php';

use App\FeedbackManager;
use App\SecurityHelper;

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
    $required_fields = ['customer_name', 'customer_email', 'rating', 'comment'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit;
        }
    }
    
    // Sanitize inputs
    $input['customer_name'] = SecurityHelper::sanitizeInput($input['customer_name']);
    $input['customer_email'] = SecurityHelper::sanitizeInput($input['customer_email']);
    $input['comment'] = SecurityHelper::sanitizeInput($input['comment']);
    
    // Validate email format
    if (!filter_var($input['customer_email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Validate rating
    if (!is_numeric($input['rating']) || $input['rating'] < 1 || $input['rating'] > 5) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
        exit;
    }
    
    // Validate service_id if provided
    if (isset($input['service_id']) && !empty($input['service_id']) && !is_numeric($input['service_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
        exit;
    }
    
    // Validate comment length
    if (strlen($input['comment']) < 10) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Comment must be at least 10 characters long']);
        exit;
    }
    
    if (strlen($input['comment']) > 1000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Comment must be less than 1000 characters']);
        exit;
    }
    
    $feedbackManager = new FeedbackManager();
    $result = $feedbackManager->createFeedback($input);
    
    if ($result['success']) {
        http_response_code(201);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Feedback API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred. Please try again later.'
    ]);
}
?>