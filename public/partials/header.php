<header>
    <nav>
        <div class="logo">
            <a href="index.php"><img src="assets/images/nananom-logo.png" alt="Logo"></a>
        </div>
        <div class="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <ul class="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="booking.php">Service Booking</a></li>
            <li><a href="enquiries.php">General Enquiries</a></li>
            <li><a href="contact.php">Contact</a></li>
            <div class="user-menu">
                <button class="user-menu-toggle" id="userMenuToggle">
                    <i class="fas fa-user-circle"></i>
                    <span id="userName">Loading...</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
                    <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </ul>

    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('show');
            menuToggle.classList.toggle('active');
        });

        // Close menu when clicking on a nav link
        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('show');
                menuToggle.classList.remove('active');
            });
        });
    }

    function checkAuthStatus() {
        fetch("/api/check_auth.php")
            .then((response) => response.json())
            .then((data) => {
                if (!data.authenticated) {
                    document.querySelector(".user-menu").style.display = "none"
                    const loginLink = document.createElement("li")
                    loginLink.innerHTML =
                        `<a href="login.php">Login <i class="fa-solid fa-arrow-right-to-bracket"></i></a>`
                    document.querySelector(".nav-menu").appendChild(loginLink)

                }
            })
            .catch((error) => {

                console.error("Auth check error:", error)
                window.location.href = "login.php"
            })
    }

    function userMenuToggle() {
        // Initialize user menu dropdown
        const userMenuToggle = document.getElementById("userMenuToggle")
        const userDropdown = document.getElementById("userDropdown")

        if (userMenuToggle && userDropdown) {
            userMenuToggle.addEventListener("click", (e) => {
                e.stopPropagation()
                userDropdown.classList.toggle("show")
            })

            // Close dropdown when clicking outside
            document.addEventListener("click", () => {
                userDropdown.classList.remove("show")
            })
        }
    }
    checkAuthStatus()
    userMenuToggle()
});
</script>
<script src="assets/js/profile.js"></script>