<?php
require_once '../classes/Auth.php';
require_once '../classes/EnquiryManager.php';
use App\Auth;
use App\EnquiryManager;

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user = $auth->getCurrentUser();
$enquiryManager = new EnquiryManager();

// Handle actions
$message = '';
$messageType = '';

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $result = $enquiryManager->updateEnquiryStatus($_POST['enquiry_id'], $_POST['status'], $_POST['response'] ?? '');
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'assign_enquiry':
                $result = $enquiryManager->assignEnquiry($_POST['enquiry_id'], $_POST['user_id']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
        }
    }
}

// Get filters
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Get enquiries
$enquiries = $enquiryManager->getEnquiries($page, 20, $search, $status);
$usersResult = $enquiryManager->getAssignableUsers();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiries - Nananom Farms Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
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

                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search enquiries..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>

                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="new" <?php echo $status == 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="in_progress"
                                        <?php echo $status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="resolved" <?php echo $status == 'resolved' ? 'selected' : ''; ?>>
                                        Resolved</option>
                                    <option value="closed" <?php echo $status == 'closed' ? 'selected' : ''; ?>>Closed
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>

                            <div class="col-md-2">
                                <a href="enquiries.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Enquiries Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($enquiries['success'] && !empty($enquiries['enquiries'])): ?>
                                    <?php foreach ($enquiries['enquiries'] as $enquiry): ?>
                                    <tr>
                                        <td>#<?php echo $enquiry['id']; ?></td>
                                        <td><?php echo htmlspecialchars($enquiry['name']); ?></td>
                                        <td><?php echo htmlspecialchars($enquiry['email']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($enquiry['subject'], 0, 50)) . '...'; ?>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-<?php echo $enquiry['status'] === 'new' ? 'primary' : ($enquiry['status'] === 'resolved' ? 'success' : 'warning'); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $enquiry['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $enquiry['assigned_user'] ?? 'Unassigned'; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($enquiry['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="openEnquiryModal(<?php echo htmlspecialchars(json_encode($enquiry)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-info"
                                                    onclick="viewEnquiryDetails(<?php echo $enquiry['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success"
                                                    onclick="openAssignModal(<?php echo $enquiry['id']; ?>)">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No enquiries found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($enquiries['success'] && $enquiries['pagination']['total_pages'] > 1): ?>
                        <nav aria-label="Enquiries pagination">
                            <ul class="pagination justify-content-center">
                                <?php
                                $current_page = $enquiries['pagination']['current_page'];
                                $total_pages = $enquiries['pagination']['total_pages'];
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

    <!-- Enquiry Modal -->
    <div class="modal fade" id="enquiryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Enquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" id="enquiryForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="enquiry_id" id="enquiryId">

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="new">New</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="response" class="form-label">Response</label>
                            <textarea name="response" id="response" class="form-control" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Enquiry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Enquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" id="assignForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="assign_enquiry">
                        <input type="hidden" name="enquiry_id" id="assignEnquiryId">

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Assign To</label>
                            <select name="user_id" id="user_id" class="form-select" required>
                                <option value="">Select User</option>
                                <?php if ($usersResult['success']): ?>
                                <?php foreach ($usersResult['data'] as $u): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?>
                                    (<?php echo $u['role']; ?>)</option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enquiry Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script>
    function openEnquiryModal(enquiry) {
        document.getElementById('enquiryId').value = enquiry.id;
        document.getElementById('status').value = enquiry.status;
        document.getElementById('response').value = enquiry.response || '';

        const modal = new bootstrap.Modal(document.getElementById('enquiryModal'));
        modal.show();
    }

    function openAssignModal(enquiryId) {
        document.getElementById('assignEnquiryId').value = enquiryId;

        const modal = new bootstrap.Modal(document.getElementById('assignModal'));
        modal.show();
    }

    function viewEnquiryDetails(id) {
        // You can implement AJAX call here to fetch and display full enquiry details
        const modal = new bootstrap.Modal(document.getElementById('viewModal'));
        document.getElementById('viewModalBody').innerHTML = '<p>Loading enquiry details for ID: ' + id + '</p>';
        modal.show();
    }
    </script>
</body>

</html>