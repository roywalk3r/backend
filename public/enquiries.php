<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Nananom Farms is a web-based palm oil management system for farmers, buyers, and other stakeholders to manage their palm oil business efficiently.">
    <meta name="keywords" content="palm oil, management, farmers, buyers, business, efficient">
    <meta name="author" content="Roy Walk">
    <meta name="copyright" content="Copyright 2022 Nananom Farms">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/enquiries.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <script src="https://kit.fontawesome.com/00a449879e.js" crossorigin="anonymous"></script>
    <title>Enquiries | Nananom Farms </title>
</head>

<body>
    <?php
include_once __DIR__ . '/partials/header.php';

?>

    <main class="container">
        <div class="hero">
            <div class="overlay"></div>
            <div class="hero-image">
                <img src="assets/images/OPTIONS_1.jpg" alt="Hero Image">
            </div>

            <div class="hero-content">
                <h2>General Enquiries</h2>
                <p>Have questions or need information? Reach out to us , we’re here to help.</p>
            </div>
        </div>
        <!-----general enquiries form----->
        <div class="enquiries-flex" id="enquirySection">
            <div class="form-container">
                <h1>Send Us a Message</h1>
                <p class="subtitle">Have a question? We’ll be glad to assist you.</p>
                <form id="enquiryForm">
                    <div class="row">
                        <div class="input-group">
                            <label for="enquiryName">Your Name *</label>
                            <input type="text" id="enquiryName" name="name" placeholder="Your Name" required>
                        </div>
                        <div class="input-group">
                            <label for="enquiryEmail">Email Address *</label>
                            <input type="email" id="enquiryEmail" name="email" placeholder="Email address" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-group">
                            <label for="enquiryPhone">Phone Number</label>
                            <input type="tel" id="enquiryPhone" placeholder="Phone number" name="phone">
                        </div>
                        <div class="input-group">
                            <label for="enquirySubject">Subject *</label>
                            <input type="text" id="enquirySubject" name="subject" placeholder="Subject" required>
                        </div>
                    </div>

                    <div class="input-group full-width">
                        <label for="enquiryMessage">Message *</label>
                        <textarea id="enquiryMessage" name="message" rows="5" placeholder="Your Message"
                            required></textarea>
                    </div>

                    <div class="submit-section">
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-paper-plane"></i>
                            Send Enquiry
                        </button>
                    </div>
                </form>
            </div>
            <div class="contact-card">
                <div class="title">Contact Information</div>
                <div class="text">
                    <a href="#" class="contact-location">
                        <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z" />
                            </svg></span>
                        <span class="label">Location:</span>
                        <span class="value">Nananom Farms, Accra, Ghana</span>
                    </a>
                    <a href="tel:+233546666123" class="contact-phone">
                        <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M6.62 10.79a15.053 15.053 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1c-9.39 0-17-7.61-17-17a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.46.57 3.58a1 1 0 0 1-.24 1.01l-2.2 2.2z" />
                            </svg></span>
                        <span class="label">Phone:</span>
                        <span class="value">+233 546 666 123</span>
                    </a>
                    <a href="mailto:info@nananomfarms.com" class="contact-email">
                        <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 2v.01L12 13 4 6.01V6h16zM4 18V8.99l8 6 8-6V18H4z" />
                            </svg></span>
                        <span class="label">Email:</span>
                        <span class="value">info@nananomfarms.com</span>
                    </a>
                    <a href="https://wa.me/233546666123" target="_blank" class="contact-whatsapp">
                        <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#25D366"
                                viewBox="0 0 32 32">
                                <path
                                    d="M16.003 5.333c-5.896 0-10.667 4.771-10.667 10.667 0 1.885.495 3.726 1.434 5.351L5.334 27l5.812-1.414a10.64 10.64 0 0 0 4.857 1.147c5.895 0 10.667-4.771 10.667-10.667 0-2.844-1.108-5.518-3.119-7.529S18.847 5.333 16.003 5.333zm0 2c2.242 0 4.35.873 5.933 2.457s2.467 3.692 2.467 5.877c0 4.718-3.749 8.666-8.667 8.666-1.53 0-3.028-.418-4.337-1.209l-.62-.367-3.43.835.912-3.303-.402-.654a8.63 8.63 0 0 1-1.276-4.435c0-4.718 3.749-8.667 8.666-8.667zm-3.08 5.581c-.138 0-.283.008-.424.031-.408.065-.817.31-1.06.677-.423.65-.87 2.012 1.066 4.347 1.838 2.243 3.52 2.757 4.37 2.757.347 0 .642-.042.874-.114.592-.186 1.058-.75 1.2-1.193.1-.318.131-.703.02-.958s-.186-.296-.373-.387l-1.626-.764c-.284-.133-.492-.046-.68.157l-.36.4c-.048.054-.154.18-.213.171-.06-.008-.495-.137-1.07-.677-.532-.497-.718-.88-.799-1.06-.078-.175-.008-.28.046-.35.05-.066.119-.155.18-.24.06-.086.13-.17.185-.263.105-.174.183-.42.119-.662-.063-.24-.43-1.013-.597-1.303-.148-.26-.434-.41-.77-.414z" />
                            </svg></span>
                        <span class="label">WhatsApp:</span>
                        <span class="value">+233 546 666 123</span>
                    </a>
                    <div class="contact-extra">
                        <div class="hours">
                            <span class="icon"><i class="fas fa-clock"></i></span>
                            <span class="label">Hours:</span>
                            <span class="value">Mon - Sat: 8:00am - 6:00pm</span>
                        </div>
                        <div class="socials">
                            <span class="icon"><i class="fab fa-facebook"></i></span>
                            <a href="https://www.facebook.com/" target="_blank">Facebook</a>
                            <span class="icon"><i class="fab fa-instagram"></i></span>
                            <a href="https://www.instagram.com/" target="_blank">Instagram</a>
                        </div>
                    </div>
                    <div class="contact-note">
                        <em>We respond quickly during business hours. For urgent matters, call or WhatsApp us
                            directly!</em>
                    </div>
                </div>
            </div>
        </div>
        
        <!-----submit enquiry----->
        <div class="hero">
            <div class="overlay"></div>
            <div class="hero-image">
                <img src="assets/images/OTHER_OPTION_1.png" alt="Hero Image">
            </div>
            <div class="hero-content">
                <h2>We’re Here Whenever You Need Us</h2>
                <p>Send us a message today and we’re always ready to answer your questions and support your needs.</p>
                <a href="#enquirySection" class="ctaBtn ctaBt-main">Submit Enquiry</a>
            </div>
        </div>


    </main>
    <div id="toastContainer"></div>

    <?php
include_once __DIR__ . '/partials/footer.php';
?>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/contact.js"></script>

</body>

</html>