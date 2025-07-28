// Global variables
let services = []

// DOM Content Loaded
document.addEventListener("DOMContentLoaded", () => {
  loadServices()
  initializeEventListeners()
  setMinDate()
})

// Initialize event listeners
function initializeEventListeners() {
  // Mobile menu toggle
  const hamburger = document.querySelector(".hamburger")
  const navMenu = document.querySelector(".nav-menu")

  if (hamburger) {
    hamburger.addEventListener("click", () => {
      navMenu.classList.toggle("active")
    })
  }

  // Form submissions
  document.getElementById("bookingForm").addEventListener("submit", handleBookingSubmit)
  document.getElementById("enquiryForm").addEventListener("submit", handleEnquirySubmit)
  document.getElementById("feedbackForm").addEventListener("submit", handleFeedbackSubmit)

  // Close modals when clicking outside
  window.addEventListener("click", (event) => {
    const modals = document.querySelectorAll(".modal")
    modals.forEach((modal) => {
      if (event.target === modal) {
        modal.style.display = "none"
      }
    })
  })

  // Smooth scrolling for navigation links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })
}

// Load services from backend
async function loadServices() {
  try {
    showLoading("servicesGrid")
    const response = await fetch("api/get_services.php")
    const data = await response.json()

    if (data.success) {
      services = data.services
      displayServices(data.services)
      populateServiceSelect(data.services)
    } else {
      showError("Failed to load services")
    }
  } catch (error) {
    console.error("Error loading services:", error)
    showError("Failed to load services")
  } finally {
    hideLoading("servicesGrid")
  }
}

// Display services in grid
function displayServices(services) {
  const servicesGrid = document.getElementById("servicesGrid")
  servicesGrid.innerHTML = ""

  services.forEach((service) => {
    const serviceCard = document.createElement("div")
    serviceCard.className = "service-card"
    serviceCard.innerHTML = `
            <h3>${service.name}</h3>
            <p>${service.description}</p>
            <div class="price">$${Number.parseFloat(service.price).toFixed(2)} / ${service.unit}</div>
            <button class="btn btn-primary" onclick="bookService('${service.name}')">Book Now</button>
        `
    servicesGrid.appendChild(serviceCard)
  })
}

// Populate service select dropdown
function populateServiceSelect(services) {
  const serviceSelect = document.getElementById("service")
  serviceSelect.innerHTML = '<option value="">Select a service</option>'

  services.forEach((service) => {
    const option = document.createElement("option")
    option.value = service.name
    option.textContent = `${service.name} - $${Number.parseFloat(service.price).toFixed(2)}/${service.unit}`
    serviceSelect.appendChild(option)
  })
}

// Modal functions
function openBookingModal() {
  document.getElementById("bookingModal").style.display = "block"
}

function closeBookingModal() {
  document.getElementById("bookingModal").style.display = "none"
  document.getElementById("bookingForm").reset()
}

function openEnquiryModal() {
  document.getElementById("enquiryModal").style.display = "block"
}

function closeEnquiryModal() {
  document.getElementById("enquiryModal").style.display = "none"
  document.getElementById("enquiryForm").reset()
}

function openFeedbackModal() {
  document.getElementById("feedbackModal").style.display = "block"
}

function closeFeedbackModal() {
  document.getElementById("feedbackModal").style.display = "none"
  document.getElementById("feedbackForm").reset()
}

// Book specific service
function bookService(serviceName) {
  document.getElementById("service").value = serviceName
  openBookingModal()
}

// Set minimum date to today
function setMinDate() {
  const today = new Date().toISOString().split("T")[0]
  document.getElementById("appointmentDate").setAttribute("min", today)
}

// Handle booking form submission
async function handleBookingSubmit(e) {
  e.preventDefault()

  const formData = new FormData(e.target)
  const data = Object.fromEntries(formData.entries())

  try {
    showLoading("bookingForm")
    const response = await fetch("api/create_booking.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })

    const result = await response.json()

    if (result.success) {
      showSuccess("Booking submitted successfully! We will contact you soon.")
      closeBookingModal()
    } else {
      showError(result.message || "Failed to submit booking")
    }
  } catch (error) {
    console.error("Error submitting booking:", error)
    showError("Failed to submit booking. Please try again.")
  } finally {
    hideLoading("bookingForm")
  }
}

// Handle enquiry form submission
async function handleEnquirySubmit(e) {
  e.preventDefault()

  const formData = new FormData(e.target)
  const data = Object.fromEntries(formData.entries())

  try {
    showLoading("enquiryForm")
    const response = await fetch("api/create_enquiry.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })

    const result = await response.json()

    if (result.success) {
      showSuccess("Enquiry submitted successfully! We will respond within 24 hours.")
      closeEnquiryModal()
    } else {
      showError(result.message || "Failed to submit enquiry")
    }
  } catch (error) {
    console.error("Error submitting enquiry:", error)
    showError("Failed to submit enquiry. Please try again.")
  } finally {
    hideLoading("enquiryForm")
  }
}

// Handle feedback form submission
async function handleFeedbackSubmit(e) {
  e.preventDefault()

  const formData = new FormData(e.target)
  const data = Object.fromEntries(formData.entries())

  try {
    showLoading("feedbackForm")
    const response = await fetch("api/create_feedback.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })

    const result = await response.json()

    if (result.success) {
      showSuccess("Thank you for your feedback!")
      closeFeedbackModal()
    } else {
      showError(result.message || "Failed to submit feedback")
    }
  } catch (error) {
    console.error("Error submitting feedback:", error)
    showError("Failed to submit feedback. Please try again.")
  } finally {
    hideLoading("feedbackForm")
  }
}

// Utility functions
function showLoading(elementId) {
  const element = document.getElementById(elementId)
  if (element) {
    const loading = document.createElement("div")
    loading.className = "loading"
    loading.innerHTML = '<div class="spinner"></div><p>Loading...</p>'
    element.appendChild(loading)
    loading.style.display = "block"
  }
}

function hideLoading(elementId) {
  const element = document.getElementById(elementId)
  if (element) {
    const loading = element.querySelector(".loading")
    if (loading) {
      loading.remove()
    }
  }
}

function showSuccess(message) {
  showAlert(message, "success")
}

function showError(message) {
  showAlert(message, "error")
}

function showAlert(message, type) {
  // Remove existing alerts
  const existingAlerts = document.querySelectorAll(".alert")
  existingAlerts.forEach((alert) => alert.remove())

  // Create new alert
  const alert = document.createElement("div")
  alert.className = `alert alert-${type}`
  alert.textContent = message
  alert.style.display = "block"

  // Insert at top of body
  document.body.insertBefore(alert, document.body.firstChild)

  // Auto remove after 5 seconds
  setTimeout(() => {
    alert.remove()
  }, 5000)
}

// Form validation
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

function validatePhone(phone) {
  const re = /^[+]?[1-9][\d]{0,15}$/
  return re.test(phone)
}

// Add real-time validation
document.addEventListener("input", (e) => {
  if (e.target.type === "email") {
    if (e.target.value && !validateEmail(e.target.value)) {
      e.target.setCustomValidity("Please enter a valid email address")
    } else {
      e.target.setCustomValidity("")
    }
  }

  if (e.target.type === "tel") {
    if (e.target.value && !validatePhone(e.target.value)) {
      e.target.setCustomValidity("Please enter a valid phone number")
    } else {
      e.target.setCustomValidity("")
    }
  }
})
