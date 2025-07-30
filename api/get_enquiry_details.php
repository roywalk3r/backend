<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../classes/Auth.php';
require_once '../classes/EnquiryManager.php';

use App\Auth;
use App\EnquiryManager;

try {
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    // Get enquiry ID
    $enquiry_id = $_GET['id'] ?? null;
    if (!$enquiry_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Enquiry ID is required']);
        exit;
    }

    $enquiryManager = new EnquiryManager();
    $result = $enquiryManager->getEnquiryById($enquiry_id);

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'enquiry' => $result['enquiry']
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
