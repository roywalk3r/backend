const toastManager = {
  error: (message) => {
    console.error(message)
  },
  success: (message) => {
    console.log(message)
  },
}

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
    const hamburger = document.getElementById("hamburger")
    const navMenu = document.getElementById("navMenu")

    if (hamburger) {
      hamburger.addEventListener("click", () => this.toggleMobileMenu())
    }

    document.addEventListener("click", (e) => this.closeMobileMenuOnOutsideClick(e))
    window.addEventListener("scroll", () => this.handleScroll())

    // Smooth scrolling for navigation links
    document.querySelectorAll(".nav-link").forEach((link) => {
      link.addEventListener("click", (e) => this.smoothScroll(e))
    })

    // Form submissions
    const bookingForm = document.getElementById("bookingForm")
    const enquiryForm = document.getElementById("enquiryForm")
    const feedbackForm = document.getElementById("feedbackForm")
    const quickContactForm = document.getElementById("quickContactForm")

    if (bookingForm) {
      bookingForm.addEventListener("submit", (e) => this.handleBookingSubmit(e))
    }
    if (enquiryForm) {
      enquiryForm.addEventListener("submit", (e) => this.handleEnquirySubmit(e))
    }
    if (feedbackForm) {
      feedbackForm.addEventListener("submit", (e) => this.handleFeedbackSubmit(e))
    }
    if (quickContactForm) {
      quickContactForm.addEventListener("submit", (e) => this.handleQuickContactSubmit(e))
    }

    // Modal controls
    document.addEventListener("click", (e) => this.handleModalClicks(e))
    document.addEventListener("keydown", (e) => this.handleKeydown(e))

    // Form navigation
    const nextStep = document.getElementById("nextStep")
    const prevStep = document.getElementById("prevStep")

    if (nextStep) {
      nextStep.addEventListener("click", () => this.nextStep())
    }
    if (prevStep) {
      prevStep.addEventListener("click", () => this.prevStep())
    }

    // Service filters
    document.querySelectorAll(".filter-btn").forEach((btn) => {
      btn.addEventListener("click", (e) => this.filterServices(e))
    })

    // Rating inputs
    document.querySelectorAll(".rating-input input").forEach((input) => {
      input.addEventListener("change", () => this.updateRatingDisplay())
    })
  }

  // Navigation Methods
  toggleMobileMenu() {
    const hamburger = document.getElementById("hamburger")
    const navMenu = document.getElementById("navMenu")

    if (hamburger && navMenu) {
      hamburger.classList.toggle("active")
      navMenu.classList.toggle("active")
    }
  }

  closeMobileMenuOnOutsideClick(e) {
    const hamburger = document.getElementById("hamburger")
    const navMenu = document.getElementById("navMenu")

    if (hamburger && navMenu && !hamburger.contains(e.target) && !navMenu.contains(e.target)) {
      hamburger.classList.remove("active")
      navMenu.classList.remove("active")
    }
  }

  handleScroll() {
    const navbar = document.getElementById("navbar")
    if (navbar) {
      const scrolled = window.scrollY > 50
      navbar.classList.toggle("scrolled", scrolled)
    }

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
    const hamburger = document.getElementById("hamburger")
    const navMenu = document.getElementById("navMenu")
    if (hamburger && navMenu) {
      hamburger.classList.remove("active")
      navMenu.classList.remove("active")
    }
  }

  // Data Loading Methods
  async loadServices() {
    try {
      this.showLoading("servicesGrid")
      const response = await fetch("/api/get_services.php")

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      console.log(data, "services")
      if (data.success) {
        this.services = data.services
        this.populateServiceSelect(data.services)
      } else {
        toastManager.error("Failed to load services")
        this.displayServiceError()
      }
    } catch (error) {
      console.error("Error loading services:", error)
      toastManager.error("Failed to load services")
      this.displayServiceError()
    }
  }

  displayServiceError() {
    const servicesGrid = document.getElementById("servicesGrid")
    if (servicesGrid) {
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
  }

  populateServiceSelect(services) {
    const serviceSelects = [document.getElementById("service"), document.getElementById("feedbackService")]

    serviceSelects.forEach((serviceSelect) => {
      if (serviceSelect) {
        const isBookingForm = serviceSelect.id === "service"
        serviceSelect.innerHTML = isBookingForm
          ? '<option value="">Choose a service...</option>'
          : '<option value="">Select service...</option>'

        services.forEach((service) => {
          const option = document.createElement("option")
          option.value = service.id
          option.dataset.serviceName = service.name
          option.textContent = `${service.name} - $${Number.parseFloat(service.price).toFixed(2)}/${service.unit}`
          serviceSelect.appendChild(option)
        })
      }
    })
  }

  async loadTestimonials() {
    try {
      const response = await fetch("/api/get_feedback.php")

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success && data.feedback.length > 0) {
        this.displayTestimonials(data.feedback.slice(0, 6))
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
    if (!testimonialsGrid) return

    testimonialsGrid.innerHTML = testimonials
      .map(
        (testimonial) => `
          <div class="testimonial-card">
            <div class="testimonial-content">
              ${this.escapeHtml(testimonial.comment || testimonial.message)}
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
    if (!testimonialsGrid) return

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

    const bookingData = {
      customer_name: data.customer_name,
      customer_email: data.customer_email,
      customer_phone: data.customer_phone,
      service_id: Number.parseInt(data.service_id),
      booking_date: data.booking_date,
      booking_time: data.booking_time,
      notes: data.notes || "",
    }

    try {
      this.showButtonLoading(e.target.querySelector('button[type="submit"]'))

      const response = await fetch("/api/create_booking.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(bookingData),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()

      if (result.success) {
        toastManager.success("Booking submitted successfully! We will contact you soon.")
        this.closeBookingModal()
        e.target.reset()
        this.resetFormSteps()
      } else {
        toastManager.error(result.message || "Failed to submit booking")
      }
    } catch (error) {
      console.error("Error submitting booking:", error)
      toastManager.error("Failed to submit booking. Please try again.")
    } finally {
      this.hideButtonLoading(e.target.querySelector('button[type="submit"]'))
    }
  }

  async handleEnquirySubmit(e) {
    e.preventDefault()

    if (!this.validateEnquiryForm(e.target)) {
      return
    }

    const formData = new FormData(e.target)
    const data = Object.fromEntries(formData.entries())

    const enquiryData = {
      name: data.name,
      email: data.email,
      phone: data.phone || "",
      subject: data.subject,
      message: data.message,
    }

    try {
      this.showButtonLoading(e.target.querySelector('button[type="submit"]'))

      const response = await fetch("/api/create_enquiry.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(enquiryData),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()

      if (result.success) {
        toastManager.success("Enquiry submitted successfully! We will respond within 24 hours.")
        this.closeEnquiryModal()
        e.target.reset()
        this.clearFormErrors(e.target)
      } else {
        toastManager.error(result.message || "Failed to submit enquiry")
      }
    } catch (error) {
      console.error("Error submitting enquiry:", error)
      toastManager.error("Failed to submit enquiry. Please try again.")
    } finally {
      this.hideButtonLoading(e.target.querySelector('button[type="submit"]'))
    }
  }

  async handleFeedbackSubmit(e) {
    e.preventDefault()

    const formData = new FormData(e.target)
    const data = Object.fromEntries(formData.entries())

    const feedbackData = {
      customer_name: data.customer_name,
      customer_email: data.customer_email,
      service_id: Number.parseInt(data.service_id),
      rating: Number.parseInt(data.rating),
      comment: data.comment,
    }

    try {
      this.showButtonLoading(e.target.querySelector('button[type="submit"]'))

      const response = await fetch("/api/create_feedback.php", {
  method: "POST", 
  headers: {
    "Content-Type": "application/json",
  },
  body: JSON.stringify(feedbackData),
});


      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()

      if (result.success) {
        toastManager.success("Thank you for your feedback!")
        this.closeFeedbackModal()
        e.target.reset()
        this.resetRatingInput()
      } else {
        toastManager.error(result.message || "Failed to submit feedback")
      }
    } catch (error) {
      console.error("Error submitting feedback:", error)
      toastManager.error("Failed to submit feedback. Please try again.")
    } finally {
      this.hideButtonLoading(e.target.querySelector('button[type="submit"]'))
    }
  }

  async handleQuickContactSubmit(e) {
    e.preventDefault()

    const formData = new FormData(e.target)
    const data = Object.fromEntries(formData.entries())

    const contactData = {
      name: data.name,
      email: data.email,
      phone: data.phone || "",
      subject: data.subject,
      message: data.message,
    }

    try {
      this.showButtonLoading(e.target.querySelector('button[type="submit"]'))

      const response = await fetch("/api/create_enquiry.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(contactData),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()

      if (result.success) {
        toastManager.success("Message sent successfully! We will get back to you soon.")
        e.target.reset()
      } else {
        toastManager.error(result.message || "Failed to send message")
      }
    } catch (error) {
      console.error("Error sending message:", error)
      toastManager.error("Failed to send message. Please try again.")
    } finally {
      this.hideButtonLoading(e.target.querySelector('button[type="submit"]'))
    }
  }

  // Form Validation Methods
  validateEnquiryForm(form) {
    const requiredFields = form.querySelectorAll("[required]")
    let isValid = true

    // Clear previous errors
    this.clearFormErrors(form)

    requiredFields.forEach((field) => {
      if (!field.value.trim()) {
        this.showFieldError(field, "This field is required")
        isValid = false
      }
    })

    // Validate email
    const emailField = form.querySelector('[name="email"]')
    if (emailField && emailField.value && !this.isValidEmail(emailField.value)) {
      this.showFieldError(emailField, "Please enter a valid email address")
      isValid = false
    }

    if (!isValid) {
      toastManager.error("Please fill in all required fields correctly")
    }

    return isValid
  }

  validateBookingForm() {
    const form = document.getElementById("bookingForm")
    if (!form) return true

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
    const emailField = form.querySelector('[name="customer_email"]')
    if (emailField && emailField.value && !this.isValidEmail(emailField.value)) {
      emailField.classList.add("error")
      isValid = false
    }

    // Validate date
    const dateField = form.querySelector('[name="booking_date"]')
    if (dateField && dateField.value) {
      const selectedDate = new Date(dateField.value)
      const today = new Date()
      today.setHours(0, 0, 0, 0)

      if (selectedDate < today) {
        dateField.classList.add("error")
        toastManager.error("Please select a future date")
        isValid = false
      }
    }

    return isValid
  }

  showFieldError(field, message) {
    field.classList.add("error")
    const errorSpan = field.parentElement.querySelector(".error-message")
    if (errorSpan) {
      errorSpan.textContent = message
      errorSpan.style.display = "block"
    }
  }

  clearFormErrors(form) {
    const errorFields = form.querySelectorAll(".error")
    const errorMessages = form.querySelectorAll(".error-message")

    errorFields.forEach((field) => field.classList.remove("error"))
    errorMessages.forEach((msg) => {
      msg.textContent = ""
      msg.style.display = "none"
    })
  }

  // Modal Methods
  openBookingModal() {
    const modal = document.getElementById("bookingModal")
    if (modal) {
      modal.classList.add("active")
      document.body.style.overflow = "hidden"
    }
  }

  closeBookingModal() {
    const modal = document.getElementById("bookingModal")
    if (modal) {
      modal.classList.remove("active")
      document.body.style.overflow = ""
      this.resetFormSteps()
    }
  }

  openEnquiryModal() {
    const modal = document.getElementById("enquiryModal")
    if (modal) {
      modal.classList.add("active")
      document.body.style.overflow = "hidden"
    }
  }

  closeEnquiryModal() {
    const modal = document.getElementById("enquiryModal")
    if (modal) {
      modal.classList.remove("active")
      document.body.style.overflow = ""
    }
  }

  openFeedbackModal() {
    const modal = document.getElementById("feedbackModal")
    if (modal) {
      modal.classList.add("active")
      document.body.style.overflow = "hidden"
    }
  }

  closeFeedbackModal() {
    const modal = document.getElementById("feedbackModal")
    if (modal) {
      modal.classList.remove("active")
      document.body.style.overflow = ""
    }
  }

  handleModalClicks(e) {
    if (e.target.classList.contains("modal")) {
      e.target.classList.remove("active")
      document.body.style.overflow = ""
    }
  }

  handleKeydown(e) {
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
    document.querySelectorAll(".form-step").forEach((step) => {
      step.classList.remove("active")
    })

    const currentStepElement = document.querySelector(`[data-step="${this.currentStep}"]`)
    if (currentStepElement) {
      currentStepElement.classList.add("active")
    }

    const prevBtn = document.getElementById("prevStep")
    const nextBtn = document.getElementById("nextStep")
    const submitBtn = document.getElementById("submitBooking")

    if (prevBtn) {
      prevBtn.style.display = this.currentStep > 1 ? "block" : "none"
    }
    if (nextBtn) {
      nextBtn.style.display = this.currentStep < this.maxSteps ? "block" : "none"
    }
    if (submitBtn) {
      submitBtn.style.display = this.currentStep === this.maxSteps ? "block" : "none"
    }
  }

  resetFormSteps() {
    this.currentStep = 1
    this.updateFormSteps()
  }

  validateCurrentStep() {
    const currentStepElement = document.querySelector(`[data-step="${this.currentStep}"]`)
    if (!currentStepElement) return true

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
      toastManager.error("Please fill in all required fields")
    }

    return isValid
  }

  // Service Methods
  bookService(serviceId, serviceName) {
    const serviceSelect = document.getElementById("service")
    if (serviceSelect) {
      serviceSelect.value = serviceId
    }
    this.openBookingModal()
  }

  filterServices(e) {
    const filterValue = e.target.dataset.filter
    const serviceCards = document.querySelectorAll(".service-card")

    document.querySelectorAll(".filter-btn").forEach((btn) => {
      btn.classList.remove("active")
    })
    e.target.classList.add("active")

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
    const dateInput = document.getElementById("bookingDate")
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
    if (overlay) {
      setTimeout(() => {
        overlay.classList.remove("active")
      }, 1000)
    }
  }

  updateRatingDisplay() {
    // Additional visual feedback can be added here
  }

  resetRatingInput() {
    document.querySelectorAll(".rating-input input").forEach((input) => {
      input.checked = false
    })
  }

  initializeAnimations() {
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

function bookService(serviceId, serviceName) {
  app.bookService(serviceId, serviceName)
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
