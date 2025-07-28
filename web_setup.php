<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nananom Farms - Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .setup-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
    }

    .setup-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .setup-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }

    .step {
        display: none;
        padding: 2rem;
    }

    .step.active {
        display: block;
    }

    .step-indicator {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
    }

    .step-item {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 0.5rem;
        font-weight: bold;
        color: #6c757d;
    }

    .step-item.active {
        background: #667eea;
        color: white;
    }

    .step-item.completed {
        background: #28a745;
        color: white;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
    }

    .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .alert {
        border-radius: 10px;
        border: none;
    }

    .progress {
        height: 10px;
        border-radius: 10px;
        background: #e9ecef;
    }

    .progress-bar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .test-result {
        padding: 1rem;
        border-radius: 10px;
        margin: 0.5rem 0;
    }

    .test-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .test-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>
</head>

<body>
    <div class="setup-container">
        <div class="setup-card">
            <div class="setup-header">
                <i class="fas fa-leaf fa-3x mb-3"></i>
                <h1>Nananom Farms</h1>
                <p class="mb-0">Database Setup Wizard</p>
            </div>

            <!-- Progress Bar -->
            <div class="px-4 pt-3">
                <div class="progress mb-3">
                    <div class="progress-bar" role="progressbar" style="width: 20%" id="progressBar"></div>
                </div>
            </div>

            <!-- Step Indicators -->
            <div class="step-indicator">
                <div class="step-item active" id="step-indicator-1">1</div>
                <div class="step-item" id="step-indicator-2">2</div>
                <div class="step-item" id="step-indicator-3">3</div>
                <div class="step-item" id="step-indicator-4">4</div>
                <div class="step-item" id="step-indicator-5">5</div>
            </div>

            <form id="setupForm">
                <!-- Step 1: Welcome -->
                <div class="step active" id="step-1">
                    <h3><i class="fas fa-home me-2"></i>Welcome to Setup</h3>
                    <p class="text-muted">This wizard will help you set up your Nananom Farms database and create your
                        admin account.</p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>What we'll do:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Test your database connection</li>
                            <li>Create the database and tables</li>
                            <li>Set up your admin account</li>
                            <li>Configure basic settings</li>
                            <li>Verify everything works</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="nextStep()">
                            Get Started <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Database Configuration -->
                <div class="step" id="step-2">
                    <h3><i class="fas fa-database me-2"></i>Database Configuration</h3>
                    <p class="text-muted">Enter your database connection details.</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="db_host" class="form-label">Database Host</label>
                                <input type="text" class="form-control" id="db_host" name="db_host" value="localhost"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="db_port" class="form-label">Port</label>
                                <input type="number" class="form-control" id="db_port" name="db_port" value="3306">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="db_user" class="form-label">Username</label>
                                <input type="text" class="form-control" id="db_user" name="db_user" value="root"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="db_pass" class="form-label">Password</label>
                                <input type="password" class="form-control" id="db_pass" name="db_pass">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="db_name" class="form-label">Database Name</label>
                        <input type="text" class="form-control" id="db_name" name="db_name" value="nananom" required>
                        <div class="form-text">This database will be created if it doesn't exist.</div>
                    </div>

                    <div id="connection-test-result"></div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </button>
                        <div>
                            <button type="button" class="btn btn-outline-primary me-2" onclick="testConnection()">
                                <i class="fas fa-plug me-2"></i> Test Connection
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextStep()" id="db-next-btn"
                                disabled>
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Admin Account -->
                <div class="step" id="step-3">
                    <h3><i class="fas fa-user-shield me-2"></i>Admin Account</h3>
                    <p class="text-muted">Create your administrator account.</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin_first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="admin_first_name" name="admin_first_name"
                                    value="System" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin_last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="admin_last_name" name="admin_last_name"
                                    value="Administrator" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email"
                            value="admin@nananomfarms.com" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin_password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="admin_password" name="admin_password"
                                    value="admin123" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="admin_password_confirm" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="admin_password_confirm"
                                    name="admin_password_confirm" value="admin123" required>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Please change the default password after your first login for
                        security.
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 4: Installation -->
                <div class="step" id="step-4">
                    <h3><i class="fas fa-cogs me-2"></i>Installation</h3>
                    <p class="text-muted">Setting up your database and creating tables...</p>

                    <div id="installation-progress">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Preparing installation...</p>
                        </div>
                    </div>

                    <div id="installation-results" style="display: none;">
                        <!-- Results will be populated here -->
                    </div>

                    <div class="d-flex justify-content-between" id="installation-controls" style="display: none;">
                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()" id="installation-next-btn">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 5: Complete -->
                <div class="step" id="step-5">
                    <h3><i class="fas fa-check-circle me-2 text-success"></i>Setup Complete!</h3>
                    <p class="text-muted">Your Nananom Farms system has been successfully set up.</p>

                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Congratulations!</strong> Your system is ready to use.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-tachometer-alt fa-2x text-primary mb-3"></i>
                                    <h5>Admin Dashboard</h5>
                                    <p class="text-muted">Manage your system, users, and content.</p>
                                    <a href="admin/index.php" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt me-2"></i> Login to Admin
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-globe fa-2x text-info mb-3"></i>
                                    <h5>Public Website</h5>
                                    <p class="text-muted">View your public-facing website.</p>
                                    <a href="public/index.html" class="btn btn-info">
                                        <i class="fas fa-external-link-alt me-2"></i> View Website
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Login Credentials:</h5>
                        <div class="bg-light p-3 rounded">
                            <strong>Email:</strong> <span id="final-admin-email"></span><br>
                            <strong>Password:</strong> <span id="final-admin-password"></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <button type="button" class="btn btn-success btn-lg"
                            onclick="window.location.href='admin/index.php'">
                            <i class="fas fa-rocket me-2"></i> Get Started
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let currentStep = 1;
    const totalSteps = 5;

    function updateProgress() {
        const progress = (currentStep / totalSteps) * 100;
        document.getElementById('progressBar').style.width = progress + '%';

        // Update step indicators
        for (let i = 1; i <= totalSteps; i++) {
            const indicator = document.getElementById(`step-indicator-${i}`);
            if (i < currentStep) {
                indicator.className = 'step-item completed';
                indicator.innerHTML = '<i class="fas fa-check"></i>';
            } else if (i === currentStep) {
                indicator.className = 'step-item active';
                indicator.innerHTML = i;
            } else {
                indicator.className = 'step-item';
                indicator.innerHTML = i;
            }
        }
    }

    function showStep(step) {
        // Hide all steps
        for (let i = 1; i <= totalSteps; i++) {
            document.getElementById(`step-${i}`).classList.remove('active');
        }

        // Show current step
        document.getElementById(`step-${step}`).classList.add('active');
        currentStep = step;
        updateProgress();
    }

    function nextStep() {
        if (currentStep < totalSteps) {
            if (currentStep === 3) {
                // Validate admin account before proceeding
                if (!validateAdminAccount()) {
                    return;
                }
            }

            if (currentStep === 3) {
                // Start installation
                startInstallation();
            }

            showStep(currentStep + 1);
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    }

    function testConnection() {
        const formData = new FormData();
        formData.append('action', 'test_connection');
        formData.append('db_host', document.getElementById('db_host').value);
        formData.append('db_port', document.getElementById('db_port').value);
        formData.append('db_user', document.getElementById('db_user').value);
        formData.append('db_pass', document.getElementById('db_pass').value);
        formData.append('db_name', document.getElementById('db_name').value);

        const resultDiv = document.getElementById('connection-test-result');
        resultDiv.innerHTML =
            '<div class="test-result"><i class="fas fa-spinner fa-spin me-2"></i>Testing connection...</div>';

        fetch('setup_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML =
                        '<div class="test-result test-success"><i class="fas fa-check me-2"></i>' + data.message +
                        '</div>';
                    document.getElementById('db-next-btn').disabled = false;
                } else {
                    resultDiv.innerHTML = '<div class="test-result test-error"><i class="fas fa-times me-2"></i>' +
                        data.message + '</div>';
                    document.getElementById('db-next-btn').disabled = true;
                }
            })
            .catch(error => {
                resultDiv.innerHTML =
                    '<div class="test-result test-error"><i class="fas fa-times me-2"></i>Connection test failed: ' +
                    error.message + '</div>';
                document.getElementById('db-next-btn').disabled = true;
            });
    }

    function validateAdminAccount() {
        const password = document.getElementById('admin_password').value;
        const confirmPassword = document.getElementById('admin_password_confirm').value;
        const email = document.getElementById('admin_email').value;

        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return false;
        }

        if (password.length < 6) {
            alert('Password must be at least 6 characters long!');
            return false;
        }

        if (!email.includes('@')) {
            alert('Please enter a valid email address!');
            return false;
        }

        return true;
    }

    function startInstallation() {
        const progressDiv = document.getElementById('installation-progress');
        const resultsDiv = document.getElementById('installation-results');
        const controlsDiv = document.getElementById('installation-controls');

        progressDiv.style.display = 'block';
        resultsDiv.style.display = 'none';
        controlsDiv.style.display = 'none';

        // Collect all form data
        const formData = new FormData();
        formData.append('action', 'install');
        formData.append('db_host', document.getElementById('db_host').value);
        formData.append('db_port', document.getElementById('db_port').value);
        formData.append('db_user', document.getElementById('db_user').value);
        formData.append('db_pass', document.getElementById('db_pass').value);
        formData.append('db_name', document.getElementById('db_name').value);
        formData.append('admin_first_name', document.getElementById('admin_first_name').value);
        formData.append('admin_last_name', document.getElementById('admin_last_name').value);
        formData.append('admin_email', document.getElementById('admin_email').value);
        formData.append('admin_password', document.getElementById('admin_password').value);

        fetch('setup_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                progressDiv.style.display = 'none';
                resultsDiv.style.display = 'block';
                controlsDiv.style.display = 'flex';

                if (data.success) {
                    resultsDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Installation Successful!</strong>
                        </div>
                        <div class="installation-steps">
                            ${data.data.map(step => `
                                <div class="test-result test-success">
                                    <i class="fas fa-check me-2"></i>${step}
                                </div>
                            `).join('')}
                        </div>
                    `;

                    // Update final step with credentials
                    document.getElementById('final-admin-email').textContent = document.getElementById(
                        'admin_email').value;
                    document.getElementById('final-admin-password').textContent = document.getElementById(
                        'admin_password').value;

                    document.getElementById('installation-next-btn').disabled = false;
                } else {
                    resultsDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Installation Failed!</strong>
                        </div>
                        <div class="test-result test-error">
                            <i class="fas fa-times me-2"></i>${data.message}
                        </div>
                    `;
                    document.getElementById('installation-next-btn').disabled = true;
                }
            })
            .catch(error => {
                progressDiv.style.display = 'none';
                resultsDiv.style.display = 'block';
                controlsDiv.style.display = 'flex';

                resultsDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Installation Error!</strong>
                    </div>
                    <div class="test-result test-error">
                        <i class="fas fa-times me-2"></i>Installation failed: ${error.message}
                    </div>
                `;
                document.getElementById('installation-next-btn').disabled = true;
            });
    }

    // Initialize
    updateProgress();
    </script>
</body>

</html>