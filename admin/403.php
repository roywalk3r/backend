<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden | Nananom Farms</title>
    <link rel="stylesheet" href="assets/css/error.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-ban"></i>
            </div>

            <div class="error-code">403</div>

            <div class="error-title">Access Forbidden</div>

            <div class="error-message">
                <p>Sorry, you don't have permission to access this resource.</p>
                <p>This area is restricted and requires proper authorization.</p>
            </div>

            <div class="error-actions">
                <a href="../../public/index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Go Home
                </a>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
                <a href="../../public/contact.php" class="btn btn-primary">
                    <i class="fas fa-envelope"></i>
                    Contact Support
                </a>
            </div>

            <div class="error-help">
                <h3>What can you do?</h3>
                <ul>
                    <li><i class="fas fa-check"></i> Check if you're logged in with the correct account</li>
                    <li><i class="fas fa-check"></i> Contact the administrator if you believe this is an error</li>
                    <li><i class="fas fa-check"></i> Return to the homepage and try again</li>
                    <li><i class="fas fa-check"></i> Clear your browser cache and cookies</li>
                </ul>
            </div>
        </div>

        <div class="error-decoration">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
            <div class="floating-shape shape-4"></div>
        </div>
    </div>

    <script>
    // Add some interactive effects
    document.addEventListener('DOMContentLoaded', function() {
        const shapes = document.querySelectorAll('.floating-shape');

        shapes.forEach((shape, index) => {
            shape.style.animationDelay = `${index * 0.5}s`;
        });

        // Add click effect to buttons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                this.appendChild(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    });
    </script>
</body>

</html>