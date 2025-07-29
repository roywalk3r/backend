<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Nananom Farms</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-close">
            <a href="index.php" title="Close">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <div class="auth-header">
            <img src="assets/images/logo.png" alt="Nananom Farms Logo" class="logo">
            <h1>Welcome Back!</h1>
            <p>Sign in to access your account and manage your services</p>
        </div>

        <div class="auth-page">
            <!-- Left Side - Image -->
            <div class="auth-left">
                <img src="assets/images/login.png" alt="Nananom Farms" class="auth-image">
                <div class="image-overlay">
                    <div class="overlay-content">
                        <h2>Welcome Back!</h2>
                        <p>Sign in to access your account and manage your agricultural services with Nananom Farms.</p>
                        <div class="features">
                            <div class="feature">
                                <i class="fas fa-seedling"></i>
                                <span>Manage Your Bookings</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-chart-line"></i>
                                <span>Track Service Progress</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-headset"></i>
                                <span>Get Expert Support</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="auth-right">
                <form id="loginForm" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                        </div>
                        <div class="field-error" id="emailError" style="display: none;"></div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password"
                                required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="field-error" id="passwordError" style="display: none;"></div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" id="rememberMe" name="rememberMe">
                            <span class="checkmark"></span>
                            <span class="checkbox-text">Remember me</span>
                        </label>
                        <a href="forgot-password.html" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <span class="btn-text">Sign In</span>
                        <div class="btn-loading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>

                </form>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php">Create one here</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer"></div>

    <script src="assets/js/auth.js"></script>
    <script src="assets/js/login.js"></script>
    <script src="assets/js/toast.js"></script>
</body>

</html>