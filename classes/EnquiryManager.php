<?php
namespace App;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';

class EnquiryManager {
    private $conn;
    
  public function __construct() {
        $this->conn = getMysqliConnection();
    }
    
    public function getEnquiries($page = 1, $limit = 20, $search = '', $status = '', $priority = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $whereConditions = [];
            $params = [];
            $types = '';
            
            if (!empty($search)) {
                $whereConditions[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR e.email LIKE ? OR e.subject LIKE ? OR e.message LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
                $types .= 'sssss';
            }
            
            if (!empty($status)) {
                $whereConditions[] = "e.status = ?";
                $params[] = $status;
                $types .= 's';
            }
            
            if (!empty($priority)) {
                $whereConditions[] = "e.priority = ?";
                $params[] = $priority;
                $types .= 's';
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM enquiries e $whereClause";
            
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
            
            // Get enquiries with assigned user info
            $query = "
                SELECT e.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_user,
                       CONCAT(e.first_name, ' ', e.last_name) as customer_name,
                       s.name as service_name
                FROM enquiries e 
                LEFT JOIN users u ON e.assigned_to = u.id
                LEFT JOIN services s ON e.service_id = s.id
                $whereClause 
                ORDER BY e.created_at DESC 
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
            
            $enquiries = [];
            while ($row = $result->fetch_assoc()) {
                $enquiries[] = $row;
            }
            
            return [
                'success' => true,
                'enquiries' => $enquiries,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'per_page' => $limit
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error getting enquiries: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving enquiries: ' . $e->getMessage()
            ];
        }
    }
    
    public function getEnquiryById($id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT e.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_user,
                       CONCAT(e.first_name, ' ', e.last_name) as customer_name,
                       s.name as service_name
                FROM enquiries e 
                LEFT JOIN users u ON e.assigned_to = u.id
                LEFT JOIN services s ON e.service_id = s.id
                WHERE e.id = ?
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
                    'message' => 'Enquiry not found'
                ];
            }
        } catch (Exception $e) {
            error_log("Error getting enquiry: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving enquiry: ' . $e->getMessage()
            ];
        }
    }
    
    public function createEnquiry($data) {
        try {
            // Validate required fields
            $required = ['name', 'email', 'subject', 'message'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format'
                ];
            }
            
            // Split name into first and last name
            $name_parts = explode(' ', trim($data['name']), 2);
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
            
            $stmt = $this->conn->prepare("
                INSERT INTO enquiries (first_name, last_name, email, phone, subject, message, service_id, status, priority, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'new', 'medium', NOW())
            ");
            
            $phone = $data['phone'] ?? null;
            $service_id = isset($data['service_id']) ? (int)$data['service_id'] : null;
            
            $stmt->bind_param("ssssssi", 
                $first_name,
                $last_name,
                $data['email'], 
                $phone,
                $data['subject'],
                $data['message'],
                $service_id
            );
            
            if ($stmt->execute()) {
                $enquiry_id = $this->conn->insert_id;
                
                return [
                    'success' => true,
                    'message' => 'Enquiry submitted successfully',
                    'enquiry_id' => $enquiry_id
                ];
            } else {
                error_log("Enquiry creation failed: " . $stmt->error);
                return [
                    'success' => false,
                    'message' => 'Failed to submit enquiry'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Enquiry creation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Server error occurred'
            ];
        }
    }
    
    public function updateEnquiryStatus($id, $status, $response = '') {
        try {
            $validStatuses = ['new', 'in_progress', 'resolved', 'closed'];
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Invalid status provided'
                ];
            }
            
            // Check if enquiry exists
            $enquiryCheck = $this->getEnquiryById($id);
            if (!$enquiryCheck['success']) {
                return $enquiryCheck;
            }
            
            $stmt = $this->conn->prepare("
                UPDATE enquiries 
                SET status = ?, response = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ssi", $status, $response, $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Enquiry status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error updating enquiry status: ' . $stmt->error
                ];
            }
        } catch (Exception $e) {
            error_log("Error updating enquiry status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating enquiry status: ' . $e->getMessage()
            ];
        }
    }
    
    public function assignEnquiry($id, $userId) {
        try {
            // Check if enquiry exists
            $enquiryCheck = $this->getEnquiryById($id);
            if (!$enquiryCheck['success']) {
                return $enquiryCheck;
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
                UPDATE enquiries 
                SET assigned_to = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $userId, $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Enquiry assigned successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error assigning enquiry: ' . $stmt->error
                ];
            }
        } catch (Exception $e) {
            error_log("Error assigning enquiry: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error assigning enquiry: ' . $e->getMessage()
            ];
        }
    }
    
    public function getAssignableUsers() {
        try {
            $result = $this->conn->query("
                SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name, r.name as role
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.is_active = 1 
                ORDER BY u.first_name, u.last_name
            ");
            
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            
            return [
                'success' => true,
                'data' => $users
            ];
        } catch (Exception $e) {
            error_log("Error getting assignable users: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving users: ' . $e->getMessage()
            ];
        }
    }
    
    public function deleteEnquiry($id) {
        try {
            // Check if enquiry exists
            $enquiryCheck = $this->getEnquiryById($id);
            if (!$enquiryCheck['success']) {
                return $enquiryCheck;
            }
            
            $stmt = $this->conn->prepare("DELETE FROM enquiries WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Enquiry deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error deleting enquiry: ' . $stmt->error
                ];
            }
        } catch (Exception $e) {
            error_log("Error deleting enquiry: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error deleting enquiry: ' . $e->getMessage()
            ];
        }
    }
    
    public function getEnquiryStats() {
        try {
            $stats = [];
            
            // Total enquiries
            $result = $this->conn->query("SELECT COUNT(*) as total FROM enquiries");
            $stats['total_enquiries'] = $result->fetch_assoc()['total'];
            
            // Enquiries by status
            $result = $this->conn->query("
                SELECT status, COUNT(*) as count 
                FROM enquiries 
                GROUP BY status
            ");
            $stats['enquiries_by_status'] = [];
            while ($row = $result->fetch_assoc()) {
                $stats['enquiries_by_status'][$row['status']] = $row['count'];
            }
            
            // This month's enquiries
            $result = $this->conn->query("
                SELECT COUNT(*) as count 
                FROM enquiries 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats['enquiries_this_month'] = $result->fetch_assoc()['count'];
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("Get enquiry stats error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving enquiry statistics: ' . $e->getMessage()
            ];
        }
    }
}
?>