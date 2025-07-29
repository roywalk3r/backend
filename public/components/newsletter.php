<div class="newsletter-container">
    <div class="newsletter">
        <div class="newsletter-icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="24" fill="#f1dcb5" />
                <path
                    d="M14 18C14 16.8954 14.8954 16 16 16H32C33.1046 16 34 16.8954 34 18V30C34 31.1046 33.1046 32 32 32H16C14.8954 32 14 31.1046 14 30V18Z"
                    stroke="#8C6B65" stroke-width="2" />
                <path d="M34 18L24 25L14 18" stroke="#8C6B65" stroke-width="2" />
            </svg>
        </div>
        <h1>Stay In The Loop</h1>
        <p>Be the first to know about new arrivals, exclusive deals, and tips. Join our community and never miss a
            trend.</p>
        <form class="newsletter-form" method="POST" action="/frontend/newsletter.php">
            <input class="email" type="email" name="email" placeholder="Enter your email address" required>
            <button class="btn" type="submit">Subscribe</button>
        </form>
        <div class="newsletter-note">No spam, unsubscribe at any time. We respect your privacy.</div>
    </div>
</div>