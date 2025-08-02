<div class="col-md-3 col-lg-2 sidebar p-3 ">
    <div class="text-center mb-4">
        <img src="assets/logo.png" alt="Logo" class="logo">
        <h5 class="text-black">Nananom Farms</h5>
        <small class="text-black">Admin Panel</small>
    </div>

    <nav class="nav flex-column">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"
            href="dashboard.php">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'enquiries.php' ? 'active' : ''; ?>"
            href="enquiries.php">
            <i class="fas fa-envelope me-2"></i>
            Enquiries
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>"
            href="bookings.php">
            <i class="fas fa-calendar me-2"></i>
            Bookings
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>"
            href="services.php">
            <i class="fas fa-cogs me-2"></i>
            Services
        </a>

        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>"
            href="users.php">
            <i class="fas fa-users me-2"></i>
            Users
        </a>
        <hr class="text-black">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>"
            href="profile.php">
            <i class="fas fa-user me-2"></i>
            Profile
        </a>
        <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt me-2"></i>
            Logout
        </a>
    </nav>
</div>