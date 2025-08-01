<?php
namespace App;

require_once __DIR__ . '/../config/database.php';

class FeedbackManager {
    private $conn;
    
    public function __construct() {
        $this->conn = getMysqliConnection();
    }

    
    
    public function getAllFeedback($page = 1, $limit = 10, $search = '', $status = '', $rating = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $whereConditions = [];
            $params = [];
            $types = '';
            
            if (!empty($search)) {
                $whereConditions[] = "(f.customer_name LIKE ? OR f.email LIKE ? OR f.comment LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
                $types .= 'sss';
            }
            
            if (!empty($status)) {
                $whereConditions[] = "f.status = ?";
                $params[] = $status;
                $types .= 's';
            }
            
            if (!empty($rating)) {
                $whereConditions[] = "f.rating = ?";
                $params[] = $rating;
                $types .= 'i';
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM feedback f LEFT JOIN services s ON f.service_id = s.id $whereClause";
            
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
            
            // Get feedback
            $query = "
                SELECT f.id, f.customer_name, f.customer_email, f.phone, f.service_id, f.comment, f.rating, 
                       f.status, f.admin_response, f.created_at, f.updated_at, s.name as service_name
                FROM feedback f 
                LEFT JOIN services s ON f.service_id = s.id
                $whereClause 
                ORDER BY f.created_at DESC 
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
            
            $feedback = [];
            while ($row = $result->fetch_assoc()) {
                $feedback[] = $row;
            }
            
            return [
                'success' => true,
                'feedback' => $feedback,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'per_page' => $limit
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("Error getting feedback: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving feedback: ' . $e->getMessage(),
                'feedback' => [],
                'pagination' => [
                    'current_page' => 1,
                    'total_pages' => 0,
                    'total_count' => 0,
                    'per_page' => $limit
                ]
            ];
        }
    }
    
    public function getFeedbackById($id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT f.id, f.customer_name, f.email, f.phone, f.service_id, f.comment, f.rating, 
                       f.status, f.admin_response, f.created_at, f.updated_at, s.name as service_name
                FROM feedback f 
                LEFT JOIN services s ON f.service_id = s.id
                WHERE f.id = ?
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
                    'message' => 'Feedback not found'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error getting feedback: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving feedback: ' . $e->getMessage()
            ];
        }
    }
    
    public function updateFeedbackStatus($id, $status, $adminResponse = null) {
        try {
            $validStatuses = ['pending', 'approved', 'rejected'];
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Invalid status. Must be: ' . implode(', ', $validStatuses)
                ];
            }
            
            $updateFields = ["status = ?"];
            $params = [$status];
            $types = 's';
            
            if ($adminResponse !== null) {
                $updateFields[] = "admin_response = ?";
                $params[] = $adminResponse;
                $types .= 's';
            }
            
            $updateFields[] = "updated_at = NOW()";
            $params[] = $id;
            $types .= 'i';
            
            $query = "UPDATE feedback SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Feedback status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error updating feedback status: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error updating feedback status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating feedback status: ' . $e->getMessage()
            ];
        }
    }
    
    public function addAdminResponse($id, $response) {
        try {
            if (empty($response)) {
                return [
                    'success' => false,
                    'message' => 'Admin response cannot be empty'
                ];
            }
            
            $stmt = $this->conn->prepare("
                UPDATE feedback 
                SET admin_response = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("si", $response, $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Admin response added successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error adding admin response: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error adding admin response: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error adding admin response: ' . $e->getMessage()
            ];
        }
    }
    
    public function getFeedbackByRating($rating) {
        try {
            $stmt = $this->conn->prepare("
                SELECT f.id, f.customer_name, f.email, f.comment, f.rating, f.status, f.created_at, s.name as service_name
                FROM feedback f 
                LEFT JOIN services s ON f.service_id = s.id
                WHERE f.rating = ? 
                ORDER BY f.created_at DESC
            ");
            $stmt->bind_param("i", $rating);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $feedback = [];
            while ($row = $result->fetch_assoc()) {
                $feedback[] = $row;
            }
            
            return [
                'success' => true,
                'feedback' => $feedback
            ];
        } catch (\Exception $e) {
            error_log("Error getting feedback by rating: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving feedback by rating: ' . $e->getMessage(),
                'feedback' => []
            ];
        }
    }
    
    public function searchFeedback($searchTerm, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $searchPattern = "%$searchTerm%";
            
            // Get total count
            $countStmt = $this->conn->prepare("
                SELECT COUNT(*) as total 
                FROM feedback f
                LEFT JOIN services s ON f.service_id = s.id
                WHERE f.customer_name LIKE ? OR f.email LIKE ? OR f.comment LIKE ?
            ");
            $countStmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
            $countStmt->execute();
            $totalCount = $countStmt->get_result()->fetch_assoc()['total'];
            $totalPages = ceil($totalCount / $limit);
            
            // Get feedback
            $stmt = $this->conn->prepare("
                SELECT f.id, f.customer_name, f.email, f.phone, f.service_id, f.comment, f.rating, 
                       f.status, f.admin_response, f.created_at, f.updated_at, s.name as service_name
                FROM feedback f 
                LEFT JOIN services s ON f.service_id = s.id
                WHERE f.customer_name LIKE ? OR f.email LIKE ? OR f.comment LIKE ?
                ORDER BY f.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("sssii", $searchPattern, $searchPattern, $searchPattern, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $feedback = [];
            while ($row = $result->fetch_assoc()) {
                $feedback[] = $row;
            }
            
            return [
                'success' => true,
                'feedback' => $feedback,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'per_page' => $limit
                ]
            ];
        } catch (\Exception $e) {
            error_log("Error searching feedback: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error searching feedback: ' . $e->getMessage(),
                'feedback' => [],
                'pagination' => [
                    'current_page' => 1,
                    'total_pages' => 0,
                    'total_count' => 0,
                    'per_page' => $limit
                ]
            ];
        }
    }
    
    public function getFeedbackStats() {
        try {
            $stats = [];
            
            // Total feedback
            $result = $this->conn->query("SELECT COUNT(*) as total FROM feedback");
            $stats['total_feedback'] = $result->fetch_assoc()['total'];
            
            // Feedback by status
            $result = $this->conn->query("
                SELECT status, COUNT(*) as count 
                FROM feedback 
                GROUP BY status
            ");
            $stats['feedback_by_status'] = [];
            while ($row = $result->fetch_assoc()) {
                $stats['feedback_by_status'][$row['status']] = $row['count'];
            }
            
            // Average rating
            $result = $this->conn->query("SELECT AVG(rating) as avg_rating FROM feedback WHERE rating IS NOT NULL");
            $avgRating = $result->fetch_assoc()['avg_rating'];
            $stats['average_rating'] = $avgRating ? round($avgRating, 2) : 0;
            
            // Feedback by rating
            $result = $this->conn->query("
                SELECT rating, COUNT(*) as count 
                FROM feedback 
                WHERE rating IS NOT NULL
                GROUP BY rating 
                ORDER BY rating
            ");
            $stats['feedback_by_rating'] = [];
            while ($row = $result->fetch_assoc()) {
                $stats['feedback_by_rating'][$row['rating']] = $row['count'];
            }
            
            // Recent feedback (last 30 days)
            $result = $this->conn->query("
                SELECT COUNT(*) as recent_count 
                FROM feedback 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats['recent_feedback'] = $result->fetch_assoc()['recent_count'];
            
            return [
                'success' => true,
                'stats' => $stats
            ];
            
        } catch (\Exception $e) {
            error_log("Get feedback stats error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving feedback statistics: ' . $e->getMessage()
            ];
        }
    }
    
    public function deleteFeedback($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM feedback WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Feedback deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error deleting feedback: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error deleting feedback: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error deleting feedback: ' . $e->getMessage()
            ];
        }
    }
    
    public function getFeedback($page = 1, $limit = 10, $search = '', $rating = '', $status = '') {
        // Alias for getAllFeedback to maintain compatibility with the original method signature
        return $this->getAllFeedback($page, $limit, $search, $status, $rating);
    }
    
   public function createFeedback($data) {
    try {
        $stmt = $this->conn->prepare("
            INSERT INTO feedback (first_name, last_name, email, rating, message, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->bind_param("sssis", 
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['rating'],
            $data['message']
        );
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Feedback created successfully',
                'feedback_id' => $this->conn->insert_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error creating feedback: ' . $stmt->error
            ];
        }
    } catch (\Exception $e) {
        error_log("Error creating feedback: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error creating feedback: ' . $e->getMessage()
        ];
    }
}
}
?>