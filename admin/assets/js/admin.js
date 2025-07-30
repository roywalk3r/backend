
// Sidebar toggle
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar")
  sidebar.classList.toggle("active")
}

// Notifications dropdown
function toggleNotifications() {
  const dropdown = document.getElementById("notificationDropdown")
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block"
}

// User menu dropdown
function toggleUserMenu() {
  const menu = document.getElementById("userMenu")
  menu.style.display = menu.style.display === "block" ? "none" : "block"
}

// Close dropdowns when clicking outside
document.addEventListener("click", (event) => {
  const notificationBtn = document.querySelector(".notification-btn")
  const notificationDropdown = document.getElementById("notificationDropdown")
  const userBtn = document.querySelector(".user-btn")
  const userMenu = document.getElementById("userMenu")

  // Close notification dropdown
  if (notificationDropdown && !notificationBtn.contains(event.target)) {
    notificationDropdown.style.display = "none"
  }

  // Close user menu
  if (userMenu && !userBtn.contains(event.target)) {
    userMenu.style.display = "none"
  }
})

// Initialize charts
function initializeCharts(bookingStatusData) {
  // Booking Status Chart
  const statusCtx = document.getElementById("bookingStatusChart")
  if (statusCtx) {
    const statusLabels = bookingStatusData.map((item) => item.status.charAt(0).toUpperCase() + item.status.slice(1))
    const statusCounts = bookingStatusData.map((item) => item.count)

    new Chart(statusCtx, {
      type: "doughnut",
      data: {
        labels: statusLabels,
        datasets: [
          {
            data: statusCounts,
            backgroundColor: ["#ffc107", "#28a745", "#17a2b8", "#dc3545"],
            borderWidth: 2,
            borderColor: "#fff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
        },
      },
    })
  }

  // Revenue Chart (sample data)
  const revenueCtx = document.getElementById("revenueChart")
  if (revenueCtx) {
    new Chart(revenueCtx, {
      type: "line",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        datasets: [
          {
            label: "Revenue ($)",
            data: [12000, 15000, 18000, 22000, 25000, 28000],
            borderColor: "#2c5530",
            backgroundColor: "rgba(44, 85, 48, 0.1)",
            tension: 0.4,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => "$" + value.toLocaleString(),
            },
          },
        },
        plugins: {
          legend: {
            display: false,
          },
        },
      },
    })
  }
}

// Form validation
function validateForm(formId) {
  const form = document.getElementById(formId)
  const inputs = form.querySelectorAll("input[required], select[required], textarea[required]")
  let isValid = true

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      input.classList.add("error")
      isValid = false
    } else {
      input.classList.remove("error")
    }
  })

  return isValid
}

// Show loading state
function showLoading(elementId) {
  const element = document.getElementById(elementId)
  if (element) {
    element.innerHTML = '<div class="loading"><div class="spinner"></div></div>'
  }
}

// Hide loading state
function hideLoading(elementId) {
  const element = document.getElementById(elementId)
  if (element) {
    const loading = element.querySelector(".loading")
    if (loading) {
      loading.remove()
    }
  }
}

// Show alert message
function showAlert(message, type = "info") {
  const alert = document.createElement("div")
  alert.className = `alert alert-${type}`
  alert.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`

  // Insert at top of content
  const content = document.querySelector(".content")
  if (content) {
    content.insertBefore(alert, content.firstChild)

    // Auto remove after 5 seconds
    setTimeout(() => {
      alert.remove()
    }, 5000)
  }
}

// Confirm delete action
function confirmDelete(message = "Are you sure you want to delete this item?") {
  return confirm(message)
}

// Format currency
function formatCurrency(amount) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(amount)
}

// Format date
function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  })
}

// Export data to CSV
function exportToCSV(data, filename) {
  const csv = data.map((row) => Object.values(row).join(",")).join("\n")
  const blob = new Blob([csv], { type: "text/csv" })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement("a")
  a.href = url
  a.download = filename
  a.click()
  window.URL.revokeObjectURL(url)
}

// Print functionality
function printPage() {
  window.print()
}

// Auto-refresh data every 5 minutes
function startAutoRefresh() {
  setInterval(() => {
    if (document.visibilityState === "visible") {
      location.reload()
    }
  }, 300000) // 5 minutes
}

// Initialize admin panel
document.addEventListener("DOMContentLoaded", () => {
  // Start auto-refresh
  startAutoRefresh()

  // Initialize tooltips if needed
  const tooltips = document.querySelectorAll("[data-tooltip]")
  tooltips.forEach((tooltip) => {
    tooltip.addEventListener("mouseenter", () => {
      // Add tooltip functionality if needed
    })
  })

  // Handle form submissions with loading states
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const submitBtn = form.querySelector('button[type="submit"]')
      if (submitBtn) {
        submitBtn.disabled = true
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'

        // Re-enable after 3 seconds (fallback)
        setTimeout(() => {
          submitBtn.disabled = false
          submitBtn.innerHTML = submitBtn.getAttribute("data-original-text") || "Submit"
        }, 3000)
      }
    })
  })
})

// Real-time notifications (WebSocket or polling)
function initializeNotifications() {
  // This would connect to your WebSocket server or use polling
  // For now, we'll simulate with localStorage
  setInterval(() => {
    // Check for new notifications
    const lastCheck = localStorage.getItem("lastNotificationCheck") || 0
    const now = Date.now()

    if (now - lastCheck > 60000) {
      // Check every minute
      // fetchNotifications();
      localStorage.setItem("lastNotificationCheck", now)
    }
  }, 60000)
}

// Search functionality
function initializeSearch() {
  const searchInputs = document.querySelectorAll('input[type="search"], input[name="search"]')
  searchInputs.forEach((input) => {
    let timeout
    input.addEventListener("input", () => {
      clearTimeout(timeout)
      timeout = setTimeout(() => {
        // Perform search
        const form = input.closest("form")
        if (form) {
          form.submit()
        }
      }, 500)
    })
  })
}



// Initialize search on page load
document.addEventListener("DOMContentLoaded", initializeSearch)
