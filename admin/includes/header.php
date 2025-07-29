<?php
require_once __DIR__ . '/../../classes/Auth.php';
use App\Auth;

$auth = new Auth();
$auth->requireAuth();
$user = $auth->getCurrentUser();
$auth->isNotAuthorized();


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Dashboard'; ?> - Nananom Farms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <link href="assets/css/sidebar.css" rel="stylesheet">

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="assets/logo.png" alt="Logo" class="logo">
                Nananom Farms Admin
            </a>

            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($user['first_name'] ?? 'User'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit me-2"></i>Profile</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="logout.php"><i
                                    class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <!-- <h2><?php echo ucfirst(str_replace('.php', '', basename($_SERVER['PHP_SELF']))); ?></h2> -->
                    <p class="text-muted">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Last login:
                        <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'First time'; ?>
                    </small>
                </div>
            </div>
            <!-- Content will be added here -->
        </div>
    </div>
</body>

</html>