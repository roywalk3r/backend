// Modern Frontend JavaScript
class NananomFarms {
  constructor() {
    this.services = []
    this.currentStep = 1
    this.maxSteps = 2
    this.init()
  }

  init() {
    this.setupEventListeners()
    this.loadServices()
    this.loadTestimonials()
    this.initializeAnimations()
    this.setMinDate()
    this.hideLoadingOverlay()
  }

  setupEventListeners() {
    // Navigation
    document.getElementById("hamburger").addEventListener("click", this.toggleMobileMenu)
    document.addEventListener("click", this.closeMobileMenuOnOutsideClick)
    window.addEventListener("scroll", this.handleScroll)

    // Smooth scrolling for navigation links
    document.querySelectorAll(".nav-link").forEach((link) => {
      link.addEventListener("click", this.smoothScroll)
    })

    // Form submissions
    document.getElementById("bookingForm").addEventListener("submit", this.handleBookingSubmit.bind(this))
    document.getElementById("enquiryForm").addEventListener("submit", this.handleEnquirySubmit.bind(this))
    document.getElementById("feedbackForm").addEventListener("submit", this.handleFeedbackSubmit.bind(this))
    document.getElementById("quickContactForm").addEventListener("submit", this.handleQuickContactSubmit.bind(this))

    // Modal controls
    document.addEventListener("click", this.handleModalClicks.bind(this))
    document.addEventListener("keydown", this.handleKeydown.bind(this))

    // Form navigation
    document.getElementById("nextStep").addEventListener("click", this.nextStep.bind(this))
    document.getElementById("prevStep").addEventListener("click", this.prevStep.bind(this))

    // Service filters
    document.querySelectorAll(".filter-btn").forEach((btn) => {
      btn.addEventListener("click", this.filterServices.bind(this))
    })

    // Rating inputs
    document.querySelectorAll(".rating-input input").forEach((input) => {
      input.addEventListener("change", this.updateRatingDisplay)
    })
  }

  // Navigation Methods
  toggleMobileMenu() {
    const hamburger = document.getElementById("hamburger")
    const navMenu = document.getElementById("navMenu")

    hamburger.classList.toggle("active")
    navMenu.classList.toggle("active")
  }

  closeMobileMenuOnOutsideClick(e) {
    const hamburger = document.getElementById("hamburger")
    const navMenu = document.getElementById("navMenu")

    if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
      hamburger.classList.remove("active")
      navMenu.classList.remove("active")
    }
  }

  handleScroll() {
    const navbar = document.getElementById("navbar")
    const scrolled = window.scrollY > 50

    navbar.classList.toggle("scrolled", scrolled)

    // Update active navigation link
    const sections = document.querySelectorAll("section[id]")
    const navLinks = document.querySelectorAll(".nav-link")

    let current = ""
    sections.forEach((section) => {
      const sectionTop = section.offsetTop - 100
      if (window.scrollY >= sectionTop) {
        current = section.getAttribute("id")
      }
    })

    navLinks.forEach((link) => {
      link.classList.remove("active")
      if (link.getAttribute("href") === `#${current}`) {
        link.classList.add("active")
      }
    })
  }

  smoothScroll(e) {
    e.preventDefault()
    const targetId = e.target.getAttribute("href")
    const targetSection = document.querySelector(targetId)

    if (targetSection) {
      const offsetTop = targetSection.offsetTop - 80
      window.scrollTo({
        top: offsetTop,
        behavior: "smooth",
      })
    }

    // Close mobile menu
    document.getElementById("hamburger").classList.remove("active")
    document.getElementById("navMenu").classList.remove("active")
  }

  // Data Loading Methods
  async loadServices() {
    try {
      this.showLoading("servicesGrid")
      const response = await fetch("../api/get_services.php")
      const data = await response.json()

      if (data.success) {
        this.services = data.services
        this.displayServices(data.services)
        this.populateServiceSelect(data.services)
      } else {
        this.showToast("Failed to load services", "error")
        this.displayServiceError()
      }
    } catch (error) {
      console.error("Error loading services:", error)
      this.showToast("Failed to load services", "error")
      this.displayServiceError()
    }
  }

  displayServices(services) {
    const servicesGrid = document.getElementById("servicesGrid")

    if (!services || services.length === 0) {
      servicesGrid.innerHTML = `
                <div class="no-services">
                    <i class="fas fa-info-circle"></i>
                    <h3>No Services Available</h3>
                    <p>Please check back later for our service offerings.</p>
                </div>
            `
      return
    }

    servicesGrid.innerHTML = services
      .map(
        (service) => `
            <div class="service-card" data-category="${service.category || "general"}">
                <div class="service-image">
                    <i class="fas fa-${this.getServiceIcon(service.category)}"></i>
                </div>
                <div class="service-content">
                    <h3 class="service-title">${this.escapeHtml(service.name)}</h3>
                    <p class="service-description">${this.escapeHtml(service.description)}</p>
                    <div class="service-price">
                        <span class="price-amount">$${Number.parseFloat(service.price).toFixed(2)}</span>
                        <span class="price-unit">per ${service.unit || "unit"}</span>
                    </div>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Quality Guaranteed</li>
                        <li><i class="fas fa-check"></i> Fast Delivery</li>
                        <li><i class="fas fa-check"></i> Expert Support</li>
                    </ul>
                    <button class="btn btn-primary btn-full" onclick="app.bookService('${this.escapeHtml(service.name)}')">
                        <i class="fas fa-calendar-plus"></i>
                        Book Now
                    </button>
                </div>
            </div>
        `,
      )
      .join("")
  }

  displayServiceError() {
    const servicesGrid = document.getElementById("servicesGrid")
    servicesGrid.innerHTML = `
            <div class="service-error">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Unable to Load Services</h3>
                <p>There was an error loading our services. Please try again later.</p>
                <button class="btn btn-primary" onclick="app.loadServices()">
                    <i class="fas fa-refresh"></i>
                    Retry
                </button>
            </div>
        `
  }

  populateServiceSelect(services) {
    const serviceSelect = document.getElementById("service")
    serviceSelect.innerHTML = '<option value="">Choose a service...</option>'

    services.forEach((service) => {
      const option = document.createElement("option")
      option.value = service.name
      option.textContent = `${service.name} - $${Number.parseFloat(service.price).toFixed(2)}/${service.unit}`
      serviceSelect.appendChild(option)
    })
  }

  async loadTestimonials() {
    try {
      const response = await fetch("../api/get_feedback.php")
      const data = await response.json()

      if (data.success && data.feedback.length > 0) {
        this.displayTestimonials(data.feedback.slice(0, 6)) // Show top 6
      } else {
        this.displayDefaultTestimonials()
      }
    } catch (error) {
      console.error("Error loading testimonials:", error)
      this.displayDefaultTestimonials()
    }
  }

  displayTestimonials(testimonials) {
    const testimonialsGrid = document.getElementById("testimonialsGrid")

    testimonialsGrid.innerHTML = testimonials
      .map(
        (testimonial) => `
            <div class="testimonial-card">
                <div class="testimonial-content">
                    ${this.escapeHtml(testimonial.message)}
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        ${(testimonial.customer_name || "A").charAt(0).toUpperCase()}
                    </div>
                    <div class="author-info">
                        <h5>${this.escapeHtml(testimonial.customer_name || "Anonymous")}</h5>
                        <span>Verified Customer</span>
                        <div class="testimonial-rating">
                            ${this.generateStars(testimonial.rating || 5)}
                        </div>
                    </div>
                </div>
            </div>
        `,
      )
      .join("")
  }

  displayDefaultTestimonials() {
    const testimonialsGrid = document.getElementById("testimonialsGrid")
    const defaultTestimonials = [
      {
        message:
          "Excellent quality palm oil and outstanding customer service. Nananom Farms has been our trusted supplier for over 5 years.",
        author: "John Mensah",
        company: "Ghana Food Industries",
        rating: 5,
      },
      {
        message:
          "Reliable delivery and competitive pricing. Their sustainable farming practices align perfectly with our company values.",
        author: "Sarah Osei",
        company: "EcoManufacturing Ltd",
        rating: 5,
      },
      {
        message:
          "Professional consultation services helped us optimize our palm oil usage. Highly recommended for bulk orders.",
        author: "Michael Asante",
        company: "West Africa Trading",
        rating: 4,
      },
    ]

    testimonialsGrid.innerHTML = defaultTestimonials
      .map(
        (testimonial) => `
            <div class="testimonial-card">
                <div class="testimonial-content">
                    ${testimonial.message}
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        ${testimonial.author.charAt(0)}
                    </div>
                    <div class="author-info">
                        <h5>${testimonial.author}</h5>
                        <span>${testimonial.company}</span>
                        <div class="testimonial-rating">
                            ${this.generateStars(testimonial.rating)}
                        </div>
                    </div>
                </div>
            </div>
        `,
      )
      .join("")
  }

  // Form Handling Methods
  async handleBookingSubmit(e) {
    e.preventDefault()

    if (!this.validateBookingForm()) {
      return
    }

    const formData = new FormData(e.target)
    const data = Object.fromEntries(formData.entries())

    try {
      this.showButtonLoading(e.target.querySelector('button[type="submit"]'))

      const response = await fetch("../api/create_booking.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      })

      const result = await response.json()

      if (result.success) {
        this.showToast("Booking submitted successfully! We will contact you soon.", "success")
        this.closeBookingModal()
        e.target.reset()
        this.resetFormSteps()
      } else {
        this.showToast(result.message || "Failed to submit booking", "error")
      }
    } catch (error) {
      console.error("Error submitting booking:", error)
      this.showToast("Failed to submit booking. Please try again.", "error")
    } finally {
      this.hideButtonLoading(e.target.querySelector('button[type="submit"]'))
    }
  }

  async handleEnquirySubmit(e) {
    e.preventDefault()

    const formData = new FormData(e.target)
    const data = Object.fromEntries(formData.entries())

    try {
      this.showButtonLoading(e.target.querySelector('button[type="submit"]'))

      const response = await fetch("../api/create_enquiry.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      })

      const result = await response.json()

      if (result.success) {
        this.showToast("Enquiry submitted successfully! We will respond within 24 hours.", "success")
        this.closeEnquiryModal()
        e.target.reset()
      } else {
        this.showToast(result.message || "Failed to submit enquiry", "error")
      }
    } catch (error) {
      console.error("Error submitting enquiry:", error)
      this.showToast("Failed to submit enquiry. Please try again.", "error")
    } finally {
      this.hideButtonLoading(e.target.querySelector('button[type="submit"]'))
    }
  }

  async handleFeedbackSubmit(e) {
    e.preventDefault()

    const formData = new FormData(e.target)
    const data = Object.fromEntries(formData.entries())

    try {
      this.showButtonLoading(e.target.querySelector('button[type="submit"]'))

      const response = await fetch("../api/create_feedback.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      })

      const result = await response.json()

      if (result.success) {
        this.showToast("Thank you for your feedback!", "success")
        this.closeFeedbackModal()
        e.target.reset()
        this.resetRatingInput()
      } else {
        this.showToast(result.message || "Failed to submit feedback", "error")
      }
    } catch (error) {
      console.error("Error submitting feedback:", error)
      this.showToast("Failed to submit feedback. Please try again.", "error")
    } finally {
      this.hideButtonLoading(e.target.querySelector('button[type="submit"]'))
    }
  }

  async handleQuickContactSubmit(e) {
    e.preventDefault()

    const formData = new FormData(e.target)
    const data = Object.fromEntries(formData.entries())

    try {
      this.showButtonLoading(e.target.querySelector('button[type="submit"]'))

      const response = await fetch("../api/create_enquiry.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      })

      const result = await response.json()

      if (result.success) {
        this.showToast("Message sent successfully! We will get back to you soon.", "success")
        e.target.reset()
      } else {
        this.showToast(result.message || "Failed to send message", "error")
      }
    } catch (error) {
      console.error("Error sending message:", error)
      this.showToast("Failed to send message. Please try again.", "error")
    } finally {
      this.hideButtonLoading(e.target.querySelector('button[type="submit"]'))
    }
  }

  // Modal Methods
  openBookingModal() {
    const modal = document.getElementById("bookingModal")
    modal.classList.add("active")
    document.body.style.overflow = "hidden"
  }

  closeBookingModal() {
    const modal = document.getElementById("bookingModal")
    modal.classList.remove("active")
    document.body.style.overflow = ""
    this.resetFormSteps()
  }

  openEnquiryModal() {
    const modal = document.getElementById("enquiryModal")
    modal.classList.add("active")
    document.body.style.overflow = "hidden"
  }

  closeEnquiryModal() {
    const modal = document.getElementById("enquiryModal")
    modal.classList.remove("active")
    document.body.style.overflow = ""
  }

  openFeedbackModal() {
    const modal = document.getElementById("feedbackModal")
    modal.classList.add("active")
    document.body.style.overflow = "hidden"
  }

  closeFeedbackModal() {
    const modal = document.getElementById("feedbackModal")
    modal.classList.remove("active")
    document.body.style.overflow = ""
  }

  handleModalClicks(e) {
    // Close modal when clicking outside
    if (e.target.classList.contains("modal")) {
      e.target.classList.remove("active")
      document.body.style.overflow = ""
    }
  }

  handleKeydown(e) {
    // Close modal on Escape key
    if (e.key === "Escape") {
      const activeModal = document.querySelector(".modal.active")
      if (activeModal) {
        activeModal.classList.remove("active")
        document.body.style.overflow = ""
      }
    }
  }

  // Form Step Navigation
  nextStep() {
    if (this.currentStep < this.maxSteps) {
      if (this.validateCurrentStep()) {
        this.currentStep++
        this.updateFormSteps()
      }
    }
  }

  prevStep() {
    if (this.currentStep > 1) {
      this.currentStep--
      this.updateFormSteps()
    }
  }

  updateFormSteps() {
    // Hide all steps
    document.querySelectorAll(".form-step").forEach((step) => {
      step.classList.remove("active")
    })

    // Show current step
    document.querySelector(`[data-step="${this.currentStep}"]`).classList.add("active")

    // Update navigation buttons
    const prevBtn = document.getElementById("prevStep")
    const nextBtn = document.getElementById("nextStep")
    const submitBtn = document.getElementById("submitBooking")

    prevBtn.style.display = this.currentStep > 1 ? "block" : "none"
    nextBtn.style.display = this.currentStep < this.maxSteps ? "block" : "none"
    submitBtn.style.display = this.currentStep === this.maxSteps ? "block" : "none"
  }

  resetFormSteps() {
    this.currentStep = 1
    this.updateFormSteps()
  }

  validateCurrentStep() {
    const currentStepElement = document.querySelector(`[data-step="${this.currentStep}"]`)
    const requiredFields = currentStepElement.querySelectorAll("[required]")
    let isValid = true

    requiredFields.forEach((field) => {
      if (!field.value.trim()) {
        field.classList.add("error")
        isValid = false
      } else {
        field.classList.remove("error")
      }
    })

    if (!isValid) {
      this.showToast("Please fill in all required fields", "error")
    }

    return isValid
  }

  validateBookingForm() {
    const form = document.getElementById("bookingForm")
    const requiredFields = form.querySelectorAll("[required]")
    let isValid = true

    requiredFields.forEach((field) => {
      if (!field.value.trim()) {
        field.classList.add("error")
        isValid = false
      } else {
        field.classList.remove("error")
      }
    })

    // Validate email
    const emailField = form.querySelector('[type="email"]')
    if (emailField && emailField.value && !this.isValidEmail(emailField.value)) {
      emailField.classList.add("error")
      isValid = false
    }

    // Validate date
    const dateField = form.querySelector('[type="date"]')
    if (dateField && dateField.value) {
      const selectedDate = new Date(dateField.value)
      const today = new Date()
      today.setHours(0, 0, 0, 0)

      if (selectedDate < today) {
        dateField.classList.add("error")
        this.showToast("Please select a future date", "error")
        isValid = false
      }
    }

    return isValid
  }

  // Service Methods
  bookService(serviceName) {
    document.getElementById("service").value = serviceName
    this.openBookingModal()
  }

  filterServices(e) {
    const filterValue = e.target.dataset.filter
    const serviceCards = document.querySelectorAll(".service-card")

    // Update active filter button
    document.querySelectorAll(".filter-btn").forEach((btn) => {
      btn.classList.remove("active")
    })
    e.target.classList.add("active")

    // Filter service cards
    serviceCards.forEach((card) => {
      if (filterValue === "all" || card.dataset.category === filterValue) {
        card.style.display = "block"
        card.style.animation = "fadeInUp 0.5s ease"
      } else {
        card.style.display = "none"
      }
    })
  }

  // Utility Methods
  setMinDate() {
    const today = new Date().toISOString().split("T")[0]
    const dateInput = document.getElementById("appointmentDate")
    if (dateInput) {
      dateInput.setAttribute("min", today)
    }
  }

  showLoading(elementId) {
    const element = document.getElementById(elementId)
    if (element) {
      element.innerHTML = `
                <div class="loading-container">
                    <div class="spinner"></div>
                    <p>Loading...</p>
                </div>
            `
    }
  }

  showButtonLoading(button) {
    if (button) {
      button.disabled = true
      button.dataset.originalText = button.innerHTML
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'
    }
  }

  hideButtonLoading(button) {
    if (button) {
      button.disabled = false
      button.innerHTML = button.dataset.originalText || "Submit"
    }
  }

  hideLoadingOverlay() {
    const overlay = document.getElementById("loadingOverlay")
    setTimeout(() => {
      overlay.classList.remove("active")
    }, 1000)
  }

  showToast(message, type = "info") {
    const toast = document.getElementById("toast")
    const toastIcon = toast.querySelector(".toast-icon")
    const toastMessage = toast.querySelector(".toast-message")

    // Set icon based on type
    const icons = {
      success: "fas fa-check-circle",
      error: "fas fa-exclamation-circle",
      info: "fas fa-info-circle",
    }

    toastIcon.className = `toast-icon ${icons[type]}`
    toastMessage.textContent = message
    toast.className = `toast ${type}`

    // Show toast
    toast.classList.add("show")

    // Auto hide after 5 seconds
    setTimeout(() => {
      this.closeToast()
    }, 5000)
  }

  closeToast() {
    const toast = document.getElementById("toast")
    toast.classList.remove("show")
  }

  updateRatingDisplay() {
    // This method is called when rating changes
    // Additional visual feedback can be added here
  }

  resetRatingInput() {
    document.querySelectorAll(".rating-input input").forEach((input) => {
      input.checked = false
    })
  }

  initializeAnimations() {
    // Animate statistics on scroll
    const observerOptions = {
      threshold: 0.5,
      rootMargin: "0px 0px -100px 0px",
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          this.animateStats()
          observer.unobserve(entry.target)
        }
      })
    }, observerOptions)

    const heroStats = document.querySelector(".hero-stats")
    if (heroStats) {
      observer.observe(heroStats)
    }

    // Fade in animations
    const fadeElements = document.querySelectorAll(".fade-in")
    const fadeObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible")
          }
        })
      },
      { threshold: 0.1 },
    )

    fadeElements.forEach((el) => fadeObserver.observe(el))
  }

  animateStats() {
    const statNumbers = document.querySelectorAll(".stat-number")

    statNumbers.forEach((stat) => {
      const target = Number.parseInt(stat.dataset.count)
      const duration = 2000
      const step = target / (duration / 16)
      let current = 0

      const timer = setInterval(() => {
        current += step
        if (current >= target) {
          current = target
          clearInterval(timer)
        }
        stat.textContent = Math.floor(current)
      }, 16)
    })
  }

  getServiceIcon(category) {
    const icons = {
      crude_oil: "oil-can",
      refined_oil: "flask",
      kernel_oil: "seedling",
      consultation: "user-tie",
      bulk_supply: "truck",
    }
    return icons[category] || "cog"
  }

  generateStars(rating) {
    let stars = ""
    for (let i = 1; i <= 5; i++) {
      if (i <= rating) {
        stars += '<i class="fas fa-star"></i>'
      } else {
        stars += '<i class="far fa-star"></i>'
      }
    }
    return stars
  }

  escapeHtml(text) {
    const div = document.createElement("div")
    div.textContent = text
    return div.innerHTML
  }

  isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }
}

// Global functions for onclick handlers
function openBookingModal() {
  app.openBookingModal()
}

function closeBookingModal() {
  app.closeBookingModal()
}

function openEnquiryModal() {
  app.openEnquiryModal()
}

function closeEnquiryModal() {
  app.closeEnquiryModal()
}

function openFeedbackModal() {
  app.openFeedbackModal()
}

function closeFeedbackModal() {
  app.closeFeedbackModal()
}

function closeToast() {
  app.closeToast()
}

function bookService(serviceName) {
  app.bookService(serviceName)
}

// Initialize the application
const app = new NananomFarms()

// Service Worker Registration (optional)
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("/sw.js")
      .then((registration) => {
        console.log("SW registered: ", registration)
      })
      .catch((registrationError) => {
        console.log("SW registration failed: ", registrationError)
      })
  })
}
