<?php
require_once '../classes/Auth.php';
require_once '../classes/BookingManager.php';
require_once '../classes/UserManager.php';
use App\Auth;
use App\BookingManager;
use App\UserManager;

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user = $auth->getCurrentUser();
$bookingManager = new BookingManager();
$userManager = new UserManager();

// Handle actions
$message = '';
$messageType = '';

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $result = $bookingManager->updateBookingStatus($_POST['booking_id'], $_POST['status'], $_POST['notes'] ?? '');
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'assign_booking':
                $result = $bookingManager->assignBooking($_POST['booking_id'], $_POST['user_id']);
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
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Get bookings
$bookings = $bookingManager->getBookings($page, 20, $search, $status, $date_from, $date_to);
$usersResult = $userManager->getUsers(1, 100);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Nananom Farms Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/booking.css">
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
                <div class="card mb-4 primary">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search bookings..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>

                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>
                                        Pending</option>
                                    <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>
                                        Confirmed</option>
                                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>
                                        Completed</option>
                                    <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>
                                        Cancelled</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" placeholder="From Date"
                                    value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>

                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" placeholder="To Date"
                                    value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>

                            <div class="col-md-1">
                                <a href="bookings.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Date & Time</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($bookings['success'] && !empty($bookings['bookings'])): ?>
                                    <?php foreach ($bookings['bookings'] as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['id']; ?></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong><br>
                                                <small
                                                    class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                                <?php if (!empty($booking['phone'])): ?>
                                                <br><small
                                                    class="text-muted"><?php echo htmlspecialchars($booking['phone']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($booking['service'] ?? 'N/A'); ?></strong>
                                                <?php if (!empty($booking['service_description'])): ?>
                                                <br><small
                                                    class="text-muted"><?php echo htmlspecialchars(substr($booking['service_description'], 0, 50)) . '...'; ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="fas fa-calendar text-primary"></i>
                                                <?php echo date('d/m/Y', strtotime($booking['appointment_date'] ?? $booking['booking_date'])); ?><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i>
                                                    <?php echo date('H:i', strtotime($booking['booking_time'] ?? '09:00:00')); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong
                                                class="text-success">$<?php echo number_format($booking['total_amount'] ?? 0, 2); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $booking['status'] === 'pending' ? 'warning' : 
                                                    ($booking['status'] === 'confirmed' ? 'primary' : 
                                                    ($booking['status'] === 'completed' ? 'success' : 'danger')); 
                                            ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($booking['assigned_user'])): ?>
                                            <span class="text-success">
                                                <i class="fas fa-user-check"></i>
                                                <?php echo htmlspecialchars($booking['assigned_user']); ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="text-danger">
                                                <i class="fas fa-user-times"></i>
                                                Unassigned
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="openBookingModal(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-info"
                                                    onclick="viewBookingDetails(<?php echo $booking['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success"
                                                    onclick="openAssignModal(<?php echo $booking['id']; ?>)">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No bookings found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($bookings['success'] && $bookings['pagination']['total_pages'] > 1): ?>
                        <nav aria-label="Bookings pagination">
                            <ul class="pagination justify-content-center">
                                <?php
                                $current_page = $bookings['pagination']['current_page'];
                                $total_pages = $bookings['pagination']['total_pages'];
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

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" id="bookingForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="booking_id" id="bookingId">

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Booking</button>
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
                    <h5 class="modal-title">Assign Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" id="assignForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="assign_booking">
                        <input type="hidden" name="booking_id" id="assignBookingId">

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Assign To</label>
                            <select name="user_id" id="user_id" class="form-select" required>
                                <option value="">Select User</option>
                                <?php if ($usersResult['success']): ?>
                                <?php foreach ($usersResult['users'] as $u): ?>
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
                    <h5 class="modal-title">Booking Details</h5>
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
    <script>
    function openBookingModal(booking) {
        document.getElementById('bookingId').value = booking.id;
        document.getElementById('status').value = booking.status;
        document.getElementById('notes').value = booking.notes || '';

        const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
        modal.show();
    }

    function openAssignModal(bookingId) {
        document.getElementById('assignBookingId').value = bookingId;

        const modal = new bootstrap.Modal(document.getElementById('assignModal'));
        modal.show();
    }

    function viewBookingDetails(id) {
        // You can implement AJAX call here to fetch and display full booking details
        const modal = new bootstrap.Modal(document.getElementById('viewModal'));
        document.getElementById('viewModalBody').innerHTML = '<p>Loading booking details for ID: ' + id + '</p>';
        modal.show();
    }
    </script>
</body>

</html>