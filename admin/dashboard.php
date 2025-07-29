<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/ServiceManager.php';
require_once __DIR__ . '/../classes/BookingManager.php';
require_once __DIR__ . '/../classes/EnquiryManager.php';

use App\Auth;
use App\ServiceManager;
use App\BookingManager;
use App\EnquiryManager;

// Initialize authentication
$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$conn = getMysqliConnection();

// Initialize managers
$serviceManager = new ServiceManager($auth);
$bookingManager = new BookingManager();
$enquiryManager = new EnquiryManager();
// Get dashboard statistics
$stats = [];

try {
    // Get service stats
    $serviceStats = $serviceManager->getServiceStats();
    if ($serviceStats['success']) {
        $stats['services'] = $serviceStats['data'];
    }
    
    // Get booking stats
    $bookingStats = $bookingManager->getBookingStats();
    if ($bookingStats['success']) {
        $stats['bookings'] = $bookingStats['data'];
    }
    
    // Get enquiry stats
    $enquiryStats = $enquiryManager->getEnquiryStats();
    if ($enquiryStats['success']) {
        $stats['enquiries'] = $enquiryStats['data'];
    }
    
    // Get recent activity
    $recentEnquiries = $enquiryManager->getEnquiries(1, 5);
    $recentBookings = $bookingManager->getBookings(1, 5);
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = [
        'services' => ['total_services' => 0],
        'bookings' => ['total_bookings' => 0],
        'enquiries' => ['total_enquiries' => 0]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <style>
    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8);
        padding: 0.75rem 1rem;
        margin: 0.25rem 0;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        color: white;
        background: rgba(255, 255, 255, 0.1);
    }



    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: antiquewhite;
    }

    .table th {
        border: none;
        background: #f8f9fa;
        font-weight: 600;
    }

    .badge {
        font-size: 0.75rem;
    }

    .activity-item {
        padding: 1rem;
        border-left: 3px solid #667eea;
        margin-bottom: 1rem;
        background: #f8f9fa;
        border-radius: 0 10px 10px 0;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Header -->
                <?php include 'includes/header.php'; ?>
                <?php include 'includes/welcome.php'; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?php echo $stats['services']['total_services'] ?? 0; ?>
                                    </div>
                                    <div>Active Services</div>
                                </div>
                                <i class="fas fa-cogs fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?php echo $stats['bookings']['total_bookings'] ?? 0; ?>
                                    </div>
                                    <div>Total Bookings</div>
                                </div>
                                <i class="fas fa-calendar fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?php echo $stats['enquiries']['total_enquiries'] ?? 0; ?>
                                    </div>
                                    <div>Customer Enquiries</div>
                                </div>
                                <i class="fas fa-envelope fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number">
                                        $<?php echo number_format($stats['bookings']['total_revenue'] ?? 0, 0); ?></div>
                                    <div>Total Revenue</div>
                                </div>
                                <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Services by Category
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="servicesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Booking Status Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="bookingsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-envelope me-2"></i>
                                    Recent Enquiries
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($recentEnquiries['success']) && $recentEnquiries['success'] && !empty($recentEnquiries['enquiries'])): ?>
                                <?php foreach ($recentEnquiries['enquiries'] as $enquiry): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php echo htmlspecialchars($enquiry['first_name'] . ' ' . $enquiry['last_name']); ?>
                                            </h6>
                                            <p class="mb-1 text-muted">
                                                <?php echo htmlspecialchars(substr($enquiry['subject'], 0, 50)) . '...'; ?>
                                            </p>
                                            <small
                                                class="text-muted"><?php echo date('M j, Y H:i', strtotime($enquiry['created_at'])); ?></small>
                                        </div>
                                        <span
                                            class="badge bg-<?php echo $enquiry['status'] === 'new' ? 'primary' : ($enquiry['status'] === 'resolved' ? 'success' : 'warning'); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $enquiry['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div class="text-center">
                                    <a href="enquiries.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View All Enquiries
                                    </a>
                                </div>
                                <?php else: ?>
                                <p class="text-muted text-center">No recent enquiries.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar me-2"></i>
                                    Recent Bookings
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($recentBookings['success']) && $recentBookings['success'] && !empty($recentBookings['bookings'])): ?>
                                <?php foreach ($recentBookings['bookings'] as $booking): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($booking['customer_name']); ?>
                                            </h6>
                                            <p class="mb-1 text-muted">
                                                <?php echo htmlspecialchars($booking['service'] ?? 'Unknown Service'); ?>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($booking['appointment_date'] ?? $booking['booking_date'])); ?>
                                                - $<?php echo number_format($booking['total_amount'] ?? 0, 2); ?>
                                            </small>
                                        </div>
                                        <span
                                            class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div class="text-center">
                                    <a href="bookings.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View All Bookings
                                    </a>
                                </div>
                                <?php else: ?>
                                <p class="text-muted text-center">No recent bookings.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Services by Category Chart
    const servicesData = <?php echo json_encode($stats['services']['services_by_category'] ?? []); ?>;
    const servicesLabels = Object.keys(servicesData).map(key => key.replace('_', ' ').toUpperCase());
    const servicesValues = Object.values(servicesData);

    if (servicesLabels.length > 0) {
        new Chart(document.getElementById('servicesChart'), {
            type: 'doughnut',
            data: {
                labels: servicesLabels,
                datasets: [{
                    data: servicesValues,
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#17a2b8'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Bookings Status Chart
    const bookingsData = <?php echo json_encode($stats['bookings']['bookings_by_status'] ?? []); ?>;
    const bookingsLabels = Object.keys(bookingsData).map(key => key.replace('_', ' ').toUpperCase());
    const bookingsValues = Object.values(bookingsData);

    if (bookingsLabels.length > 0) {
        new Chart(document.getElementById('bookingsChart'), {
            type: 'bar',
            data: {
                labels: bookingsLabels,
                datasets: [{
                    label: 'Bookings',
                    data: bookingsValues,
                    backgroundColor: '#667eea',
                    borderColor: '#764ba2',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    </script>
</body>

</html>