<?php
require_once '../classes/Auth.php';
require_once '../classes/FeedbackManager.php';
use App\Auth;
use App\FeedbackManager;

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user = $auth->getCurrentUser();
$feedbackManager = new FeedbackManager();

// Handle actions
$message = '';
$messageType = '';

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $result = $feedbackManager->updateFeedbackStatus($_POST['feedback_id'], $_POST['status'], $_POST['response'] ?? '');
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'delete_feedback':
                $result = $feedbackManager->deleteFeedback($_POST['feedback_id']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
        }
    }
}

// Get filters
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$rating = $_GET['rating'] ?? '';
$status = $_GET['status'] ?? '';

// Get feedback
$feedback = $feedbackManager->getAllFeedback($page, 20, $search, $rating, $status);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Nananom Farms Admin</title>
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
            <div class="main-container">
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Header -->
                <?php include 'includes/header.php'; ?>

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
                                <input type="text" name="search" class="form-control" placeholder="Search feedback..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>

                            <div class="col-md-2">
                                <select name="rating" class="form-select">
                                    <option value="">All Ratings</option>
                                    <option value="5" <?php echo $rating == '5' ? 'selected' : ''; ?>>5 Stars</option>
                                    <option value="4" <?php echo $rating == '4' ? 'selected' : ''; ?>>4 Stars</option>
                                    <option value="3" <?php echo $rating == '3' ? 'selected' : ''; ?>>3 Stars</option>
                                    <option value="2" <?php echo $rating == '2' ? 'selected' : ''; ?>>2 Stars</option>
                                    <option value="1" <?php echo $rating == '1' ? 'selected' : ''; ?>>1 Star</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>
                                        Pending</option>
                                    <option value="approved" <?php echo $status == 'approved' ? 'selected' : ''; ?>>
                                        Approved</option>
                                    <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>
                                        Rejected</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>

                            <div class="col-md-2">
                                <a href="feedback.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Feedback Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Rating</th>
                                        <th>Comment</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($feedback['success'] && !empty($feedback['feedback'])): ?>
                                    <?php foreach ($feedback['feedback'] as $item): ?>
                                    <tr>
                                        <td>#<?php echo $item['id']; ?></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($item['customer_name']); ?></strong><br>
                                                <small
                                                    class="text-muted"><?php echo htmlspecialchars($item['email']); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['service_name'] ?? 'General'); ?></td>
                                        <td>
                                            <div class="rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i
                                                    class="fas fa-star <?php echo $i <= $item['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                                <span class="ms-1">(<?php echo $item['rating']; ?>/5)</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="max-width: 200px;">
                                                <?php echo htmlspecialchars(substr($item['comment'], 0, 100)) . (strlen($item['comment']) > 100 ? '...' : ''); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $item['status'] === 'approved' ? 'success' : 
                                                    ($item['status'] === 'rejected' ? 'danger' : 'warning'); 
                                            ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($item['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="openFeedbackModal(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-info"
                                                    onclick="viewFeedbackDetails(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="deleteFeedback(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No feedback found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($feedback['success'] && $feedback['pagination']['total_pages'] > 1): ?>
                        <nav aria-label="Feedback pagination">
                            <ul class="pagination justify-content-center">
                                <?php
                                $current_page = $feedback['pagination']['current_page'];
                                $total_pages = $feedback['pagination']['total_pages'];
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

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" id="feedbackForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="feedback_id" id="feedbackId">

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="response" class="form-label">Response</label>
                            <textarea name="response" id="response" class="form-control" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Feedback</button>
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
                    <h5 class="modal-title">Feedback Details</h5>
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
                                </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function openFeedbackModal(feedback) {
        document.getElementById('feedbackId').value = feedback.id;
        document.getElementById('status').value = feedback.status;
        document.getElementById('response').value = feedback.response || '';

        const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
        modal.show();
    }

    function viewFeedbackDetails(id) {
        const modal = new bootstrap.Modal(document.getElementById('viewModal'));
        document.getElementById('viewModalBody').innerHTML = '<p>Loading feedback details for ID: ' + id + '</p>';
        modal.show();
    }

    function deleteFeedback(id) {
        if (confirm('Are you sure you want to delete this feedback?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_feedback">
                <input type="hidden" name="feedback_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>

</html>