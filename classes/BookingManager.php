<?php
namespace App;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';

class BookingManager {
    private $conn;
    
    public function __construct() {
        $this->conn = getMysqliConnection();
    }
    
    public function getBookings($page = 1, $limit = 20, $search = '', $status = '', $date_from = '', $date_to = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $whereConditions = [];
            $params = [];
            $types = '';
            
            if (!empty($search)) {
                $whereConditions[] = "(CONCAT(b.first_name, ' ', b.last_name) LIKE ? OR b.email LIKE ? OR s.name LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
                $types .= 'sss';
            }
            
            if (!empty($status)) {
                $whereConditions[] = "b.status = ?";
                $params[] = $status;
                $types .= 's';
            }
            
            if (!empty($date_from)) {
                $whereConditions[] = "b.booking_date >= ?";
                $params[] = $date_from;
                $types .= 's';
            }
            
            if (!empty($date_to)) {
                $whereConditions[] = "b.booking_date <= ?";
                $params[] = $date_to;
                $types .= 's';
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get total count
            $countQuery = "
                SELECT COUNT(*) as total 
                FROM bookings b 
                LEFT JOIN services s ON b.service_id = s.id 
                $whereClause
            ";
            
            if (!empty($params)) {
                $countStmt = $this->conn->prepare($countQuery);
                $countStmt->bind_param($types, ...$params);
                $countStmt->execute();
                $countResult = $countStmt->get_result();
            } else {
                $countResult = $this->conn->query($countQuery);
            }
            
            $totalCount = $countResult->fetch_assoc()['total'];
            $totalPages = ceil($totalCount / $limit);
            
            // Get bookings with user assignment info
            $query = "
                SELECT b.*, 
                       s.name as service, 
                       s.category as service_category,
                       CONCAT(b.first_name, ' ', b.last_name) as customer_name,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_user
                FROM bookings b 
                LEFT JOIN services s ON b.service_id = s.id 
                LEFT JOIN users u ON b.assigned_to = u.id
                $whereClause 
                ORDER BY b.created_at DESC 
                LIMIT ? OFFSET ?
            ";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
            
            $stmt = $this->conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $bookings = [];
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
            
            return [
                'success' => true,
                'bookings' => $bookings,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'per_page' => $limit
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("Error getting bookings: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving bookings: ' . $e->getMessage()
            ];
        }
    }
    
    public function getAllBookings($page = 1, $limit = 10, $search = '', $status = '') {
        return $this->getBookings($page, $limit, $search, $status);
    }
    
    public function getBookingById($id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT b.*, 
                       s.name as service_name, 
                       s.category as service_category, 
                       s.price as service_price,
                       CONCAT(b.first_name, ' ', b.last_name) as customer_name,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_user
                FROM bookings b 
                LEFT JOIN services s ON b.service_id = s.id 
                LEFT JOIN users u ON b.assigned_to = u.id
                WHERE b.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return [
                    'success' => true,
                    'data' => $result->fetch_assoc()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Booking not found'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error getting booking: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving booking: ' . $e->getMessage()
            ];
        }
    }
    
    public function updateBookingStatus($id, $status, $notes = '') {
        try {
            // Validate status
            $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Invalid status provided'
                ];
            }
            
            // Check if booking exists
            $bookingCheck = $this->getBookingById($id);
            if (!$bookingCheck['success']) {
                return $bookingCheck;
            }
            
            $stmt = $this->conn->prepare("
                UPDATE bookings 
                SET status = ?, notes = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ssi", $status, $notes, $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Booking status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error updating booking status: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error updating booking status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating booking status: ' . $e->getMessage()
            ];
        }
    }
    
    public function assignBooking($id, $userId) {
        try {
            // Check if booking exists
            $bookingCheck = $this->getBookingById($id);
            if (!$bookingCheck['success']) {
                return $bookingCheck;
            }
            
            // Check if user exists
            $userCheck = $this->conn->prepare("SELECT id FROM users WHERE id = ?");
            $userCheck->bind_param("i", $userId);
            $userCheck->execute();
            
            if ($userCheck->get_result()->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            $stmt = $this->conn->prepare("
                UPDATE bookings 
                SET assigned_to = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $userId, $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Booking assigned successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error assigning booking: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error assigning booking: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error assigning booking: ' . $e->getMessage()
            ];
        }
    }
    
    public function createBooking($data) {
        try {
            // Validate required fields - updated to match API expectations
            $required = ['customer_name', 'customer_email', 'customer_phone', 'service_id', 'booking_date', 'booking_time'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Validate service exists
            $serviceCheck = $this->conn->prepare("SELECT id, price FROM services WHERE id = ? AND is_active = 1");
            $serviceCheck->bind_param("i", $data['service_id']);
            $serviceCheck->execute();
            $serviceResult = $serviceCheck->get_result();
            
            if ($serviceResult->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid service selected'
                ];
            }
            
            $service = $serviceResult->fetch_assoc();
            $quantity = $data['quantity'] ?? 1;
            $totalAmount = $service['price'] * $quantity;
            
            // Split customer_name into first_name and last_name for database storage
            $nameParts = explode(' ', trim($data['customer_name']), 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
            
            $stmt = $this->conn->prepare("
                INSERT INTO bookings (first_name, last_name, email, phone, service_id, booking_date, booking_time, quantity, total_amount, status, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            
            $phone = $data['customer_phone'];
            $email = $data['customer_email'];
            $bookingTime = $data['booking_time'];
            $notes = $data['notes'] ?? '';
            
            $stmt->bind_param("ssssssssds", 
                $firstName, 
                $lastName, 
                $email, 
                $phone,
                $data['service_id'],
                $data['booking_date'],
                $bookingTime,
                $quantity,
                $totalAmount,
                $notes
            );
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Booking created successfully',
                    'data' => ['id' => $this->conn->insert_id]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error creating booking: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error creating booking: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error creating booking: ' . $e->getMessage()
            ];
        }
    }
    
    public function updateBooking($id, $data) {
        try {
            // Check if booking exists
            $bookingCheck = $this->getBookingById($id);
            if (!$bookingCheck['success']) {
                return $bookingCheck;
            }
            
            $updateFields = [];
            $params = [];
            $types = '';
            
            $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'service_id', 'booking_date', 'booking_time', 'quantity', 'status', 'notes'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    // Validate service if updating service_id
                    if ($field === 'service_id') {
                        $serviceCheck = $this->conn->prepare("SELECT id, price FROM services WHERE id = ? AND is_active = 1");
                        $serviceCheck->bind_param("i", $data[$field]);
                        $serviceCheck->execute();
                        if ($serviceCheck->get_result()->num_rows === 0) {
                            return [
                                'success' => false,
                                'message' => 'Invalid service selected'
                            ];
                        }
                    }
                    
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                    
                    if (in_array($field, ['service_id', 'quantity'])) {
                        $types .= 'i';
                    } elseif (in_array($field, ['total_amount'])) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                }
            }
            
            // Recalculate total amount if service_id or quantity changed
            if (isset($data['service_id']) || isset($data['quantity'])) {
                $serviceId = $data['service_id'] ?? $bookingCheck['data']['service_id'];
                $quantity = $data['quantity'] ?? $bookingCheck['data']['quantity'];
                
                $priceCheck = $this->conn->prepare("SELECT price FROM services WHERE id = ?");
                $priceCheck->bind_param("i", $serviceId);
                $priceCheck->execute();
                $priceResult = $priceCheck->get_result();
                
                if ($priceResult->num_rows > 0) {
                    $service = $priceResult->fetch_assoc();
                    $totalAmount = $service['price'] * $quantity;
                    
                    $updateFields[] = "total_amount = ?";
                    $params[] = $totalAmount;
                    $types .= 'd';
                }
            }
            
            if (empty($updateFields)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }
            
            $updateFields[] = "updated_at = NOW()";
            $params[] = $id;
            $types .= 'i';
            
            $query = "UPDATE bookings SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Booking updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error updating booking: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error updating booking: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating booking: ' . $e->getMessage()
            ];
        }
    }
    
    public function deleteBooking($id) {
        try {
            // Check if booking exists
            $bookingCheck = $this->getBookingById($id);
            if (!$bookingCheck['success']) {
                return $bookingCheck;
            }
            
            $stmt = $this->conn->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Booking deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error deleting booking: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error deleting booking: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error deleting booking: ' . $e->getMessage()
            ];
        }
    }
    
    public function getBookingStats() {
        try {
            $stats = [];
            
            // Total bookings
            $result = $this->conn->query("SELECT COUNT(*) as total FROM bookings");
            $stats['total_bookings'] = $result->fetch_assoc()['total'];
            
            // Bookings by status
            $result = $this->conn->query("
                SELECT status, COUNT(*) as count 
                FROM bookings 
                GROUP BY status
            ");
            $stats['bookings_by_status'] = [];
            while ($row = $result->fetch_assoc()) {
                $stats['bookings_by_status'][$row['status']] = $row['count'];
            }
            
            // This month's bookings
            $result = $this->conn->query("
                SELECT COUNT(*) as count 
                FROM bookings 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats['bookings_this_month'] = $result->fetch_assoc()['count'];
            
            // Total revenue
            $result = $this->conn->query("
                SELECT SUM(total_amount) as total_revenue 
                FROM bookings 
                WHERE status IN ('confirmed', 'completed')
            ");
            $stats['total_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (\Exception $e) {
            error_log("Get booking stats error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving booking statistics: ' . $e->getMessage()
            ];
        }
    }
}
?>