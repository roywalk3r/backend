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

require_once __DIR__. '/../classes/BookingManager.php';
require_once  __DIR__. '/../classes/SecurityHelper.php';
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
    $required_fields = ['customer_name', 'customer_email', 'customer_phone', 'service_id', 'booking_date', 'booking_time'];
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
    $input['customer_phone'] = SecurityHelper::sanitizeInput($input['customer_phone']);
    $input['notes'] = isset($input['notes']) ? SecurityHelper::sanitizeInput($input['notes']) : '';
    
    // Validate email format
    if (!filter_var($input['customer_email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Validate service_id is numeric
    if (!is_numeric($input['service_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
        exit;
    }
    
    // Validate date format
    $date = DateTime::createFromFormat('Y-m-d', $input['booking_date']);
    if (!$date || $date->format('Y-m-d') !== $input['booking_date']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
        exit;
    }
    
    // Validate time format
    $time = DateTime::createFromFormat('H:i', $input['booking_time']);
    if (!$time || $time->format('H:i') !== $input['booking_time']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid time format. Use HH:MM']);
        exit;
    }
    
    $bookingManager = new App\BookingManager();
    $result = $bookingManager->createBooking($input);
    
    if ($result['success']) {
        http_response_code(201);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Booking API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred. Please try again later.'
    ]);
}
?>