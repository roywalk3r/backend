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
    <link rel="stylesheet" href="assets/css/booking.css">
    <link rel="stylesheet" href="assets/css/enquiries.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <script src="https://kit.fontawesome.com/00a449879e.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/booking-faq.js"></script>
    <title>Booking | Nananom Farms</title>
</head>

<body>
    <?php
include_once __DIR__ . '/partials/header.php';

?>

    <main class="container">
        <div class="hero">
            <div class="overlay"></div>
            <div class="hero-image">
                <img src="assets/images/Gemini_Generated_1.png" alt="Hero Image">
            </div>

            <div class="hero-content">
                <h2>Book a Service with Nananom Farms</h2>
                <p>Easily schedule your palm oil service or order with us today.</p>
            </div>
        </div>
        <!-----service booking form----->
        <div class="form-container" id="service-booking">
            <h1>Book a service</h1>
            <p class="subheading">We’re ready to serve you! Book a service appointment by providing your details below.
            </p>
            <form id="bookingForm">
                <div class="row">
                    <div class="input-group">
                        <label for="customerName">Full Name *</label>
                        <input type="text" id="customerName" name="customer_name" placeholder="John Doe" required>
                    </div>

                    <div class="input-group">
                        <label for="customerEmail">Email Address *</label>
                        <input type="email" id="customerEmail" name="customer_email" placeholder="e0N1M@example.com"
                            required>
                    </div>

                </div>
                <div class="row">
                    <div class="input-group">
                        <label for="customerPhone">Phone Number *</label>
                        <input type="tel" id="customerPhone" name="customer_phone" placeholder="+233 (123) 456-7890"
                            required>
                    </div>
                    <div class="input-group">
                        <label for="bookingDate">Preferred Date *</label>
                        <input type="date" id="bookingDate" name="booking_date" required>
                    </div>
                </div>



                <div class="row">


                    <div class="input-group">
                        <label for="bookingTime">Preferred Time *</label>
                        <input type="time" id="bookingTime" name="booking_time" required>
                    </div>

                    <div class="input-group">
                        <label for="service">Select Service *</label>
                        <select id="service" name="service_id" required>
                            <option value="">Choose a service...</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                </div>

                <div class="input-group full-width">
                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes" rows="4"
                        placeholder="Any special requirements or additional information..."></textarea>
                </div>

                <div class="submit-section">
                    <button type="submit" id="submitBooking">Submit Booking</button>
                </div>

                <div class="thank-you">
                    <p>Thank you! Your booking has been received</p>
                </div>
            </form>
        </div>
        </div>

        <!---reasons for booking with us section--->
        <section class="booking-reasons redesigned">
            <h2>Why Book With Nananom Farms?</h2>
            <p class="subtitle">We provide exceptional value, quality produce, and unmatched service.</p>
            <div class="reasons-list">
                <div class="reason">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="#31610D" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg></span>
                    <span class="text">Trusted Local Farm</span>
                </div>
                <div class="reason">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="#31610D" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg></span>
                    <span class="text">Organic & Fresh Produce</span>
                </div>
                <div class="reason">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="#31610D" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg></span>
                    <span class="text">Quick & Easy Booking</span>
                </div>
                <div class="reason">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="#31610D" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg></span>
                    <span class="text">Nationwide Delivery</span>
                </div>
            </div>
        </section>

        <!---how it works and faq flex container--->
        <div class="info-flex">
            <section class="how-it-works redesigned">
                <h2>How It Works</h2>
                <p class="subtitle">Booking with us is simple. Here’s what to expect after you submit your form.</p>
                <ol class="steps">
                    <li>
                        <span class="step-num">1</span>
                        <span class="step-content">
                            <strong>Fill Out the Booking Form</strong>
                            <span>Provide your details and select your preferred service.</span>
                        </span>
                    </li>
                    <li>
                        <span class="step-num">2</span>
                        <span class="step-content">
                            <strong>We Review Your Request</strong>
                            <span>Our team checks your information and confirms availability.</span>
                        </span>
                    </li>
                    <li>
                        <span class="step-num">3</span>
                        <span class="step-content">
                            <strong>Get a Confirmation from Us</strong>
                            <span>We contact you to confirm your booking and discuss details.</span>
                        </span>
                    </li>
                    <li>
                        <span class="step-num">4</span>
                        <span class="step-content">
                            <strong>Enjoy Our Service</strong>
                            <span>Sit back as we deliver quality service right on time.</span>
                        </span>
                    </li>
                </ol>
            </section>
            <section class="faq redesigned">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-list">
                    <details>
                        <summary><span>How long does it take to confirm a service booking?</span></summary>
                        <div class="faq-answer">We’ll reach out within 24 hours after you submit your booking request.
                            Once you submit your form, our team immediately reviews your information and contacts you
                            via your preferred method (phone or email) to confirm your booking. You will always receive
                            a confirmation message, and if you don’t hear from us in 24 hours, please reach out
                            directly—your satisfaction is our priority!</div>
                    </details>
                    <details>
                        <summary><span>Can I change my booking details after submission?</span></summary>
                        <div class="faq-answer">Yes! Simply contact our support team by phone, WhatsApp, or email as
                            soon as possible. We’ll update your booking details, whether it’s a change of date, service
                            type, or recipient. Our team is always happy to accommodate your needs and ensure your
                            booking experience is smooth and flexible.</div>
                    </details>
                    <details>
                        <summary><span>Do I need to make payment when booking?</span></summary>
                        <div class="faq-answer">No upfront payment is required to book a service. After reviewing your
                            request, we’ll discuss payment options and send you a secure payment link or bank details if
                            needed. You only pay once your booking is confirmed and you are satisfied with the
                            arrangements. We believe in transparency and trust with every transaction.</div>
                    </details>
                    <details>
                        <summary><span>Can I book for someone else?</span></summary>
                        <div class="faq-answer">Absolutely! You can book for a friend, family member, or organization.
                            Just provide their details in the booking form, and we’ll make sure all communication and
                            delivery is handled with care and privacy. If you’d like to include a personalized message
                            or gift, let us know and we’ll make it special!</div>
                    </details>
                    <details>
                        <summary><span>What if I need to cancel or reschedule?</span></summary>
                        <div class="faq-answer">You can cancel or reschedule your booking at any time before the service
                            date by contacting our support team. There are no cancellation fees, and we’ll work with you
                            to find a new date or process your cancellation quickly. We understand plans change and aim
                            to be as flexible as possible.</div>
                    </details>
                    <details>
                        <summary><span>Is my information safe with you?</span></summary>
                        <div class="faq-answer">Yes, your privacy and security are very important to us. All information
                            you provide is encrypted and used only for your booking and service experience. We never
                            share your data with third parties, and you can request deletion of your data at any time.
                        </div>
                    </details>
                    <details>
                        <summary><span>Do you offer support after booking?</span></summary>
                        <div class="faq-answer">Yes! Our support team is available before, during, and after your
                            booking. Whether you have questions about your order, delivery, or need help with anything
                            else, we’re just a call or message away. We pride ourselves on responsive, friendly service
                            at every step.</div>
                    </details>
                    <details>
                        <summary><span>How do I know my booking was successful?</span></summary>
                        <div class="faq-answer">You’ll receive an instant confirmation message on the website after
                            submitting your form, as well as a follow-up email or SMS. If you don’t see a confirmation,
                            please check your spam folder or contact us directly. We want you to feel confident and
                            informed at every stage.</div>
                    </details>
                    <details>
                        <summary><span>Can I request a specific delivery date?</span></summary>
                        <div class="faq-answer">Yes, you can specify your preferred delivery or service date in the
                            booking form. We do our best to accommodate all requests, and if there’s any conflict, our
                            team will reach out to discuss alternatives and ensure you get the timing that works best
                            for you.</div>
                    </details>
                    <details>
                        <summary><span>What services do you offer for booking?</span></summary>
                        <div class="faq-answer">We offer a variety of services including farm produce delivery, palm oil
                            processing, farm consultation, and more. You can see a full list of services in the booking
                            form dropdown. If you need something unique, just ask—our team is always ready to help with
                            custom requests!</div>
                    </details>
                </div>
            </section>
        </div>

        <div class="hero">
            <div class="overlay"></div>
            <div class="hero-image">
                <img src="assets/images/Gemini_Generated_Image.png" alt="Hero Image">
            </div>

            <div class="hero-content">
                <h2>Ready to Book Your Service?</h2>
                <p>Don’t miss out on our reliable service and top-quality products. Book with Nananom Farms now!</p>
                <a href="#service-booking" class="ctaBtn ctaBt-main">Book now</a>
            </div>
        </div>

    </main>
    <?php
include_once __DIR__ . '/partials/footer.php';
?>
    <script src="assets/js/main.js"></script>
</body>

</html>