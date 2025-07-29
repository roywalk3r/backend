<?php
require_once '../classes/Auth.php';
require_once '../classes/UserManager.php';
use App\Auth;
use App\UserManager;

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user = $auth->getCurrentUser();
$userManager = new UserManager();

// Check if user has admin privileges
$isAdmin = $auth->hasRole('admin');

// Handle actions
$message = '';
$messageType = '';

if ($_POST && $isAdmin) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_user':
                $result = $userManager->createUser($_POST);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'update_user':
                $result = $userManager->updateUser($_POST['user_id'], $_POST);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'delete_user':
                $result = $userManager->deleteUser($_POST['user_id'], $user['id']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
        }
    }
}

// Get filters
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';

// Get users and roles
$users = $userManager->getUsers($page, 20, $search, $role);
$rolesData = $userManager->getRoles();
$roles = $rolesData['success'] ? $rolesData['roles'] : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Nananom Farms Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Header -->
                <?php include 'includes/header.php'; ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-users me-2"></i>Users Management</h2>
                        <p class="text-muted">Manage system users and their permissions</p>
                    </div>
                    <?php if ($isAdmin): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="fas fa-plus me-2"></i>Add New User
                    </button>
                    <?php endif; ?>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4 primary">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search users..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>

                            <div class="col-md-3">
                                <select name="role" class="form-select">
                                    <option value="">All Roles</option>
                                    <?php foreach ($roles as $roleItem): ?>
                                    <option value="<?php echo htmlspecialchars($roleItem['name']); ?>"
                                        <?php echo $role == $roleItem['name'] ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($roleItem['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>

                            <div class="col-md-2">
                                <a href="users.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Created</th>
                                        <?php if ($isAdmin): ?>
                                        <th>Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($users['success'] && !empty($users['users'])): ?>
                                    <?php foreach ($users['users'] as $userItem): ?>
                                    <tr>
                                        <td>#<?php echo $userItem['id']; ?></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($userItem['first_name'] . ' ' . $userItem['last_name']); ?></strong>
                                                <?php if ($userItem['id'] == $user['id']): ?>
                                                <span class="badge bg-info ms-1">You</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($userItem['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $userItem['role'] === 'admin' ? 'danger' : 
                                                    ($userItem['role'] === 'manager' ? 'warning' : 'primary'); 
                                            ?>">
                                                <?php echo ucfirst($userItem['role'] ?? 'Unknown'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-<?php echo $userItem['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $userItem['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($userItem['last_login']): ?>
                                            <?php echo date('d/m/Y H:i', strtotime($userItem['last_login'])); ?>
                                            <?php else: ?>
                                            <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($userItem['created_at'])); ?></td>
                                        <?php if ($isAdmin): ?>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="openUserModal(<?php echo htmlspecialchars(json_encode($userItem)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($userItem['id'] != $user['id']): ?>
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="deleteUser(<?php echo $userItem['id']; ?>, '<?php echo htmlspecialchars($userItem['first_name'] . ' ' . $userItem['last_name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="<?php echo $isAdmin ? '8' : '7'; ?>" class="text-center">No users
                                            found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($users['success'] && $users['pagination']['total_pages'] > 1): ?>
                        <nav aria-label="Users pagination">
                            <ul class="pagination justify-content-center">
                                <?php
                                $current_page = $users['pagination']['current_page'];
                                $total_pages = $users['pagination']['total_pages'];
                                $query_params = http_build_query(array_filter($_GET));
                                ?>

                                <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?page=<?php echo $current_page - 1; ?>&<?php echo $query_params; ?>">Previous</a>
                                </li>
                                <?php endif; ?>

                                <li class="page-item active">
                                    <span class="page-link">
                                        Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                                    </span>
                                </li>

                                <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?page=<?php echo $current_page + 1; ?>&<?php echo $query_params; ?>">Next</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <?php if ($isAdmin): ?>
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" id="userForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create_user">
                        <input type="hidden" name="user_id" id="userId">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" name="first_name" id="first_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select name="role" id="role" class="form-select" required>
                                        <?php foreach ($roles as $roleItem): ?>
                                        <option value="<?php echo htmlspecialchars($roleItem['name']); ?>">
                                            <?php echo ucfirst($roleItem['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status</label>
                                    <select name="is_active" id="is_active" class="form-select">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="passwordField">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <div class="form-text">Leave blank to keep current password (for updates)</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    <?php if ($isAdmin): ?>

    function openUserModal(user = null) {
        if (user) {
            // Edit mode
            document.getElementById('userModalTitle').textContent = 'Edit User';
            document.getElementById('formAction').value = 'update_user';
            document.getElementById('userId').value = user.id;
            document.getElementById('first_name').value = user.first_name;
            document.getElementById('last_name').value = user.last_name;
            document.getElementById('email').value = user.email;
            document.getElementById('phone').value = user.phone || '';
            document.getElementById('role').value = user.role;
            document.getElementById('is_active').value = user.is_active;
            document.getElementById('password').required = false;
            document.getElementById('submitBtn').textContent = 'Update User';
        } else {
            // Create mode
            document.getElementById('userModalTitle').textContent = 'Add New User';
            document.getElementById('formAction').value = 'create_user';
            document.getElementById('userId').value = '';
            document.getElementById('userForm').reset();
            document.getElementById('password').required = true;
            document.getElementById('submitBtn').textContent = 'Save User';
        }

        const modal = new bootstrap.Modal(document.getElementById('userModal'));
        modal.show();
    }

    function deleteUser(id, name) {
        if (confirm('Are you sure you want to delete user: ' + name + '?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    <?php endif; ?>
    </script>
</body>

</html>