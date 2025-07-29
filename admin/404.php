<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Nananom Farms</title>
    <link rel="stylesheet" href="assets/css/error.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-search"></i>
            </div>

            <div class="error-code">404</div>

            <div class="error-title">Page Not Found</div>

            <div class="error-message">
                <p>Oops! The page you're looking for seems to have wandered off.</p>
                <p>It might have been moved, deleted, or you entered the wrong URL.</p>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search our website...">
                    <button type="button" id="searchBtn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="error-actions">
                <a href="../../public/" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Go Home
                </a>
                <a href="../../public/booking.php" class="btn btn-secondary">
                    <i class="fas fa-leaf"></i>
                    Our Services
                </a>
                <a href="../../public/contact.php" class="btn btn-outline">
                    <i class="fas fa-envelope"></i>
                    Contact Us
                </a>
            </div>

            <!-- <div class="error-help">
                <h3>Popular Pages</h3>
                <div class="popular-links">
                    <a href="about.html">
                        <i class="fas fa-info-circle"></i>
                        About Us
                    </a>
                    <a href="services.html">
                        <i class="fas fa-seedling"></i>
                        Farm Services
                    </a>
                    <a href="blog.html">
                        <i class="fas fa-blog"></i>
                        Farm Blog
                    </a>
                    <a href="contact.html">
                        <i class="fas fa-phone"></i>
                        Get in Touch
                    </a>
                </div>
            </div> -->
        </div>

        <div class="error-decoration">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
            <div class="floating-shape shape-4"></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const shapes = document.querySelectorAll('.floating-shape');

        // Animate shapes
        shapes.forEach((shape, index) => {
            shape.style.animationDelay = `${index * 0.7}s`;
        });

        // Search functionality
        function performSearch() {
            const query = searchInput.value.trim();
            if (query) {
                // Redirect to a search page or Google search
                window.location.href =
                    `https://www.google.com/search?q=site:${window.location.hostname} ${encodeURIComponent(query)}`;
            }
        }

        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
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