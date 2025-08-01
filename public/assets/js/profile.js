// Profile JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Initialize profile page
  initializeProfile()
  initializeTabs()
  initializeForms()
  loadProfileData()

})
function getHostPath() {
    const url = new URL(window.location.href)
    return url.origin + '/' + url.pathname.split('/')[1]
  }
  
function initializeProfile() {
  // Check authentication status
  checkAuthStatus()

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



function initializeTabs() {
  const tabLinks = document.querySelectorAll(".profile-nav-link")
  const tabContents = document.querySelectorAll(".tab-content")

  tabLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault()

      const targetTab = this.getAttribute("data-tab")

      // Remove active class from all tabs and contents
      tabLinks.forEach((l) => l.classList.remove("active"))
      tabContents.forEach((c) => c.classList.remove("active"))

      // Add active class to clicked tab and corresponding content
      this.classList.add("active")
      const targetContent = document.getElementById(targetTab)
      if (targetContent) {
        targetContent.classList.add("active")
      }

      // Load tab-specific data
      loadTabData(targetTab)
    })
  })
}

function initializeForms() {
  // Profile form
  const profileForm = document.getElementById("profileForm")
  if (profileForm) {
    profileForm.addEventListener("submit", handleProfileUpdate)

    // Add real-time validation
    const inputs = profileForm.querySelectorAll("input")
    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        validateField(this)
      })

      input.addEventListener("input", function () {
        clearFieldError(this)
      })
    })
  }

  // Password form
  const passwordForm = document.getElementById("passwordForm")
  if (passwordForm) {
    passwordForm.addEventListener("submit", handlePasswordUpdate)

    // Password strength checker
    const newPasswordInput = document.getElementById("newPassword")
    if (newPasswordInput) {
      newPasswordInput.addEventListener("input", function () {
        checkPasswordStrength(this.value)
      })
    }

    // Confirm password validation
    const confirmPasswordInput = document.getElementById("confirmNewPassword")
    if (confirmPasswordInput) {
      confirmPasswordInput.addEventListener("input", () => {
        validatePasswordMatch()
      })
    }
  }

  // Initialize filters
  const bookingFilter = document.getElementById("bookingStatusFilter")
  if (bookingFilter) {
    bookingFilter.addEventListener("change", function () {
      filterBookings(this.value)
    })
  }

  const enquiryFilter = document.getElementById("enquiryStatusFilter")
  if (enquiryFilter) {
    enquiryFilter.addEventListener("change", function () {
      filterEnquiries(this.value)
    })
  }
}

function loadProfileData() {
  showLoading("Loading profile...")
  const location = getHostPath()
  fetch(`${location}/api/get_customer_profile.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        populateProfileData(data.user)
        hideLoading()
      } else {
        showAlert("Failed to load profile data: " + (data.message || "Unknown error"), "error")
        hideLoading()
      }
    })
    .catch((error) => {
      console.error("Profile load error:", error)
      showAlert("Failed to load profile data. Please refresh the page.", "error")
      hideLoading()
    })
}

function populateProfileData(user) {
  // Update header information
  const profileName = document.getElementById("profileName")
  const profileEmail = document.getElementById("profileEmail")
  const userName = document.getElementById("userName")

  if (profileName) profileName.textContent = `${user.first_name} ${user.last_name}`
  if (profileEmail) profileEmail.textContent = user.email
  if (userName) userName.textContent = user.first_name

  // Update form fields
  const firstName = document.getElementById("firstName")
  const lastName = document.getElementById("lastName")
  const email = document.getElementById("email")
  const phone = document.getElementById("phone")

  if (firstName) firstName.value = user.first_name || ""
  if (lastName) lastName.value = user.last_name || ""
  if (email) email.value = user.email || ""
  if (phone) phone.value = user.phone || ""
}

function loadTabData(tab) {
  switch (tab) {
    case "bookings":
      loadBookings()
      break
    case "enquiries":
      loadEnquiries()
      break
    case "personal":
      // Personal tab is already loaded
      break
    case "security":
      // Security tab doesn't need data loading
      break
  }
}

function loadBookings() {
  const container = document.getElementById("bookingsContainer")
  if (!container) return
    const location = getHostPath()

  container.innerHTML =
    '<div class="loading"><i class="fas fa-spinner fa-spin"></i><p>Loading your bookings...</p></div>'

  fetch(`${location}/api/get_customer_bookings.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayBookings(data.bookings)
        updateStats("bookings", data.bookings.length)
      } else {
        container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No bookings found</h3>
                        <p>You haven't made any bookings yet. <a href="booking.php">Browse our services</a> to get started.</p>
                    </div>
                `
      }
    })
    .catch((error) => {
      console.error("Bookings load error:", error)
      container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error loading bookings</h3>
                    <p>Please try again later or contact support if the problem persists.</p>
                </div>
            `
    })
}

function displayBookings(bookings) {
  const container = document.getElementById("bookingsContainer")
  if (!container) return

  if (bookings.length === 0) {
    container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No bookings found</h3>
                <p>You haven't made any bookings yet. <a href="booking.php">Browse our services</a> to get started.</p>
            </div>
        `
    return
  }

  const bookingsHTML = bookings
    .map(
      (booking) => `
        <div class="booking-item" data-status="${booking.status.toLowerCase()}">
            <div class="item-header">
                <div class="item-title">${booking.service_name || "Service"}</div>
                <div class="item-status status-${booking.status.toLowerCase()}">${booking.status}</div>
            </div>
            <div class="item-details">
                <div class="detail-item">
                    <i class="fas fa-calendar"></i>
                    <span>${formatDate(booking.booking_date)}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <span>${booking.booking_time}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-users"></i>
                    <span>${booking.number_of_people} people</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span>$${Number.parseFloat(booking.total_cost).toFixed(2)}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Booked: ${formatDate(booking.created_at)}</span>
                </div>
            </div>
            ${
              booking.notes
                ? `
                <div class="item-message">
                    <strong>Notes:</strong> ${booking.notes}
                </div>
            `
                : ""
            }
        </div>
    `,
    )
    .join("")

  container.innerHTML = bookingsHTML
}

function loadEnquiries() {
  const container = document.getElementById("enquiriesContainer")
  if (!container) return
    const location = getHostPath()

  container.innerHTML =
    '<div class="loading"><i class="fas fa-spinner fa-spin"></i><p>Loading your enquiries...</p></div>'

  fetch(`${location}/api/get_customer_enquiries.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayEnquiries(data.enquiries)
        updateStats("enquiries", data.enquiries.length)
      } else {
        container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-envelope-open"></i>
                        <h3>No enquiries found</h3>
                        <p>You haven't submitted any enquiries yet. <a href="public/contact.php">Contact us</a> if you have any questions.</p>
                    </div>
                `
      }
    })
    .catch((error) => {
      console.error("Enquiries load error:", error)
      container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error loading enquiries</h3>
                    <p>Please try again later or contact support if the problem persists.</p>
                </div>
            `
    })
}

function displayEnquiries(enquiries) {
  const container = document.getElementById("enquiriesContainer")
  if (!container) return

  if (enquiries.length === 0) {
    container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-envelope-open"></i>
                <h3>No enquiries found</h3>
                <p>You haven't submitted any enquiries yet. <a href="public/contact.php">Contact us</a> if you have any questions.</p>
            </div>
        `
    return
  }

  const enquiriesHTML = enquiries
    .map(
      (enquiry) => `
        <div class="enquiry-item" data-status="${enquiry.status.toLowerCase()}">
            <div class="item-header">
                <div class="item-title">${enquiry.subject}</div>
                <div class="item-status status-${enquiry.status.toLowerCase()}">${enquiry.status}</div>
            </div>
            <div class="item-details">
                <div class="detail-item">
                    <i class="fas fa-calendar"></i>
                    <span>${formatDate(enquiry.created_at)}</span>
                </div>
                ${
                  enquiry.service_name
                    ? `
                    <div class="detail-item">
                        <i class="fas fa-cog"></i>
                        <span>${enquiry.service_name}</span>
                    </div>
                `
                    : ""
                }
            </div>
            <div class="item-message">
                <strong>Your Message:</strong> ${enquiry.message}
            </div>
            ${
              enquiry.response
                ? `
                <div class="item-response">
                    <strong>Our Response:</strong> ${enquiry.response}
                </div>
            `
                : ""
            }
        </div>
    `,
    )
    .join("")

  container.innerHTML = enquiriesHTML
}

function handleProfileUpdate(e) {
  e.preventDefault()

  // Validate form
  if (!validateProfileForm()) {
    return
  }

  const formData = new FormData(e.target)
  const data = Object.fromEntries(formData.entries())

  const submitBtn = e.target.querySelector('button[type="submit"]')
  setButtonLoading(submitBtn, true)
const location = getHostPath()
  fetch(`${location}/api/update_customer_profile.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showAlert("Profile updated successfully!", "success")
        // Update header info
        document.getElementById("profileName").textContent = `${data.firstName} ${data.lastName}`
        document.getElementById("profileEmail").textContent = data.email
        document.getElementById("userName").textContent = data.firstName
      } else {
        showAlert(result.message || "Failed to update profile", "error")
      }
    })
    .catch((error) => {
      console.error("Profile update error:", error)
      showAlert("Failed to update profile. Please try again.", "error")
    })
    .finally(() => {
      setButtonLoading(submitBtn, false)
    })
}

function handlePasswordUpdate(e) {
  e.preventDefault()

  // Validate form
  if (!validatePasswordForm()) {
    return
  }

  const formData = new FormData(e.target)
  const data = Object.fromEntries(formData.entries())

  const submitBtn = e.target.querySelector('button[type="submit"]')
  setButtonLoading(submitBtn, true)
const location = getHostPath()
fetch(`${location}/api/update_customer_password.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showAlert("Password updated successfully!", "success")
        e.target.reset()
        // Reset password strength indicator
        resetPasswordStrength()
      } else {
        showAlert(result.message || "Failed to update password", "error")
      }
    })
    .catch((error) => {
      console.error("Password update error:", error)
      showAlert("Failed to update password. Please try again.", "error")
    })
    .finally(() => {
      setButtonLoading(submitBtn, false)
    })
}

function validateProfileForm() {
  let isValid = true

  const firstName = document.getElementById("firstName")
  const lastName = document.getElementById("lastName")
  const email = document.getElementById("email")
  const phone = document.getElementById("phone")

  // Validate first name
  if (!firstName.value.trim()) {
    showFieldError(firstName, "First name is required")
    isValid = false
  }

  // Validate last name
  if (!lastName.value.trim()) {
    showFieldError(lastName, "Last name is required")
    isValid = false
  }

  // Validate email
  if (!email.value.trim()) {
    showFieldError(email, "Email is required")
    isValid = false
  } else if (!isValidEmail(email.value)) {
    showFieldError(email, "Please enter a valid email address")
    isValid = false
  }

  // Validate phone
  if (!phone.value.trim()) {
    showFieldError(phone, "Phone number is required")
    isValid = false
  }

  return isValid
}

function validatePasswordForm() {
  let isValid = true

  const currentPassword = document.getElementById("currentPassword")
  const newPassword = document.getElementById("newPassword")
  const confirmNewPassword = document.getElementById("confirmNewPassword")

  // Validate current password
  if (!currentPassword.value) {
    showFieldError(currentPassword, "Current password is required")
    isValid = false
  }

  // Validate new password
  if (!newPassword.value) {
    showFieldError(newPassword, "New password is required")
    isValid = false
  } else if (newPassword.value.length < 8) {
    showFieldError(newPassword, "Password must be at least 8 characters long")
    isValid = false
  }

  // Validate confirm password
  if (!confirmNewPassword.value) {
    showFieldError(confirmNewPassword, "Please confirm your new password")
    isValid = false
  } else if (newPassword.value !== confirmNewPassword.value) {
    showFieldError(confirmNewPassword, "Passwords do not match")
    isValid = false
  }

  return isValid
}

function validateField(field) {
  const value = field.value.trim()

  switch (field.id) {
    case "firstName":
    case "lastName":
      if (!value) {
        showFieldError(field, `${field.id === "firstName" ? "First" : "Last"} name is required`)
        return false
      }
      break
    case "email":
      if (!value) {
        showFieldError(field, "Email is required")
        return false
      } else if (!isValidEmail(value)) {
        showFieldError(field, "Please enter a valid email address")
        return false
      }
      break
    case "phone":
      if (!value) {
        showFieldError(field, "Phone number is required")
        return false
      }
      break
  }

  clearFieldError(field)
  return true
}

function validatePasswordMatch() {
  const newPassword = document.getElementById("newPassword")
  const confirmNewPassword = document.getElementById("confirmNewPassword")

  if (confirmNewPassword.value && newPassword.value !== confirmNewPassword.value) {
    showFieldError(confirmNewPassword, "Passwords do not match")
  } else {
    clearFieldError(confirmNewPassword)
  }
}

function showFieldError(field, message) {
  const errorElement = document.getElementById(field.id + "Error")
  if (errorElement) {
    errorElement.textContent = message
    errorElement.classList.add("show")
  }
  field.parentElement.classList.add("error")
}

function clearFieldError(field) {
  const errorElement = document.getElementById(field.id + "Error")
  if (errorElement) {
    errorElement.classList.remove("show")
  }
  field.parentElement.classList.remove("error")
}

function checkPasswordStrength(password) {
  const strengthBar = document.querySelector(".strength-fill")
  const strengthText = document.querySelector(".strength-text")

  if (!strengthBar || !strengthText) return

  let score = 0
  const feedback = []

  // Length check
  if (password.length >= 8) score++
  else feedback.push("8+ characters")

  // Lowercase check
  if (/[a-z]/.test(password)) score++
  else feedback.push("lowercase letter")

  // Uppercase check
  if (/[A-Z]/.test(password)) score++
  else feedback.push("uppercase letter")

  // Number check
  if (/[0-9]/.test(password)) score++
  else feedback.push("number")

  // Special character check
  if (/[^A-Za-z0-9]/.test(password)) score++
  else feedback.push("special character")

  const percentage = (score / 5) * 100
  strengthBar.style.width = percentage + "%"

  let color, text
  if (score <= 2) {
    color = "#ff4444"
    text = "Weak"
  } else if (score <= 3) {
    color = "#ffaa00"
    text = "Fair"
  } else if (score <= 4) {
    color = "#4caf50"
    text = "Good"
  } else {
    color = "#4caf50"
    text = "Strong"
  }

  strengthBar.style.background = color

  if (feedback.length > 0) {
    strengthText.textContent = `${text} - Add: ${feedback.join(", ")}`
  } else {
    strengthText.textContent = text
  }
}

function resetPasswordStrength() {
  const strengthBar = document.querySelector(".strength-fill")
  const strengthText = document.querySelector(".strength-text")

  if (strengthBar) strengthBar.style.width = "0%"
  if (strengthText) strengthText.textContent = "Password strength"
}

function filterBookings(status) {
  const bookingItems = document.querySelectorAll(".booking-item")

  bookingItems.forEach((item) => {
    if (!status || item.dataset.status === status) {
      item.style.display = "block"
    } else {
      item.style.display = "none"
    }
  })
}

function filterEnquiries(status) {
  const enquiryItems = document.querySelectorAll(".enquiry-item")

  enquiryItems.forEach((item) => {
    if (!status || item.dataset.status === status) {
      item.style.display = "block"
    } else {
      item.style.display = "none"
    }
  })
}

function updateStats(type, count) {
  const element = document.getElementById(`total${type.charAt(0).toUpperCase() + type.slice(1)}`)
  if (element) {
    element.textContent = count
  }
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  })
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function checkAuthStatus() {
  const currentPage = window.location.pathname
  const isAuthPage = currentPage.includes("login.php") || currentPage.includes("register.php")
  const isProfilePage = currentPage.includes("profile.php")
    const location = getHostPath()

  fetch(`${location}/api/check_auth.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.authenticated) {
        // User is logged in
        if (isAuthPage) {
          // Redirect to profile if on auth page
          window.location.href = "profile.php"
        }
        } else {
        // User is not logged in
        if (isProfilePage) {
          // Redirect to login if on profile page
          window.location.href = "login.php"
        }
      }
    })
    .catch((error) => {
      console.error("Auth check error:", error)
    })
}
async function logout() {
  try {
    const location = getHostPath()

    const response = await fetch(`${location}/api/customer_logout.php`, {
      method: "POST",
    })

    const result = await response.json()

    if (result.success) {
      showAlert("Logged out successfully", "success")
      setTimeout(() => {
        window.location.href = "index.php"
      }, 1000)
    } else {
      throw new Error("Logout failed")
    }
  } catch (error) {
    console.error("Logout error:", error)
    // Force redirect even if logout API fails
    window.location.href = "index.php"
  }
}

function togglePassword(fieldId) {
  const field = document.getElementById(fieldId)
  const toggle = field.nextElementSibling
  const icon = toggle.querySelector("i")

  if (field.type === "password") {
    field.type = "text"
    icon.classList.remove("fa-eye")
    icon.classList.add("fa-eye-slash")
  } else {
    field.type = "password"
    icon.classList.remove("fa-eye-slash")
    icon.classList.add("fa-eye")
  }
}

function showAlert(message, type) {
  // Remove existing alerts
  const existingAlerts = document.querySelectorAll(".alert")
  existingAlerts.forEach((alert) => alert.remove())

  // Create new alert
  const alert = document.createElement("div")
  alert.className = `alert alert-${type}`

  const icon = type === "success" ? "fas fa-check-circle" : "fas fa-exclamation-triangle"
  alert.innerHTML = `
        <i class="${icon}"></i>
        <span>${message}</span>
    `

  // Add to page
  document.body.appendChild(alert)

  // Auto remove after 5 seconds
  setTimeout(() => {
    alert.remove()
  }, 5000)

  // Allow manual close
  alert.addEventListener("click", () => {
    alert.remove()
  })
}

function setButtonLoading(button, loading) {
  const btnText = button.querySelector(".btn-text")
  const btnLoading = button.querySelector(".btn-loading")

  if (loading) {
    button.disabled = true
    btnText.style.display = "none"
    btnLoading.style.display = "flex"
  } else {
    button.disabled = false
    btnText.style.display = "flex"
    btnLoading.style.display = "none"
  }
}

function showLoading(message) {
  // Implementation for global loading state if needed
  console.log("Loading:", message)
}

function hideLoading() {
  // Implementation for hiding global loading state if needed
  console.log("Loading complete")
}
