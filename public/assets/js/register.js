// Register page specific JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Initialize password strength checker
  const passwordInput = document.getElementById("password")
  if (passwordInput) {
    passwordInput.addEventListener("input", function () {
      checkPasswordStrength(this.value)
    })
  }

  // Initialize form validation
  const registerForm = document.getElementById("registerForm")
  if (registerForm) {
    registerForm.addEventListener("submit", handleRegisterSubmit)

    // Real-time validation
    const inputs = registerForm.querySelectorAll("input")
    inputs.forEach((input) => {
      input.addEventListener("blur", validateField)
      input.addEventListener("input", clearFieldError)
    })
  }

  // Password confirmation validation
  const confirmPasswordInput = document.getElementById("confirmPassword")
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener("input", validatePasswordMatch)
  }
})

function getHostPath() {
  const url = new URL(window.location.href)
  return url.origin + '/' + url.pathname.split('/')[1]
  
}
function handleRegisterSubmit(e) {
  e.preventDefault()

  const formData = new FormData(e.target)
  const data = Object.fromEntries(formData.entries())

  // Validate form
  if (!validateRegisterForm(data)) {
    return
  }

  // Show loading state
  const submitBtn = e.target.querySelector('button[type="submit"]')
  setButtonLoading(submitBtn, true)
const location = getHostPath()
  // Submit registration
  fetch(`${location}/api/customer_register.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showToast("Registration successful! Please login.", "success")
        setTimeout(() => {
          window.location.href = "login.php"
        }, 2000)
      } else {
        showToast(result.message || "Registration failed", "error")
      }
    })
    .catch((error) => {
      console.error("Registration error:", error)
      showToast("Registration failed. Please try again.", "error")
    })
    .finally(() => {
      setButtonLoading(submitBtn, false)
    })
}

function validateRegisterForm(data) {
  let isValid = true

  // Check required fields
  const requiredFields = ["firstName", "lastName", "email", "phone", "password", "confirmPassword"]
  requiredFields.forEach((field) => {
    if (!data[field] || data[field].trim() === "") {
      showFieldError(field, "This field is required")
      isValid = false
    }
  })

  // Validate email
  if (data.email && !isValidEmail(data.email)) {
    showFieldError("email", "Please enter a valid email address")
    isValid = false
  }

  // Validate phone
  if (data.phone && !isValidPhone(data.phone)) {
    showFieldError("phone", "Please enter a valid phone number")
    isValid = false
  }

  // Validate password
  if (data.password && data.password.length < 8) {
    showFieldError("password", "Password must be at least 8 characters long")
    isValid = false
  }

  // Check password match
  if (data.password !== data.confirmPassword) {
    showFieldError("confirmPassword", "Passwords do not match")
    isValid = false
  }

  // Check terms agreement
  if (!data.agreeTerms) {
    showToast("Please agree to the terms and conditions", "error")
    isValid = false
  }

  return isValid
}

function validateField(e) {
  const field = e.target
  const value = field.value.trim()

  clearFieldError(field.name)

  if (!value && field.required) {
    showFieldError(field.name, "This field is required")
    return false
  }

  switch (field.type) {
    case "email":
      if (value && !isValidEmail(value)) {
        showFieldError(field.name, "Please enter a valid email address")
        return false
      }
      break
    case "tel":
      if (value && !isValidPhone(value)) {
        showFieldError(field.name, "Please enter a valid phone number")
        return false
      }
      break
    case "password":
      if (value && value.length < 8) {
        showFieldError(field.name, "Password must be at least 8 characters long")
        return false
      }
      break
  }

  return true
}

function validatePasswordMatch() {
  const password = document.getElementById("password").value
  const confirmPassword = document.getElementById("confirmPassword").value

  clearFieldError("confirmPassword")

  if (confirmPassword && password !== confirmPassword) {
    showFieldError("confirmPassword", "Passwords do not match")
    return false
  }

  return true
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
  else feedback.push("lowercase")

  // Uppercase check
  if (/[A-Z]/.test(password)) score++
  else feedback.push("uppercase")

  // Number check
  if (/[0-9]/.test(password)) score++
  else feedback.push("number")

  // Special character check
  if (/[^A-Za-z0-9]/.test(password)) score++
  else feedback.push("special char")

  // Update UI
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
    color = "#00aa00"
    text = "Good"
  } else {
    color = "#00aa00"
    text = "Strong"
  }

  strengthBar.style.background = color

  if (feedback.length > 0) {
    strengthText.textContent = `${text} - Add: ${feedback.join(", ")}`
  } else {
    strengthText.textContent = text
  }
}

function showFieldError(fieldName, message) {
  const field = document.querySelector(`[name="${fieldName}"]`)
  if (!field) return

  // Remove existing error
  clearFieldError(fieldName)

  // Add error class
  field.classList.add("error")

  // Create error message
  const errorDiv = document.createElement("div")
  errorDiv.className = "field-error"
  errorDiv.textContent = message

  // Insert after input group or field
  const inputGroup = field.closest(".input-group") || field
  inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling)
}

function clearFieldError(fieldName) {
  const field = document.querySelector(`[name="${fieldName}"]`)
  if (!field) return

  field.classList.remove("error")

  const errorDiv = field.closest(".form-group").querySelector(".field-error")
  if (errorDiv) {
    errorDiv.remove()
  }
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

function isValidPhone(phone) {
  return /^(\+\d{12}|\d{10})$/.test(phone.replace(/[\s\-$$$$]/g, ""))
 }

function setButtonLoading(button, isLoading) {
  if (isLoading) {
    button.disabled = true
    button.textContent = "Loading..."
  } else {
    button.disabled = false
    button.textContent = "Register"
  }
}

function showToast(message, type) {
  const toast = document.createElement("div")
  toast.className = `toast ${type}`
  toast.textContent = message

  document.body.appendChild(toast)

  setTimeout(() => {
    toast.remove()
  }, 3000)
}
