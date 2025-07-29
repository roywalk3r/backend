<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Nananom Farms</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Navigation -->
    <?php
include_once __DIR__ . '/partials/header.php';
?>

    <!-- Profile Container -->
    <div class="profile-container container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-circle">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            <div class="profile-info">
                <h1 id="profileName">Loading...</h1>
                <p id="profileEmail">Loading...</p>
                <span class="profile-status">Active Member</span>
            </div>
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-number" id="totalBookings">0</span>
                    <span class="stat-label">Bookings</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="totalEnquiries">0</span>
                    <span class="stat-label">Enquiries</span>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Sidebar Navigation -->
            <div class="profile-sidebar">
                <nav class="profile-nav">
                    <a href="#personal" class="profile-nav-link active" data-tab="personal">
                        <i class="fas fa-user"></i>
                        <span>Personal Information</span>
                    </a>
                    <a href="#bookings" class="profile-nav-link" data-tab="bookings">
                        <i class="fas fa-calendar-alt"></i>
                        <span>My Bookings</span>
                    </a>
                    <a href="#enquiries" class="profile-nav-link" data-tab="enquiries">
                        <i class="fas fa-envelope"></i>
                        <span>My Enquiries</span>
                    </a>
                    <a href="#security" class="profile-nav-link" data-tab="security">
                        <i class="fas fa-shield-alt"></i>
                        <span>Security</span>
                    </a>
                </nav>
            </div>

            <!-- Main Content Area -->
            <div class="profile-main">
                <!-- Personal Information Tab -->
                <div id="personal" class="tab-content active">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-user"></i> Personal Information</h2>
                            <p>Update your personal details and contact information</p>
                        </div>
                        <div class="card-body">
                            <form id="profileForm" class="profile-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="firstName">
                                            <i class="fas fa-user"></i>
                                            First Name
                                        </label>
                                        <input type="text" id="firstName" name="firstName" required>
                                        <div class="error-message" id="firstNameError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastName">
                                            <i class="fas fa-user"></i>
                                            Last Name
                                        </label>
                                        <input type="text" id="lastName" name="lastName" required>
                                        <div class="error-message" id="lastNameError"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope"></i>
                                        Email Address
                                    </label>
                                    <input type="email" id="email" name="email" required>
                                    <div class="error-message" id="emailError"></div>
                                </div>

                                <div class="form-group">
                                    <label for="phone">
                                        <i class="fas fa-phone"></i>
                                        Phone Number
                                    </label>
                                    <input type="tel" id="phone" name="phone" required>
                                    <div class="error-message" id="phoneError"></div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="btn-text">
                                            <i class="fas fa-save"></i>
                                            Update Profile
                                        </span>
                                        <div class="btn-loading" style="display: none;">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            Updating...
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Bookings Tab -->
                <div id="bookings" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-calendar-alt"></i> My Bookings</h2>
                            <p>View and manage your service bookings</p>
                        </div>
                        <div class="card-body">
                            <div class="bookings-filter">
                                <select id="bookingStatusFilter" class="filter-select">
                                    <option value="">All Bookings</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div id="bookingsContainer" class="bookings-list">
                                <div class="loading">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading your bookings...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enquiries Tab -->
                <div id="enquiries" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-envelope"></i> My Enquiries</h2>
                            <p>View your submitted enquiries and responses</p>
                        </div>
                        <div class="card-body">
                            <div class="enquiries-filter">
                                <select id="enquiryStatusFilter" class="filter-select">
                                    <option value="">All Enquiries</option>
                                    <option value="pending">Pending</option>
                                    <option value="responded">Responded</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div id="enquiriesContainer" class="enquiries-list">
                                <div class="loading">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading your enquiries...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div id="security" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-shield-alt"></i> Security Settings</h2>
                            <p>Update your password and security preferences</p>
                        </div>
                        <div class="card-body">
                            <form id="passwordForm" class="profile-form">
                                <div class="form-group">
                                    <label for="currentPassword">
                                        <i class="fas fa-lock"></i>
                                        Current Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" id="currentPassword" name="currentPassword" required>
                                        <button type="button" class="password-toggle"
                                            onclick="togglePassword('currentPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="error-message" id="currentPasswordError"></div>
                                </div>

                                <div class="form-group">
                                    <label for="newPassword">
                                        <i class="fas fa-key"></i>
                                        New Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" id="newPassword" name="newPassword" required
                                            minlength="8">
                                        <button type="button" class="password-toggle"
                                            onclick="togglePassword('newPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength">
                                        <div class="strength-bar">
                                            <div class="strength-fill"></div>
                                        </div>
                                        <span class="strength-text">Password strength</span>
                                    </div>
                                    <div class="error-message" id="newPasswordError"></div>
                                </div>

                                <div class="form-group">
                                    <label for="confirmNewPassword">
                                        <i class="fas fa-check"></i>
                                        Confirm New Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" id="confirmNewPassword" name="confirmNewPassword"
                                            required>
                                        <button type="button" class="password-toggle"
                                            onclick="togglePassword('confirmNewPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="error-message" id="confirmNewPasswordError"></div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="btn-text">
                                            <i class="fas fa-key"></i>
                                            Update Password
                                        </span>
                                        <div class="btn-loading" style="display: none;">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            Updating...
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Scripts -->
    <script src="assets/js/profile.js"></script>
</body>

</html>