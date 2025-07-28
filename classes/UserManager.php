<?php
namespace App;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';

class UserManager {
    private $conn;
    
    public function __construct() {
        $this->conn = getMysqliConnection();
    }
    
    public function getAllUsers($page = 1, $limit = 10, $search = '', $role = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $whereConditions = [];
            $params = [];
            $types = '';
            
            if (!empty($search)) {
                $whereConditions[] = "(CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm]);
                $types .= 'ss';
            }
            
            if (!empty($role)) {
                $whereConditions[] = "r.name = ?";
                $params[] = $role;
                $types .= 's';
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get total count
            $countQuery = "
                SELECT COUNT(*) as total 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
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
            
            // Get users
            $query = "
                SELECT u.id, u.first_name, u.last_name, u.email, u.phone, 
                       u.role_id, r.name as role, u.is_active, u.created_at, u.last_login
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id
                $whereClause 
                ORDER BY u.created_at DESC 
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
            
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            
            return [
                'success' => true,
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'per_page' => $limit
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("Error getting users: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving users: ' . $e->getMessage(),
                'users' => [],
                'pagination' => [
                    'current_page' => 1,
                    'total_pages' => 0,
                    'total_count' => 0,
                    'per_page' => $limit
                ]
            ];
        }
    }
    
    public function getUserById($id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.id, u.first_name, u.last_name, u.email, u.phone, 
                       u.role_id, r.name as role, u.is_active, u.created_at, u.last_login 
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?
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
                    'message' => 'User not found'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error getting user: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving user: ' . $e->getMessage()
            ];
        }
    }
    
    public function getRoles() {
        try {
            $stmt = $this->conn->prepare("SELECT id, name, description FROM roles ORDER BY name");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $roles = [];
            while ($row = $result->fetch_assoc()) {
                $roles[] = $row;
            }
            
            return [
                'success' => true,
                'roles' => $roles
            ];
        } catch (\Exception $e) {
            error_log("Error getting roles: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving roles: ' . $e->getMessage(),
                'roles' => []
            ];
        }
    }
    
    public function createUser($data) {
        try {
            // Validate required fields
            $required = ['first_name', 'last_name', 'email', 'password', 'role'];
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
            
            // Check if email already exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $data['email']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                return [
                    'success' => false,
                    'message' => 'Email already exists'
                ];
            }
            
            // Get role ID from role name
            $roleStmt = $this->conn->prepare("SELECT id FROM roles WHERE name = ?");
            $roleStmt->bind_param("s", $data['role']);
            $roleStmt->execute();
            $roleResult = $roleStmt->get_result();
            
            if ($roleResult->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid role specified'
                ];
            }
            
            $roleId = $roleResult->fetch_assoc()['id'];
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare("
                INSERT INTO users (first_name, last_name, email, password, phone, role_id, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $phone = $data['phone'] ?? null;
            $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
            
            $stmt->bind_param("sssssii", 
                $data['first_name'], 
                $data['last_name'], 
                $data['email'], 
                $hashedPassword,
                $phone,
                $roleId,
                $isActive
            );
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'User created successfully',
                    'data' => ['id' => $this->conn->insert_id]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error creating user: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error creating user: ' . $e->getMessage()
            ];
        }
    }
    
    public function updateUser($id, $data) {
        try {
            // Check if user exists
            $userCheck = $this->getUserById($id);
            if (!$userCheck['success']) {
                return $userCheck;
            }
            
            $updateFields = [];
            $params = [];
            $types = '';
            
            $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'role', 'is_active'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    // Validate email if being updated
                    if ($field === 'email') {
                        if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            return [
                                'success' => false,
                                'message' => 'Invalid email format'
                            ];
                        }
                        
                        // Check if email already exists (excluding current user)
                        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                        $stmt->bind_param("si", $data[$field], $id);
                        $stmt->execute();
                        if ($stmt->get_result()->num_rows > 0) {
                            return [
                                'success' => false,
                                'message' => 'Email already exists'
                            ];
                        }
                        
                        $updateFields[] = "email = ?";
                        $params[] = $data[$field];
                        $types .= 's';
                    }
                    // Handle role field
                    elseif ($field === 'role') {
                        // Get role ID from role name
                        $roleStmt = $this->conn->prepare("SELECT id FROM roles WHERE name = ?");
                        $roleStmt->bind_param("s", $data[$field]);
                        $roleStmt->execute();
                        $roleResult = $roleStmt->get_result();
                        
                        if ($roleResult->num_rows === 0) {
                            return [
                                'success' => false,
                                'message' => 'Invalid role specified'
                            ];
                        }
                        
                        $roleId = $roleResult->fetch_assoc()['id'];
                        $updateFields[] = "role_id = ?";
                        $params[] = $roleId;
                        $types .= 'i';
                    }
                    // Handle is_active field
                    elseif ($field === 'is_active') {
                        $updateFields[] = "is_active = ?";
                        $params[] = (int)$data[$field];
                        $types .= 'i';
                    }
                    // Handle other string fields
                    else {
                        $updateFields[] = "$field = ?";
                        $params[] = $data[$field];
                        $types .= 's';
                    }
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
            
            $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'User updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error updating user: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ];
        }
    }
    
    public function updateProfile($id, $data) {
        try {
            // Check if user exists
            $userCheck = $this->getUserById($id);
            if (!$userCheck['success']) {
                return $userCheck;
            }
            
            $updateFields = [];
            $params = [];
            $types = '';
            
            $allowedFields = ['first_name', 'last_name', 'email', 'phone'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    // Validate email if being updated
                    if ($field === 'email') {
                        if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            return [
                                'success' => false,
                                'message' => 'Invalid email format'
                            ];
                        }
                        
                        // Check if email already exists (excluding current user)
                        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                        $stmt->bind_param("si", $data[$field], $id);
                        $stmt->execute();
                        if ($stmt->get_result()->num_rows > 0) {
                            return [
                                'success' => false,
                                'message' => 'Email already exists'
                            ];
                        }
                    }
                    
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                    $types .= 's';
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
            
            $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error updating profile: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating profile: ' . $e->getMessage()
            ];
        }
    }
    
    public function changePassword($id, $data) {
        try {
            // Validate required fields
            $required = ['current_password', 'new_password', 'confirm_password'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Check if new passwords match
            if ($data['new_password'] !== $data['confirm_password']) {
                return [
                    'success' => false,
                    'message' => 'New passwords do not match'
                ];
            }
            
            // Validate password strength
            if (strlen($data['new_password']) < 6) {
                return [
                    'success' => false,
                    'message' => 'New password must be at least 6 characters long'
                ];
            }
            
            // Get current user data
            $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            $user = $result->fetch_assoc();
            
            // Verify current password
            if (!password_verify($data['current_password'], $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }
            
            // Hash new password
            $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET password = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("si", $hashedPassword, $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Password changed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error changing password: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error changing password: ' . $e->getMessage()
            ];
        }
    }
    
    public function deleteUser($id, $currentUserId = null) {
        try {
            // Prevent self-deletion if currentUserId is provided
            if ($currentUserId && $id == $currentUserId) {
                return [
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ];
            }
            
            // Check if user exists
            $userCheck = $this->getUserById($id);
            if (!$userCheck['success']) {
                return $userCheck;
            }
            
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'User deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error deleting user: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ];
        }
    }
    
    public function getUserStats() {
        try {
            $stats = [];
            
            // Total users
            $result = $this->conn->query("SELECT COUNT(*) as total FROM users");
            $stats['total_users'] = $result->fetch_assoc()['total'];
            
            // Active users
            $result = $this->conn->query("SELECT COUNT(*) as active FROM users WHERE is_active = 1");
            $stats['active_users'] = $result->fetch_assoc()['active'];
            
            // Users by role
            $result = $this->conn->query("
                SELECT r.name as role, COUNT(*) as count 
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                GROUP BY r.name
            ");
            $stats['users_by_role'] = [];
            while ($row = $result->fetch_assoc()) {
                $stats['users_by_role'][$row['role']] = $row['count'];
            }
            
            // Recent users (last 30 days)
            $result = $this->conn->query("
                SELECT COUNT(*) as recent_count 
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stats['recent_users'] = $result->fetch_assoc()['recent_count'];
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (\Exception $e) {
            error_log("Get user stats error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving user statistics: ' . $e->getMessage()
            ];
        }
    }
    
    public function toggleUserStatus($id) {
        try {
            // Check if user exists
            $userCheck = $this->getUserById($id);
            if (!$userCheck['success']) {
                return $userCheck;
            }
            
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET is_active = NOT is_active, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'User status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error updating user status: ' . $stmt->error
                ];
            }
        } catch (\Exception $e) {
            error_log("Error toggling user status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating user status: ' . $e->getMessage()
            ];
        }
    }
    
    public function getUsers($page = 1, $limit = 20, $search = '', $role = '') {
        // Alias for getAllUsers to maintain compatibility
        return $this->getAllUsers($page, $limit, $search, $role);
    }
    // Check if user isAdmin
    public function isAdmin($id) {
        try {
            $stmt = $this->conn->prepare("SELECT role_id FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['role_id'] === 1;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            error_log("Error checking admin status: " . $e->getMessage());
            return false;
        }
    }
}
?>