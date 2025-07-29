document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm")

  if (loginForm) {
    loginForm.addEventListener("submit", handleLoginSubmit)

    // Add real-time validation
    const inputs = loginForm.querySelectorAll("input")
    inputs.forEach((input) => {
      input.addEventListener("blur", validateField)
      input.addEventListener("input", clearFieldError)
    })
  }

  // Check for registration success message
  const urlParams = new URLSearchParams(window.location.search)
  if (urlParams.get("registered") === "true") {
    window.showToast("Registration successful! Please login with your credentials.", "success")
  }
})

function handleLoginSubmit(e) {
  e.preventDefault()

  const formData = new FormData(e.target)
  const data = Object.fromEntries(formData.entries())

  // Clear previous errors
  clearAllErrors()

  // Validate form
  if (!validateLoginForm(data)) {
    return
  }

  // Show loading state
  const submitBtn = e.target.querySelector('button[type="submit"]')
  setButtonLoading(submitBtn, true)

  // Submit login request
  fetch("/api/customer_login.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((result) => {
      console.log("Login response:", result)

      if (result.success) {
        window.showToast("Login successful! Redirecting...", "success")

        // Redirect after short delay
        setTimeout(() => {
          const redirectUrl = new URLSearchParams(window.location.search).get("redirect") || "profile.php"
          window.location.href = redirectUrl
        }, 1500)
      } else {
        window.showToast(result.message || "Login failed", "error")

        // Handle specific error cases
        if (result.locked) {
          window.showToast("Account temporarily locked due to multiple failed attempts", "warning")
        }
      }
    })
    .catch((error) => {
      console.error("Login error:", error)
      window.showToast("Login failed. Please check your connection and try again.", "error")
    })
    .finally(() => {
      setButtonLoading(submitBtn, false)
    })
}

function validateLoginForm(data) {
  let isValid = true

  // Email validation
  if (!data.email || data.email.trim() === "") {
    showFieldError("email", "Email is required")
    isValid = false
  } else if (!validateEmail(data.email)) {
    showFieldError("email", "Please enter a valid email address")
    isValid = false
  }

  // Password validation
  if (!data.password || data.password.trim() === "") {
    showFieldError("password", "Password is required")
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

  if (field.type === "email" && value && !validateEmail(value)) {
    showFieldError(field.name, "Please enter a valid email address")
    return false
  }

  return true
}

function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

function showFieldError(fieldName, message) {
  const errorElement = document.getElementById(`${fieldName}-error`)
  const inputElement = document.querySelector(`[name="${fieldName}"]`)

  if (errorElement && inputElement) {
    errorElement.textContent = message
    errorElement.style.display = "block"
    inputElement.classList.add("error")
  }
}

function clearFieldError(fieldName) {
  const errorElement = document.getElementById(`${fieldName}-error`)
  const inputElement = document.querySelector(`[name="${fieldName}"]`)

  if (errorElement && inputElement) {
    errorElement.style.display = "none"
    inputElement.classList.remove("error")
  }
}

function clearAllErrors() {
  const errorElements = document.querySelectorAll(".field-error")
  const inputElements = document.querySelectorAll("input.error")

  errorElements.forEach((el) => (el.style.display = "none"))
  inputElements.forEach((el) => el.classList.remove("error"))
}

function setButtonLoading(button, isLoading) {
  const btnText = button.querySelector(".btn-text")
  const btnLoading = button.querySelector(".btn-loading")

  if (isLoading) {
    button.disabled = true
    btnText.style.display = "none"
    btnLoading.style.display = "flex"
  } else {
    button.disabled = false
    btnText.style.display = "block"
    btnLoading.style.display = "none"
  }
}

// Password toggle functionality
 
// Demo credentials function (for development)
function fillDemoCredentials() {
  document.getElementById("email").value = "demo@nananom.com"
  document.getElementById("password").value = "demo123456"
}

// Add demo button in development
if (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1") {
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm")
    if (form) {
      const demoBtn = document.createElement("button")
      demoBtn.type = "button"
      demoBtn.className = "btn btn-secondary"
      demoBtn.textContent = "Fill Demo Credentials"
      demoBtn.style.marginBottom = "15px"
      demoBtn.onclick = fillDemoCredentials

      form.insertBefore(demoBtn, form.firstChild)
    }
  })
}

// Declare showToast function for demonstration purposes
window.showToast = (message, type) => {
  console.log(`Toast message: ${message} (Type: ${type})`)
}
