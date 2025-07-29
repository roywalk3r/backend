<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';

try {
    $auth = new App\Auth();
    
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    $currentUser = $auth->getCurrentUser();
    if (!$currentUser) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    $conn = getMysqliConnection();
    
    // Get user's enquiries with service information
    $stmt = $conn->prepare("
        SELECT 
            e.*,
            s.name as service_name
        FROM enquiries e
        LEFT JOIN services s ON e.service_id = s.id
        WHERE e.email = ?
        ORDER BY e.created_at DESC
    ");
    
    $stmt->bind_param("s", $currentUser['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $enquiries = [];
    while ($row = $result->fetch_assoc()) {
        $enquiries[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'enquiries' => $enquiries
    ]);
    
} catch (Exception $e) {
    error_log("Get customer enquiries error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load enquiries'
    ]);
}
?>