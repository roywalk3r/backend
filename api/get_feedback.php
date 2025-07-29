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

require_once '../classes/FeedbackManager.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $feedbackManager = new App\FeedbackManager();
    
    // Get and validate query parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $rating = isset($_GET['rating']) ? $_GET['rating'] : '';
    $service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;
    
    // Validate rating if provided
    if ($rating !== '' && (!is_numeric($rating) || $rating < 1 || $rating > 5)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid rating value']);
        exit;
    }
    
    $result = $feedbackManager->getFeedback($page, $limit, $search, $rating, $service_id);
    
    if ($result['success']) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Get Feedback API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred. Please try again later.'
    ]);
}
?>