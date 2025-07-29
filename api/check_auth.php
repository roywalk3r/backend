<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../classes/Auth.php';

try {
    $auth = new App\Auth();
    
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        
        if ($user) {
            echo json_encode([
                'authenticated' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role_name']
                ]
            ]);
        } else {
            echo json_encode(['authenticated' => false]);
        }
    } else {
        echo json_encode(['authenticated' => false]);
    }
    
} catch (Exception $e) {
    error_log("Auth check error: " . $e->getMessage());
    echo json_encode(['authenticated' => false]);
}
?>