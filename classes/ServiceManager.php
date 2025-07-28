<?php
namespace App;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SecurityHelper.php';

/**
 * Service Manager Class
 * 
 * Handles CRUD operations for services including creation, reading,
 * updating, and deletion with proper validation and security measures.
 * 
 * @author Nananom Farms Development Team
 * @version 1.0
 */
class ServiceManager {
    private $conn;
    private $auth;
    
    /**
     * Constructor
     * 
     * @param Auth $auth Authentication instance for logging
     */
    public function __construct($auth = null) {
        $this->conn = getMysqliConnection();
        $this->auth = $auth;
    }
    
    /**
     * Get all services with pagination and filtering
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @param string $search Search term
     * @param string $category Category filter
     * @param bool $activeOnly Whether to return only active services
     * @return array Result array with services and pagination info
     */
    public function getAllServices($page = 1, $limit = 10, $search = '', $category = '', $activeOnly = true) {
        try {
            // Validate and sanitize inputs
            $page = max(1, intval($page));
            $limit = max(1, min(100, intval($limit))); // Limit max items per page
            $search = SecurityHelper::sanitizeInput($search);
            $category = SecurityHelper::sanitizeInput($category);
            
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $whereConditions = [];
            $params = [];
            $types = '';
            
            if ($activeOnly) {
                $whereConditions[] = "is_active = 1";
            }
            
            if (!empty($search)) {
                $whereConditions[] = "(name LIKE ? OR description LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm]);
                $types .= 'ss';
            }
            
            if (!empty($category)) {
                $whereConditions[] = "category = ?";
                $params[] = $category;
                $types .= 's';
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) as total FROM services $whereClause";
            
            if (!empty($params)) {
                $countStmt = $this->conn->prepare($countQuery);
                if ($countStmt) {
                    $countStmt->bind_param($types, ...$params);
                    $countStmt->execute();
                    $countResult = $countStmt->get_result();
                } else {
                    throw new Exception("Failed to prepare count query: " . $this->conn->error);
                }
            } else {
                $countResult = $this->conn->query($countQuery);
                if (!$countResult) {
                    throw new Exception("Failed to execute count query: " . $this->conn->error);
                }
            }
            
            $totalCount = $countResult->fetch_assoc()['total'];
            $totalPages = ceil($totalCount / $limit);
            
            // Get services
            $query = "
                SELECT id, name, description, category, price, unit, is_active, created_at, updated_at
                FROM services 
                $whereClause 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare services query: " . $this->conn->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $services = [];
            while ($row = $result->fetch_assoc()) {
                // Format price for display
                $row['formatted_price'] = number_format($row['price'], 2);
                $services[] = $row;
            }
            
            return [
                'success' => true,
                'data' => $services,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'per_page' => $limit,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error getting services: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving services: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get service by ID
     * 
     * @param int $id Service ID
     * @param bool $activeOnly Whether to return only active services
     * @return array Result array with service data
     */
    public function getServiceById($id, $activeOnly = true) {
        try {
            $id = intval($id);
            if ($id <= 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid service ID'
                ];
            }
            
            $activeClause = $activeOnly ? "AND is_active = 1" : "";
            
            $stmt = $this->conn->prepare("
                SELECT id, name, description, category, price, unit, is_active, created_at, updated_at
                FROM services 
                WHERE id = ? $activeClause
            ");
            
            if (!$stmt) {
                throw new Exception("Failed to prepare query: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $service = $result->fetch_assoc();
                $service['formatted_price'] = number_format($service['price'], 2);
                
                return [
                    'success' => true,
                    'data' => $service
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Service not found'
                ];
            }
        } catch (Exception $e) {
            error_log("Error getting service: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving service: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create new service
     * 
     * @param array $data Service data
     * @return array Result array with success status and message
     */
    public function createService($data) {
        try {
            // Validate required fields
            $required = ['name', 'description'];
            $errors = [];
            
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $errors[] = "Field '$field' is required";
                }
            }
            
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => implode(', ', $errors)
                ];
            }
            
            // Sanitize inputs
            $name = SecurityHelper::sanitizeInput($data['name']);
            $description = SecurityHelper::sanitizeInput($data['description']);
            $category = SecurityHelper::sanitizeInput($data['category'] ?? 'general');
            $unit = SecurityHelper::sanitizeInput($data['unit'] ?? 'piece');
            
            // Validate price
            $price = floatval($data['price'] ?? 0.00);
            if ($price < 0) {
                return [
                    'success' => false,
                    'message' => 'Price cannot be negative'
                ];
            }
            
            // Check for duplicate service name
            $duplicateCheck = $this->conn->prepare("SELECT id FROM services WHERE name = ? AND is_active = 1");
            $duplicateCheck->bind_param("s", $name);
            $duplicateCheck->execute();
            
            if ($duplicateCheck->get_result()->num_rows > 0) {
                return [
                    'success' => false,
                    'message' => 'A service with this name already exists'
                ];
            }
            
            // Insert new service
            $stmt = $this->conn->prepare("
                INSERT INTO services (name, description, category, price, unit, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ");
            
            if (!$stmt) {
                throw new Exception("Failed to prepare insert query: " . $this->conn->error);
            }
            
            $stmt->bind_param("sssds", $name, $description, $category, $price, $unit);
            
            if ($stmt->execute()) {
                $serviceId = $this->conn->insert_id;
                
                // Log activity
                if ($this->auth && $this->auth->isLoggedIn()) {
                    $user = $this->auth->getCurrentUser();
                    $this->logActivity($user['id'], 'create', 'services', $serviceId, null, $data);
                }
                
                return [
                    'success' => true,
                    'message' => 'Service created successfully',
                    'data' => ['id' => $serviceId]
                ];
            } else {
                throw new Exception("Failed to execute insert query: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Error creating service: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error creating service: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update existing service
     * 
     * @param int $id Service ID
     * @param array $data Updated service data
     * @return array Result array with success status and message
     */
    public function updateService($id, $data) {
        try {
            $id = intval($id);
            if ($id <= 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid service ID'
                ];
            }
            
            // Check if service exists and get current data
            $currentService = $this->getServiceById($id, false);
            if (!$currentService['success']) {
                return $currentService;
            }
            
            $oldData = $currentService['data'];
            
            // Build update query dynamically
            $updateFields = [];
            $params = [];
            $types = '';
            
            $allowedFields = ['name', 'description', 'category', 'price', 'unit', 'is_active'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    if (in_array($field, ['name', 'description', 'category', 'unit'])) {
                        $value = SecurityHelper::sanitizeInput($data[$field]);
                        
                        // Check for duplicate name if updating name
                        if ($field === 'name' && $value !== $oldData['name']) {
                            $duplicateCheck = $this->conn->prepare("SELECT id FROM services WHERE name = ? AND id != ? AND is_active = 1");
                            $duplicateCheck->bind_param("si", $value, $id);
                            $duplicateCheck->execute();
                            
                            if ($duplicateCheck->get_result()->num_rows > 0) {
                                return [
                                    'success' => false,
                                    'message' => 'A service with this name already exists'
                                ];
                            }
                        }
                        
                        $updateFields[] = "$field = ?";
                        $params[] = $value;
                        $types .= 's';
                    } elseif ($field === 'price') {
                        $price = floatval($data[$field]);
                        if ($price < 0) {
                            return [
                                'success' => false,
                                'message' => 'Price cannot be negative'
                            ];
                        }
                        $updateFields[] = "$field = ?";
                        $params[] = $price;
                        $types .= 'd';
                    } elseif ($field === 'is_active') {
                        $updateFields[] = "$field = ?";
                        $params[] = intval($data[$field]);
                        $types .= 'i';
                    }
                }
            }
            
            if (empty($updateFields)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }
            
            // Add updated_at field
            $updateFields[] = "updated_at = NOW()";
            $params[] = $id;
            $types .= 'i';
            
            $query = "UPDATE services SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare update query: " . $this->conn->error);
            }
            
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                // Log activity
                if ($this->auth && $this->auth->isLoggedIn()) {
                    $user = $this->auth->getCurrentUser();
                    $this->logActivity($user['id'], 'update', 'services', $id, $oldData, $data);
                }
                
                return [
                    'success' => true,
                    'message' => 'Service updated successfully'
                ];
            } else {
                throw new Exception("Failed to execute update query: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Error updating service: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating service: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete service (soft delete if has dependencies, hard delete otherwise)
     * 
     * @param int $id Service ID
     * @return array Result array with success status and message
     */
    public function deleteService($id) {
        try {
            $id = intval($id);
            if ($id <= 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid service ID'
                ];
            }
            
            // Check if service exists
            $serviceCheck = $this->getServiceById($id, false);
            if (!$serviceCheck['success']) {
                return $serviceCheck;
            }
            
            $serviceData = $serviceCheck['data'];
            
            // Check for related records
            $relatedCheck = $this->conn->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM bookings WHERE service_id = ?) as booking_count
            ");
            $relatedCheck->bind_param("i", $id);
            $relatedCheck->execute();
            $related = $relatedCheck->get_result()->fetch_assoc();
            
            if ($related['booking_count'] > 0) {
                // Soft delete - deactivate service
                $stmt = $this->conn->prepare("UPDATE services SET is_active = 0, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    // Log activity
                    if ($this->auth && $this->auth->isLoggedIn()) {
                        $user = $this->auth->getCurrentUser();
                        $this->logActivity($user['id'], 'deactivate', 'services', $id, $serviceData, ['is_active' => 0]);
                    }
                    
                    return [
                        'success' => true,
                        'message' => 'Service deactivated successfully (has existing bookings)'
                    ];
                } else {
                    throw new Exception("Failed to deactivate service: " . $stmt->error);
                }
            } else {
                // Hard delete - no dependencies
                $stmt = $this->conn->prepare("DELETE FROM services WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    // Log activity
                    if ($this->auth && $this->auth->isLoggedIn()) {
                        $user = $this->auth->getCurrentUser();
                        $this->logActivity($user['id'], 'delete', 'services', $id, $serviceData, null);
                    }
                    
                    return [
                        'success' => true,
                        'message' => 'Service deleted successfully'
                    ];
                } else {
                    throw new Exception("Failed to delete service: " . $stmt->error);
                }
            }
            
        } catch (Exception $e) {
            error_log("Error deleting service: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error deleting service: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get service statistics
     * 
     * @return array Statistics data
     */
    public function getServiceStats() {
        try {
            $stats = [];
            
            // Total active services
            $result = $this->conn->query("SELECT COUNT(*) as total FROM services WHERE is_active = 1");
            if (!$result) {
                throw new Exception("Failed to get total services: " . $this->conn->error);
            }
            $stats['total_services'] = $result->fetch_assoc()['total'];
            
            // Services by category
            $result = $this->conn->query("
                SELECT category, COUNT(*) as count 
                FROM services 
                WHERE is_active = 1 
                GROUP BY category
                ORDER BY count DESC
            ");
            if (!$result) {
                throw new Exception("Failed to get services by category: " . $this->conn->error);
            }
            
            $stats['services_by_category'] = [];
            while ($row = $result->fetch_assoc()) {
                $stats['services_by_category'][$row['category']] = $row['count'];
            }
            
            // Price statistics
            $result = $this->conn->query("
                SELECT 
                    AVG(price) as avg_price,
                    MIN(price) as min_price,
                    MAX(price) as max_price
                FROM services 
                WHERE is_active = 1 AND price > 0
            ");
            if (!$result) {
                throw new Exception("Failed to get price statistics: " . $this->conn->error);
            }
            
            $priceStats = $result->fetch_assoc();
            $stats['average_price'] = round($priceStats['avg_price'] ?? 0, 2);
            $stats['min_price'] = round($priceStats['min_price'] ?? 0, 2);
            $stats['max_price'] = round($priceStats['max_price'] ?? 0, 2);
            
            // Recently added services (last 30 days)
            $result = $this->conn->query("
                SELECT COUNT(*) as count 
                FROM services 
                WHERE is_active = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            if (!$result) {
                throw new Exception("Failed to get recent services: " . $this->conn->error);
            }
            $stats['recent_services'] = $result->fetch_assoc()['count'];
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("Get service stats error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving service statistics: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all service categories
     * 
     * @return array List of categories
     */
    public function getCategories() {
        try {
            $result = $this->conn->query("
                SELECT DISTINCT category, COUNT(*) as service_count
                FROM services 
                WHERE is_active = 1 AND category IS NOT NULL AND category != ''
                GROUP BY category
                ORDER BY category
            ");
            
            if (!$result) {
                throw new Exception("Failed to get categories: " . $this->conn->error);
            }
            
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = [
                    'name' => $row['category'],
                    'count' => $row['service_count']
                ];
            }
            
            return [
                'success' => true,
                'data' => $categories
            ];
        } catch (Exception $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving categories: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Log activity for audit trail
     * 
     * @param int $userId User ID
     * @param string $action Action performed
     * @param string $tableName Table name
     * @param int $recordId Record ID
     * @param array $oldValues Old values
     * @param array $newValues New values
     */
    private function logActivity($userId, $action, $tableName, $recordId, $oldValues = null, $newValues = null) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt = $this->conn->prepare("
                INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
            $newValuesJson = $newValues ? json_encode($newValues) : null;
            
            $stmt->bind_param("ississss", $userId, $action, $tableName, $recordId, $oldValuesJson, $newValuesJson, $ip, $userAgent);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Log activity error: " . $e->getMessage());
        }
    }
}
?>