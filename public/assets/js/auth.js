// Authentication JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Check if user is already logged in
  checkAuthStatus()
  // Initialize user menu dropdown
  const userMenuToggle = document.getElementById("userMenuToggle")
  const userDropdown = document.getElementById("userDropdown")
//get host path from server url
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
  
  
  })
    const mainPath = getHostPath()

   function getHostPath() {
    const url = new URL(window.location.href)
    return url.origin + '/' + url.pathname.split('/')[1]
  }
// Check authentication status
function checkAuthStatus() {
  const currentPage = window.location.pathname
  const isAuthPage =  currentPage.includes("register.php")
  const isProfilePage = currentPage.includes("profile.php")

  fetch(`${mainPath}/api/check_auth.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.authenticated) {
        // User is logged in
        if (isAuthPage) {
          // Redirect to profile if on auth page
          window.location.href = "profile.php"
        }
        updateNavigation(data.user)
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
 
 
 

// Logout function
async function logout() {
  try {
    const response = await fetch(`${mainPath}/api/customer_logout.php`, {
      method: "POST",
    })

    const result = await response.json()

    if (result.success) {
      showAlert("Logged out successfully", "success")
      setTimeout(() => {
        window.location.href = "index.php"
      }, 1000)
    }
  } catch (error) {
    console.error("Logout error:", error)
    window.location.href = "index.php"
  }
}

// Update navigation for logged in user
function updateNavigation(user) {
  const navMenu = document.querySelector(".nav-menu")
  if (!navMenu) return

  // Remove login/register links
  const authLinks = navMenu.querySelectorAll('a[href="login.php"], a[href="register.php"]')
  authLinks.forEach((link) => link.remove())

  // Add user menu if not exists
  let userMenu = navMenu.querySelector(".user-menu")
  if (!userMenu) {
    userMenu = document.createElement("div")
    userMenu.className = "user-menu"
    userMenu.innerHTML = `
            <button class="user-menu-toggle">
                <i class="fas fa-user-circle"></i>
                <span>${user.name}</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="user-dropdown">
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        `
    navMenu.appendChild(userMenu)

    // Add dropdown toggle functionality
    const toggle = userMenu.querySelector(".user-menu-toggle")
    const dropdown = userMenu.querySelector(".user-dropdown")

    toggle.addEventListener("click", () => {
      dropdown.classList.toggle("show")
    })

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (!userMenu.contains(e.target)) {
        dropdown.classList.remove("show")
      }
    })
  }
}

// Password toggle functionality
function togglePassword(inputId) {
  const input = document.getElementById(inputId)
  const toggle = input.parentElement.querySelector(".password-toggle i")

  if (input.type === "password") {
    input.type = "text"
    toggle.className = "fas fa-eye-slash"
  } else {
    input.type = "password"
    toggle.className = "fas fa-eye"
  }
}

 

 
 
 
// Auto-save functionality
function setupAutoSave(form, saveUrl, interval = 30000) {
  let saveTimer
  let hasChanges = false

  const inputs = form.querySelectorAll("input, select, textarea")
  inputs.forEach((input) => {
    input.addEventListener("input", () => {
      hasChanges = true
      clearTimeout(saveTimer)
      saveTimer = setTimeout(() => {
        if (hasChanges) {
          autoSave(form, saveUrl)
        }
      }, interval)
    })
  })
}

function autoSave(form, saveUrl) {
  const data = serializeForm(form)

  fetch(saveUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        console.log("Auto-saved successfully")
      }
    })
    .catch((error) => {
      console.error("Auto-save failed:", error)
    })
}
function togglePassword(inputId) {
  const input = document.getElementById(inputId)
  const toggle = input.parentElement.querySelector(".password-toggle i")

  if (input.type === "password") {
    input.type = "text"
    toggle.className = "fas fa-eye-slash"
  } else {
    input.type = "password"
    toggle.className = "fas fa-eye"
  }
}

// Initialize common functionality
document.addEventListener("DOMContentLoaded", () => {
  // Setup password toggles
  


  // Setup form validation
  document.querySelectorAll("form").forEach((form) => {
    const inputs = form.querySelectorAll("input[required]")
    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        if (!this.value.trim()) {
          showFieldError(this.name, "This field is required")
        } else {
          clearFieldError(this.name)
        }
      })
    })
  })
})

 
 
 