<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../classes/BookingManager.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $bookingManager = new \App\BookingManager();
    
    // Get and validate query parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    
    // Validate date formats if provided
    if ($date_from && !DateTime::createFromFormat('Y-m-d', $date_from)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date_from format. Use YYYY-MM-DD']);
        exit;
    }
    
    if ($date_to && !DateTime::createFromFormat('Y-m-d', $date_to)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date_to format. Use YYYY-MM-DD']);
        exit;
    }
    
    $result = $bookingManager->getBookings($page, $limit, $search, $status, $service_id, $date_from, $date_to);
    
    if ($result['success']) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Get Bookings API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred. Please try again later.'
    ]);
}
?>