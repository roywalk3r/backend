<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Nananom Farms</title>
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
            <h1>Create Your Account</h1>
            <p>Join Nananom Farms and start your agricultural journey today!</p>
        </div>

        <div class="auth-page">
            <!-- Left Side - Image -->
            <div class="auth-left">
                <img src="assets/images/registerLogo.png" alt="Nananom Farms Registration" class="auth-image">
                <div class="image-overlay">
                    <div class="overlay-content">
                        <h2>Join Our Community!</h2>
                        <p>Create your account to access premium agricultural services and expert support.</p>
                        <div class="features">
                            <div class="feature">
                                <i class="fas fa-tractor"></i>
                                <span>Professional Farm Services</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-users"></i>
                                <span>Expert Agricultural Support</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-leaf"></i>
                                <span>Sustainable Farming Solutions</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="auth-right">
                <form id="registerForm" class="auth-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="firstName" name="firstName" placeholder="First name" required>
                            </div>
                            <div class="field-error" id="firstNameError" style="display: none;"></div>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="lastName" name="lastName" placeholder="Last name" required>
                            </div>
                            <div class="field-error" id="lastNameError" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                        </div>
                        <div class="field-error" id="emailError" style="display: none;"></div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
                        </div>
                        <div class="field-error" id="phoneError" style="display: none;"></div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Create a strong password"
                                required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Password strength</span>
                        </div>
                        <div class="field-error" id="passwordError" style="display: none;"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirmPassword" name="confirmPassword"
                                placeholder="Confirm your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="field-error" id="confirmPasswordError" style="display: none;"></div>
                    </div>

                    <div class="checkbox-group">
                        <label class="checkbox-container">
                            <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                            <span class="checkmark"></span>
                            <span class="checkbox-text">I agree to the <a href="#" target="_blank">Terms of Service</a>
                                and <a href="#" target="_blank">Privacy Policy</a></span>
                        </label>
                        <div class="field-error" id="agreeTermsError" style="display: none;"></div>
                    </div>

                    <div class="checkbox-group">
                        <label class="checkbox-container">
                            <input type="checkbox" id="newsletter" name="newsletter">
                            <span class="checkmark"></span>
                            <span class="checkbox-text">Subscribe to our newsletter for updates and offers</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <span class="btn-text">Create Account</span>
                        <div class="btn-loading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>



                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.html">Sign in here</a></p>
                    </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer"></div>

    <script src="assets/js/auth.js"></script>
    <script src="assets/js/register.js"></script>
    <script src="assets/js/toast.js"></script>
</body>

</html>