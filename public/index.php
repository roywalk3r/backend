<?php

// Check if setup is required
require_once __DIR__ . '/../init.php'
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nananom Farms - Premium Palm Oil & Agricultural Services</title>
    <meta name="description"
        content="Leading supplier of premium palm oil and agricultural services in Ghana. Quality guaranteed, sustainable farming practices.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/newsletter.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://kit.fontawesome.com/00a449879e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    


</head>

<body>
    <?php
    include_once __DIR__ . '/partials/header.php';
    ?>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay active">
        <div class="loading-content">
            <div class="spinner"></div>
            <h3>Loading Nananom Farms</h3>
            <p>Please wait while we prepare your experience...</p>
        </div>
    </div>
    <main class="container">
        <div class="hero">
            <div class="overlay"></div>
            <div class="hero-image">
                <img src="assets/images/hero_hp_pic.jpeg" alt="Hero Image">
            </div>
            <div class="hero-welcome">
                <img src="assets/images/logo.png" alt="Welcome Image">
                <h3>Welcome!</h3>
            </div>
            <div class="hero-content">
                <h2>Your Trusted Palm Oil Partner</h2>
                <p>Delivering Premium palm oil and reliable farm services, experience,quality tradition, and trust with
                    Nananom Farms.</p>
                <button onclick="openBookingModal()" class="ctaBtn ctaBt-main">Book a Service</button>
            </div>
        </div>

        <div class="about-us">
            <h3>About Us</h3>
            <div class="about-content">
                <div class="side-left">
                    <img src="assets/images/about_us_pic.jpeg" alt="About Us Image">
                    <p>At Nananom Farms, we are passionate about producing 100% pure, locally-sourced palm oil using
                        natural and traditional methods. Nestled in the heart of Ghana, our farm is committed to
                        quality, sustainability, and supporting the local community,one bottle at a time.</p>
                </div>
                <div class="side-right">
                    <img src="assets/images/about_us_pic_on_hp.png" alt="About Us Image">
                    <p>
                        With a deep respect for tradition and nature, Nananom Farms blends time-tested farming practices
                        with modern care to deliver palm oil that’s pure, nutritious, and full of flavor. Every drop
                        reflects our commitment to quality, community, and sustainability from our farm to your table..
                    </p>
                </div>
            </div>
            <a href="about.php" class="ctaBtn">Learn More
                <i class="fa-solid fa-arrow-up-right-from-square showing-arrow"></i>
                <i class="fa-solid fa-arrow-right-from-bracket hidden-arrow"></i>
            </a>
        </div>



        <!-- <section id="services" class="services">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Our Services</h2>
                    <p class="section-subtitle">Comprehensive agricultural solutions tailored to your needs</p>
                </div>

                <div class="service-filters">
                    <button class="filter-btn active" data-filter="all">All Services</button>
                    <button class="filter-btn" data-filter="crude_oil">Crude Oil</button>
                    <button class="filter-btn" data-filter="refined_oil">Refined Oil</button>
                    <button class="filter-btn" data-filter="consultation">Consultation</button>
                    <button class="filter-btn" data-filter="bulk_supply">Bulk Supply</button>
                </div>

                <div id="servicesGrid" class="services-grid">
                     <div class="loading-container">
                        <div class="spinner"></div>
                        <p>Loading services...</p>
                    </div>
                </div>
            </div>
        </section> -->
        <?php include_once __DIR__ . '/components/featured-section.php'; ?>
        <?php include_once __DIR__ . '/components/what-we-offer.php'; ?>
        <?php include_once __DIR__ . '/components/testimonials.php'; ?>
        <?php include_once __DIR__ . '/components/cta.php'; ?>




        <!-- Booking Modal -->
        <div id="bookingModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Book Our Service</h3>
                    <button onclick="closeBookingModal()" class="modal-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="bookingForm" class="booking-form">
                    <!-- Step 1: Personal Information -->
                    <div class="form-step active" data-step="1">
                        <h4>Personal Information</h4>

                        <div class="form-group">
                            <label for="customerName">Full Name *</label>
                            <input type="text" id="customerName" name="customer_name" required>
                        </div>

                        <div class="form-group">
                            <label for="customerEmail">Email Address *</label>
                            <input type="email" id="customerEmail" name="customer_email" required>
                        </div>

                        <div class="form-group">
                            <label for="customerPhone">Phone Number *</label>
                            <input type="tel" id="customerPhone" name="customer_phone" required>
                        </div>
                    </div>

                    <!-- Step 2: Service Details -->
                    <div class="form-step" data-step="2">
                        <h4>Service Details</h4>

                        <div class="form-group">
                            <label for="service">Select Service *</label>
                            <select id="service" name="service_id" required>
                                <option value="">Choose a service...</option>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="bookingDate">Preferred Date *</label>
                                <input type="date" id="bookingDate" name="booking_date" required>
                            </div>

                            <div class="form-group">
                                <label for="bookingTime">Preferred Time *</label>
                                <input type="time" id="bookingTime" name="booking_time" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Additional Notes</label>
                            <textarea id="notes" name="notes" rows="4"
                                placeholder="Any special requirements or additional information..."></textarea>
                        </div>
                    </div>

                    <!-- Form Navigation -->
                    <div class="form-navigation">
                        <button type="button" id="prevStep" class="btn btn-outline" style="display: none;">
                            <i class="fas fa-arrow-left"></i>
                            Previous
                        </button>

                        <button type="button" id="nextStep" class="btn btn-primary">
                            Next
                            <i class="fas fa-arrow-right"></i>
                        </button>

                        <button type="submit" id="submitBooking" class="btn btn-success" style="display: none;">
                            <i class="fas fa-check"></i>
                            Submit Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Enquiry Modal -->
        <div id="enquiryModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Send Enquiry</h3>
                    <button onclick="closeEnquiryModal()" class="modal-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="enquiryForm" class="enquiry-form">
                    <div class="form-group">
                        <label for="enquiryName">Your Name *</label>
                        <input type="text" id="enquiryName" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="enquiryEmail">Email Address *</label>
                        <input type="email" id="enquiryEmail" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="enquiryPhone">Phone Number</label>
                        <input type="tel" id="enquiryPhone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="enquirySubject">Subject *</label>
                        <input type="text" id="enquirySubject" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label for="enquiryMessage">Message *</label>
                        <textarea id="enquiryMessage" name="message" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="fas fa-paper-plane"></i>
                        Send Enquiry
                    </button>
                </form>
            </div>
        </div>

        <!-- Feedback Modal -->
        <div id="feedbackModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Share Your Feedback</h3>
                    <button onclick="closeFeedbackModal()" class="modal-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="feedbackForm" class="feedback-form">
                    <div class="form-group">
                        <label for="feedbackName">Your Name *</label>
                        <input type="text" id="feedbackName" name="customer_name" required>
                    </div>

                    <div class="form-group">
                        <label for="feedbackEmail">Email Address *</label>
                        <input type="email" id="feedbackEmail" name="customer_email" required>
                    </div>

                    <div class="form-group">
                        <label for="feedbackService">Service Used *</label>
                        <select id="feedbackService" name="service_id" required>
                            <option value="">Select service...</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Rating *</label>
                        <div class="rating-input">
                            <input type="radio" id="star5" name="rating" value="5">
                            <label for="star5"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star4" name="rating" value="4">
                            <label for="star4"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star3" name="rating" value="3">
                            <label for="star3"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star2" name="rating" value="2">
                            <label for="star2"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star1" name="rating" value="1">
                            <label for="star1"><i class="fas fa-star"></i></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="feedbackComment">Your Feedback *</label>
                        <textarea id="feedbackComment" name="comment" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="fas fa-heart"></i>
                        Submit Feedback
                    </button>
                </form>
            </div>
        </div>

        <!-- Toast Notification -->
        <div id="toast" class="toast">
            <div class="toast-content">
                <i class="toast-icon"></i>
                <span class="toast-message"></span>
            </div>
            <button onclick="closeToast()" class="toast-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </main>
    <?php
    include_once __DIR__ . '/partials/footer.php';
    ?>
    <script src="assets/js/main.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".testimonials-carousel", {
            slidesPerView: 3,
            spaceBetween: 30,
            freeMode: true,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10,
                },
                640: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            }
        });
    </script>


</body>

</html>