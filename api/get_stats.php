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

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
     $db = getMysqliConnection();
    
    // Get total counts
    $stats = [];
    
    // Total bookings
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bookings");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stats['total_bookings'] = $row ? $row['total'] : 0;
    
    // Total enquiries
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM enquiries");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stats['total_enquiries'] = $row ? $row['total'] : 0;
    
    // Total feedback
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM feedback");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stats['total_feedback'] = $row ? $row['total'] : 0;
    
    // Total services
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM services WHERE is_active = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stats['total_services'] = $row ? $row['total'] : 0;
    
    // Recent bookings (last 30 days)
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stats['recent_bookings'] = $row ? $row['total'] : 0;
    
    // Pending bookings
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stats['pending_bookings'] = $row ? $row['total'] : 0;
    
    // Average rating
    $stmt = $db->prepare("SELECT AVG(rating) as avg_rating FROM feedback");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stats['average_rating'] = ($row && $row['avg_rating']) ? round($row['avg_rating'], 1) : 0;
    
    // Booking status distribution
    $stmt = $db->prepare("
        SELECT status, COUNT(*) as count 
        FROM bookings 
        GROUP BY status
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['booking_status_distribution'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Monthly booking trends (last 6 months)
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM bookings 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['monthly_trends'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Top services by bookings
    $stmt = $db->prepare("
        SELECT 
            s.name as service_name,
            COUNT(b.id) as booking_count
        FROM services s
        LEFT JOIN bookings b ON s.id = b.service_id
        WHERE s.is_active = 1
        GROUP BY s.id, s.name
        ORDER BY booking_count DESC
        LIMIT 5
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['top_services'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Stats API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred. Please try again later.'
    ]);
}
?>