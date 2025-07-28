<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../classes/ServiceManager.php';

try {
    $serviceManager = new ServiceManager();
    $result = $serviceManager->getServices(1, 50, '', '', true); // Get all active services
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'services' => $result['services']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
