<?php
require_once '../classes/Auth.php';
require_once '../classes/ServiceManager.php';
require_once '../classes/SecurityHelper.php';

use App\Auth;
use App\ServiceManager;
use App\SecurityHelper;

// Initialize authentication
$auth = new Auth();
$auth->requireAuth();

// Check permissions
if (!$auth->hasPermission('view_services')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$user = $auth->getCurrentUser();
$serviceManager = new ServiceManager($auth);

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!$auth->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                if ($auth->hasPermission('create_services') || $auth->hasRole('admin')) {
                    $result = $serviceManager->createService($_POST);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'error';
                }
                break;
                
            case 'update':
                if ($auth->hasPermission('edit_services') || $auth->hasRole('admin')) {
                    $result = $serviceManager->updateService($_POST['service_id'], $_POST);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'error';
                }
                break;
                
            case 'delete':
                if ($auth->hasPermission('delete_services') || $auth->hasRole('admin')) {
                    $result = $serviceManager->deleteService($_POST['service_id']);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'error';
                }
                break;
        }
    }
}

// Get filters from GET parameters
$page = max(1, intval($_GET['page'] ?? 1));
$search = SecurityHelper::sanitizeInput($_GET['search'] ?? '');
$category = SecurityHelper::sanitizeInput($_GET['category'] ?? '');

// Get services and categories
$services = $serviceManager->getAllServices($page, 20, $search, $category);
$categories = $serviceManager->getCategories();
$stats = $serviceManager->getServiceStats();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services Management - Nananom Farms Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">

    <style>
    .service-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .service-card:hover {
        transform: translateY(-5px);
    }

    .price-badge {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: bold;
    }
    </style>
</head>

<body>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="main-container">
            <div class="col-md-9 col-lg-10 p-4">
                <?php include 'includes/header.php'; ?>
                <!-- Header -->
                <div class="d-flex justify-content-end align-items-center mb-4">

                    <?php if ($auth->hasRole('admin')): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal">
                        <i class="fas fa-plus me-2"></i>Add New Service
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Alert Messages -->
                <?php if ($message): ?>
                <div
                    class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                    <i
                        class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <?php if ($stats['success']): ?>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?php echo $stats['data']['total_services']; ?></div>
                                    <div>Total Services</div>
                                </div>
                                <i class="fas fa-cogs fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number">
                                        <?php echo count($stats['data']['services_by_category']); ?></div>
                                    <div>Categories</div>
                                </div>
                                <i class="fas fa-tags fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?php echo $stats['data']['recent_services']; ?></div>
                                    <div>Added This Month</div>
                                </div>
                                <i class="fas fa-plus-circle fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4 primary">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search Services</label>
                                <input type="text" name="search" id="search" class="form-control"
                                    placeholder="Search by name or description..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>

                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select name="category" id="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php if ($categories['success']): ?>
                                    <?php foreach ($categories['data'] as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>"
                                        <?php echo $category === $cat['name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $cat['name']))); ?>
                                        (<?php echo $cat['count']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="services.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Services Grid -->
                <?php if ($services['success'] && !empty($services['data'])): ?>
                <div class="row">
                    <?php foreach ($services['data'] as $service): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card service-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                                    <span
                                        class="badge bg-<?php echo $service['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>

                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars(substr($service['description'], 0, 100)) . '...'; ?>
                                </p>

                                <div class="mb-3">
                                    <small class="text-muted">Category:</small>
                                    <span class="badge bg-light text-dark">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $service['category']))); ?>
                                    </span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="price-badge">
                                        $<?php echo $service['formatted_price']; ?> /
                                        <?php echo htmlspecialchars($service['unit']); ?>
                                    </div>

                                    <?php if ($auth->hasRole('admin')): ?>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-2">
                                    <small class="text-muted">
                                        Created: <?php echo date('M j, Y', strtotime($service['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($services['pagination']['total_pages'] > 1): ?>
                <nav aria-label="Services pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php
                        $pagination = $services['pagination'];
                        $queryParams = http_build_query(array_filter(['search' => $search, 'category' => $category]));
                        ?>

                        <?php if ($pagination['has_prev']): ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="?page=<?php echo $pagination['current_page'] - 1; ?>&<?php echo $queryParams; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php endif; ?>

                        <li class="page-item active">
                            <span class="page-link">
                                Page <?php echo $pagination['current_page']; ?> of
                                <?php echo $pagination['total_pages']; ?>
                            </span>
                        </li>

                        <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="?page=<?php echo $pagination['current_page'] + 1; ?>&<?php echo $queryParams; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>

                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                    <h4>No Services Found</h4>
                    <p class="text-muted">No services match your current filters.</p>
                    <?php if ($auth->hasRole('admin')): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal">
                        <i class="fas fa-plus me-2"></i>Add First Service
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Service Modal -->
    <?php if ($auth->hasRole('admin')): ?>
    <div class="modal fade" id="serviceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalTitle">Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" id="serviceForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCSRFToken(); ?>">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="service_id" id="serviceId">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Service Name *</label>
                                    <input type="text" name="name" id="name" class="form-control" required
                                        maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select name="category" id="modalCategory" class="form-select">
                                        <option value="general">General</option>
                                        <option value="crude_oil">Crude Oil</option>
                                        <option value="refined_oil">Refined Oil</option>
                                        <option value="kernel_oil">Kernel Oil</option>
                                        <option value="consultation">Consultation</option>
                                        <option value="bulk_supply">Bulk Supply</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea name="description" id="description" class="form-control" rows="4" required
                                maxlength="500"></textarea>
                            <div class="form-text">Maximum 500 characters</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="price" id="price" class="form-control" min="0"
                                            step="0.01" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit" class="form-label">Unit</label>
                                    <select name="unit" id="unit" class="form-select">
                                        <option value="piece">Piece</option>
                                        <option value="ton">Ton</option>
                                        <option value="kg">Kilogram</option>
                                        <option value="liter">Liter</option>
                                        <option value="hour">Hour</option>
                                        <option value="day">Day</option>
                                        <option value="month">Month</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                                    value="1" checked>
                                <label for="is_active" class="form-check-label">Active Service</label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-2"></i>Save Service
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the service "<span id="deleteServiceName"></span>"?</p>
                    <p class="text-muted">This action cannot be undone. If the service has existing bookings, it will be
                        deactivated instead of deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="service_id" id="deleteServiceId">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Service
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Edit service function
    function editService(service) {
        document.getElementById('serviceModalTitle').textContent = 'Edit Service';
        document.getElementById('formAction').value = 'update';
        document.getElementById('serviceId').value = service.id;
        document.getElementById('name').value = service.name;
        document.getElementById('description').value = service.description;
        document.getElementById('modalCategory').value = service.category;
        document.getElementById('price').value = service.price;
        document.getElementById('unit').value = service.unit;
        document.getElementById('is_active').checked = service.is_active == 1;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Update Service';

        new bootstrap.Modal(document.getElementById('serviceModal')).show();
    }

    // Delete service function
    function deleteService(id, name) {
        document.getElementById('deleteServiceId').value = id;
        document.getElementById('deleteServiceName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Reset modal when closed
    document.getElementById('serviceModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('serviceModalTitle').textContent = 'Add New Service';
        document.getElementById('formAction').value = 'create';
        document.getElementById('serviceId').value = '';
        document.getElementById('serviceForm').reset();
        document.getElementById('is_active').checked = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Save Service';
    });

    // Form validation
    document.getElementById('serviceForm').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const description = document.getElementById('description').value.trim();

        if (name.length < 3) {
            e.preventDefault();
            alert('Service name must be at least 3 characters long.');
            return;
        }

        if (description.length < 10) {
            e.preventDefault();
            alert('Description must be at least 10 characters long.');
            return;
        }
    });

    // Character counter for description
    document.getElementById('description').addEventListener('input', function() {
        const maxLength = 500;
        const currentLength = this.value.length;
        const remaining = maxLength - currentLength;

        const formText = this.nextElementSibling;
        formText.textContent = `${remaining} characters remaining`;

        if (remaining < 50) {
            formText.classList.add('text-warning');
        } else {
            formText.classList.remove('text-warning');
        }

        if (remaining < 0) {
            formText.classList.add('text-danger');
            formText.classList.remove('text-warning');
        } else {
            formText.classList.remove('text-danger');
        }
    });
    </script>
</body>

</html>