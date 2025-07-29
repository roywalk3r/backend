<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
 require_once __DIR__ . '/../init.php';

$auth = new \App\Auth();
$error_message = '';
$success_message = '';

// Check if user is already logged in
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            $success_message = $result['message'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background: #fff;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 100vh;
    }

    .container {
        width: 100%;
    }

    .login-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        width: 100%;
        max-width: 400px;
    }

    .login-header {
        background: #000;
        color: white;
        padding: 2rem;
        text-align: center;
    }

    .login-body {
        padding: 2rem;
        background-color: #EFD6A7;
    }

    .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        transition: all 0.3s ease;
        background: #f1dcb5;
    }

    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    .btn-login {
        background: #31610D;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
    }

    .input-group {
        background: #f1dcb5 !important;
    }

    .alert {
        border-radius: 10px;
        border: none;
    }

    .input-group-text {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-right: none;
    }

    .form-control {
        border-left: none;
    }

    /* .default-credentials {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    } */
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <div class="login-header">
                        <img src="assets/logo.png" alt="Logo" class="mb-3">
                        <h3>Nananom Farms</h3>
                        <p class="mb-0">Admin Dashboard</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                        <?php endif; ?>
                        <!-- 
                        <div class="default-credentials">
                            <strong>Default Login:</strong><br>
                            Email: admin@nananomfarms.com<br>
                            Password: admin123
                        </div> -->

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="admin@nananomfarms.com" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password"
                                        value="admin123" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-login w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login to Dashboard
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Secure Admin Access
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>