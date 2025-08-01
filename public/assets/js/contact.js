// Contact Page JavaScript
class ContactPage {
  constructor() {
    this.chatOpen = false
    this.chatMessages = []
    this.init()
  }
getHostPath() {
  const url = new URL(window.location.href)
  return url.origin + '/' + url.pathname.split('/')[1]
}
  init() {
    this.setupEventListeners()
    this.initializeFAQs()
    this.initializeAnimations()
    this.setMinDates()
    this.initializeChat()
    this.getHostPath()
  }

  setupEventListeners() {
    // Form submissions
    document.getElementById("generalContactForm").addEventListener("submit", this.handleGeneralContact.bind(this))
    document.getElementById("quoteRequestForm").addEventListener("submit", this.handleQuoteRequest.bind(this))
    document.getElementById("visitForm").addEventListener("submit", this.handleVisitScheduling.bind(this))

    // Modal controls
    document.addEventListener("click", this.handleModalClicks.bind(this))
    document.addEventListener("keydown", this.handleKeydown.bind(this))

    // Chat functionality
    document.getElementById("chatMessageInput").addEventListener("keypress", this.handleChatKeypress.bind(this))
  }

  // Form Handlers
  async handleGeneralContact(e) {
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
        this.showToast("Message sent successfully! We'll respond within 24 hours.", "success")
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

  async handleQuoteRequest(e) {
    e.preventDefault()

    const formData = new FormData(e.target)
    const data = Object.fromEntries(formData.entries())

    // Format the message for quote request
    data.subject = "Quote Request"
    data.message = `
            Quote Request Details:
            - Product Type: ${data.product_type}
            - Quantity: ${data.quantity} tons
            - Delivery Location: ${data.delivery_location || "Not specified"}
            - Timeline: ${data.timeline || "Not specified"}
            - Special Requirements: ${data.special_requirements || "None"}
        `

    try {
      this.showButtonLoading(e.target.querySelector('button[type="submit"]'))
    const mainPath = this.getHostPath()
const response = await fetch(`${mainPath}/api/create_enquiry.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      })

      const result = await response.json()

      if (result.success) {
        this.showToast(
          "Quote request submitted successfully! We'll send you a detailed quote within 24 hours.",
          "success",
        )
        e.target.reset()
      } else {
        this.showToast(result.message || "Failed to submit quote request", "error")
      }
    } catch (error) {
      console.error("Error submitting quote request:", error)
      this.showToast("Failed to submit quote request. Please try again.", "error")
    } finally {
      this.hideButtonLoading(e.target.querySelector('button[type="submit"]'))
    }
  }

  async handleVisitScheduling(e) {
    e.preventDefault()

    const formData = new FormData(e.target)
    const data = Object.fromEntries(formData.entries())

    // Format as booking request
    data.service = `Facility Visit - ${data.location}`
    data.customer_name = data.name
    data.appointment_date = data.visit_date
    data.booking_time = data.visit_time + ":00"
    data.notes = `Purpose: ${data.purpose}\nAdditional Notes: ${data.notes || "None"}`

    try {
      this.showButtonLoading(e.target.querySelector('button[type="submit"]'))
    const mainPath = this.getHostPath()
    const response = await fetch(`${mainPath}/api/create_booking.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      })

      const result = await response.json()

      if (result.success) {
        this.showToast("Visit scheduled successfully! We'll confirm the appointment via email.", "success")
        this.closeVisitModal()
        e.target.reset()
      } else {
        this.showToast(result.message || "Failed to schedule visit", "error")
      }
    } catch (error) {
      console.error("Error scheduling visit:", error)
      this.showToast("Failed to schedule visit. Please try again.", "error")
    } finally {
      this.hideButtonLoading(e.target.querySelector('button[type="submit"]'))
    }
  }

  // FAQ Functionality
  initializeFAQs() {
    const faqQuestions = document.querySelectorAll(".faq-question")
    faqQuestions.forEach((question) => {
      question.addEventListener("click", () => this.toggleFAQ(question))
    })
  }

  toggleFAQ(questionElement) {
    const faqItem = questionElement.closest(".faq-item")
    const isActive = faqItem.classList.contains("active")

    // Close all other FAQs in the same category
    const category = faqItem.closest(".faq-category")
    category.querySelectorAll(".faq-item").forEach((item) => {
      item.classList.remove("active")
    })

    // Toggle current FAQ
    if (!isActive) {
      faqItem.classList.add("active")
    }
  }

  // Location & Map Functions
  getDirections(address) {
    const encodedAddress = encodeURIComponent(address)
    const mapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${encodedAddress}`
    window.open(mapsUrl, "_blank")
  }

  scheduleVisit(location) {
    document.getElementById("visitLocation").value = location
    document.getElementById("visitModal").classList.add("active")
    document.body.style.overflow = "hidden"
  }

  closeVisitModal() {
    document.getElementById("visitModal").classList.remove("active")
    document.body.style.overflow = ""
  }

  loadMap() {
    // Placeholder for map loading functionality
    // In a real implementation, you would integrate with Google Maps API
    this.showToast("Map functionality would be integrated with Google Maps API", "info")
  }

  // Chat Functionality
  initializeChat() {
    // Add initial welcome message
    this.addChatMessage("Hello! Welcome to Nananom Farms. How can I help you today?", "agent")

    // Simulate agent responses for demo
    this.chatResponses = [
      "Thank you for your interest in our palm oil products. What specific information are you looking for?",
      "I'd be happy to help you with that. Let me connect you with one of our specialists.",
      "For detailed pricing information, I recommend requesting a quote through our contact form.",
      "Our team will get back to you within 24 hours with a comprehensive response.",
      "Is there anything else I can help you with today?",
    ]
    this.responseIndex = 0
  }

  toggleChat() {
    const chatWindow = document.getElementById("chatWindow")
    const chatBadge = document.querySelector(".chat-badge")

    this.chatOpen = !this.chatOpen

    if (this.chatOpen) {
      chatWindow.classList.add("active")
      chatBadge.style.display = "none"
    } else {
      chatWindow.classList.remove("active")
    }
  }

  handleChatKeypress(e) {
    if (e.key === "Enter") {
      this.sendChatMessage()
    }
  }

  sendChatMessage() {
    const input = document.getElementById("chatMessageInput")
    const message = input.value.trim()

    if (message) {
      this.addChatMessage(message, "user")
      input.value = ""

      // Simulate agent response after a delay
      setTimeout(() => {
        const response = this.chatResponses[this.responseIndex % this.chatResponses.length]
        this.addChatMessage(response, "agent")
        this.responseIndex++
      }, 1000)
    }
  }

  addChatMessage(message, sender) {
    const chatMessages = document.getElementById("chatMessages")
    const messageElement = document.createElement("div")
    messageElement.className = `message ${sender}-message`

    const now = new Date()
    const timeString = now.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })

    messageElement.innerHTML = `
            <div class="message-content">
                <p>${this.escapeHtml(message)}</p>
                <span class="message-time">${timeString}</span>
            </div>
        `

    chatMessages.appendChild(messageElement)
    chatMessages.scrollTop = chatMessages.scrollHeight

    this.chatMessages.push({ message, sender, time: now })
  }

  openLiveChat() {
    if (!this.chatOpen) {
      this.toggleChat()
    }
  }

  // Animation and UI
  initializeAnimations() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px",
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible")
        }
      })
    }, observerOptions)

    // Observe elements for animation
    document.querySelectorAll(".fade-in-up, .scale-in").forEach((el) => {
      observer.observe(el)
    })
  }

  setMinDates() {
    const today = new Date()
    const tomorrow = new Date(today)
    tomorrow.setDate(tomorrow.getDate() + 1)

    const minDate = tomorrow.toISOString().split("T")[0]
    const visitDateInput = document.getElementById("visitDate")

    if (visitDateInput) {
      visitDateInput.setAttribute("min", minDate)
    }
  }

  // Modal Handling
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

      // Close chat if open
      if (this.chatOpen) {
        this.toggleChat()
      }
    }
  }

  // Utility Methods
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

  showToast(message, type = "info") {
    const toast = document.getElementById("toast")
    const toastIcon = toast.querySelector(".toast-icon")
    const toastMessage = toast.querySelector(".toast-message")

    const icons = {
      success: "fas fa-check-circle",
      error: "fas fa-exclamation-circle",
      info: "fas fa-info-circle",
    }

    toastIcon.className = `toast-icon ${icons[type]}`
    toastMessage.textContent = message
    toast.className = `toast ${type}`

    toast.classList.add("show")

    setTimeout(() => {
      this.closeToast()
    }, 5000)
  }

  closeToast() {
    const toast = document.getElementById("toast")
    toast.classList.remove("show")
  }

  escapeHtml(text) {
    const div = document.createElement("div")
    div.textContent = text
    return div.innerHTML
  }
}

// Global functions for onclick handlers
function toggleFAQ(questionElement) {
  contactPage.toggleFAQ(questionElement)
}

function getDirections(address) {
  contactPage.getDirections(address)
}

function scheduleVisit(location) {
  contactPage.scheduleVisit(location)
}

function closeVisitModal() {
  contactPage.closeVisitModal()
}

function loadMap() {
  contactPage.loadMap()
}

function toggleChat() {
  contactPage.toggleChat()
}

function sendChatMessage() {
  contactPage.sendChatMessage()
}

function handleChatKeypress(event) {
  contactPage.handleChatKeypress(event)
}

function openLiveChat() {
  contactPage.openLiveChat()
}

function closeToast() {
  contactPage.closeToast()
}

// Initialize contact page
const contactPage = new ContactPage()

// Extend main app functionality for contact page
const app = window.app || {} // Declare app variable if it's undefined
if (typeof app !== "undefined") {
  // Override booking modal to work with contact page
  app.openBookingModal = () => {
    document.getElementById("visitModal").classList.add("active")
    document.body.style.overflow = "hidden"
  }
}

