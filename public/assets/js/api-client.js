 class APIClient {
  constructor() {
    this.baseURL = "../api"
    this.defaultHeaders = {
      "Content-Type": "application/json",
      Accept: "application/json",
    }
    this.requestQueue = new Map()
    this.retryAttempts = 3
    this.retryDelay = 1000
  }

  // Generic request method
  async request(endpoint, options = {}) {
    const url = `${this.baseURL}/${endpoint}`
    const requestId = `${options.method || "GET"}_${endpoint}_${Date.now()}`

    // Prevent duplicate requests
    if (this.requestQueue.has(requestId)) {
      return this.requestQueue.get(requestId)
    }

    const config = {
      method: "GET",
      headers: { ...this.defaultHeaders },
      ...options,
    }

    // Add authentication token if available (for admin APIs)
    const token = this.getAuthToken()
    if (token) {
      config.headers["Authorization"] = `Bearer ${token}`
    }

    const requestPromise = this.executeRequest(url, config, requestId)
    this.requestQueue.set(requestId, requestPromise)

    try {
      const result = await requestPromise
      return result
    } finally {
      this.requestQueue.delete(requestId)
    }
  }

  async executeRequest(url, config, requestId, attempt = 1) {
    try {
      const response = await fetch(url, config)

      if (!response.ok) {
        throw new APIError(
          `HTTP ${response.status}: ${response.statusText}`,
          response.status,
          await this.parseErrorResponse(response),
        )
      }

      const data = await response.json()

      if (!data.success && data.success !== undefined) {
        throw new APIError(data.message || "Request failed", response.status, data)
      }

      return data
    } catch (error) {
      if (attempt < this.retryAttempts && this.shouldRetry(error)) {
        await this.delay(this.retryDelay * attempt)
        return this.executeRequest(url, config, requestId, attempt + 1)
      }
      throw error
    }
  }

  async parseErrorResponse(response) {
    try {
      return await response.json()
    } catch {
      return { message: response.statusText }
    }
  }

  shouldRetry(error) {
    return (
      error instanceof TypeError || // Network errors
      (error.status >= 500 && error.status < 600)
    ) // Server errors
  }

  delay(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms))
  }

  getAuthToken() {
    return localStorage.getItem("admin_token") || sessionStorage.getItem("admin_token")
  }

  setAuthToken(token, remember = false) {
    const storage = remember ? localStorage : sessionStorage
    storage.setItem("admin_token", token)
  }

  clearAuthToken() {
    localStorage.removeItem("admin_token")
    sessionStorage.removeItem("admin_token")
  }

  // Booking APIs
  async createBooking(bookingData) {
    return this.request("create_booking.php", {
      method: "POST",
      body: JSON.stringify(bookingData),
    })
  }

  async getBookings(filters = {}) {
    const params = new URLSearchParams(filters)
    return this.request(`get_bookings.php?${params}`)
  }

  async updateBookingStatus(bookingId, status, notes = "") {
    return this.request("update_booking_status.php", {
      method: "POST",
      body: JSON.stringify({
        booking_id: bookingId,
        status: status,
        notes: notes,
      }),
    })
  }

  async deleteBooking(bookingId) {
    return this.request("delete_booking.php", {
      method: "POST",
      body: JSON.stringify({ booking_id: bookingId }),
    })
  }

  // Enquiry APIs
  async createEnquiry(enquiryData) {
    return this.request("create_enquiry.php", {
      method: "POST",
      body: JSON.stringify(enquiryData),
    })
  }

  async getEnquiries(filters = {}) {
    const params = new URLSearchParams(filters)
    return this.request(`get_enquiries.php?${params}`)
  }

  async updateEnquiryStatus(enquiryId, status) {
    return this.request("update_enquiry_status.php", {
      method: "POST",
      body: JSON.stringify({
        enquiry_id: enquiryId,
        status: status,
      }),
    })
  }

  // Feedback APIs
  async createFeedback(feedbackData) {
    return this.request("create_feedback.php", {
      method: "POST",
      body: JSON.stringify(feedbackData),
    })
  }

  async getFeedback(filters = {}) {
    const params = new URLSearchParams(filters)
    return this.request(`get_feedback.php?${params}`)
  }

  async updateFeedbackStatus(feedbackId, status) {
    return this.request("update_feedback_status.php", {
      method: "POST",
      body: JSON.stringify({
        feedback_id: feedbackId,
        status: status,
      }),
    })
  }

  // Service APIs
  async getServices(filters = {}) {
    const params = new URLSearchParams(filters)
    console.log('Fetching services with filters:', filters)
    return this.request(`get_services.php?${params}`)
  }

  async createService(serviceData) {
    return this.request("create_service.php", {
      method: "POST",
      body: JSON.stringify(serviceData),
    })
  }

  async updateService(serviceId, serviceData) {
    return this.request("update_service.php", {
      method: "POST",
      body: JSON.stringify({
        service_id: serviceId,
        ...serviceData,
      }),
    })
  }

  async deleteService(serviceId) {
    return this.request("delete_service.php", {
      method: "POST",
      body: JSON.stringify({ service_id: serviceId }),
    })
  }

  // User Management APIs (Admin only)
  async getUsers(filters = {}) {
    const params = new URLSearchParams(filters)
    return this.request(`get_users.php?${params}`)
  }

  async createUser(userData) {
    return this.request("create_user.php", {
      method: "POST",
      body: JSON.stringify(userData),
    })
  }

  async updateUser(userId, userData) {
    return this.request("update_user.php", {
      method: "POST",
      body: JSON.stringify({
        user_id: userId,
        ...userData,
      }),
    })
  }

  async deleteUser(userId) {
    return this.request("delete_user.php", {
      method: "POST",
      body: JSON.stringify({ user_id: userId }),
    })
  }

  async getRoles() {
    return this.request("get_roles.php")
  }

  // Authentication APIs
  async login(credentials) {
    const response = await this.request("login.php", {
      method: "POST",
      body: JSON.stringify(credentials),
    })

    if (response.success && response.token) {
      this.setAuthToken(response.token, credentials.remember)
    }

    return response
  }

  async logout() {
    try {
      await this.request("logout.php", { method: "POST" })
    } finally {
      this.clearAuthToken()
    }
  }

  async getCurrentUser() {
    return this.request("get_current_user.php")
  }

  async updateProfile(profileData) {
    return this.request("update_profile.php", {
      method: "POST",
      body: JSON.stringify(profileData),
    })
  }

  async changePassword(passwordData) {
    return this.request("change_password.php", {
      method: "POST",
      body: JSON.stringify(passwordData),
    })
  }

  // Statistics APIs
  async getDashboardStats() {
    return this.request("stats.php")
  }

  async getBookingStats(dateRange = {}) {
    const params = new URLSearchParams(dateRange)
    return this.request(`booking_stats.php?${params}`)
  }

  async getRevenueStats(dateRange = {}) {
    const params = new URLSearchParams(dateRange)
    return this.request(`revenue_stats.php?${params}`)
  }

  // File Upload API
  async uploadFile(file, type = "general") {
    const formData = new FormData()
    formData.append("file", file)
    formData.append("type", type)

    return this.request("upload_file.php", {
      method: "POST",
      headers: {
        // Remove Content-Type to let browser set it with boundary
        ...Object.fromEntries(Object.entries(this.defaultHeaders).filter(([key]) => key !== "Content-Type")),
      },
      body: formData,
    })
  }

  // Export APIs
  async exportData(type, filters = {}, format = "csv") {
    const params = new URLSearchParams({
      type: type,
      format: format,
      ...filters,
    })

    const response = await fetch(`${this.baseURL}/export_data.php?${params}`, {
      headers: {
        Authorization: `Bearer ${this.getAuthToken()}`,
      },
    })

    if (!response.ok) {
      throw new APIError(`Export failed: ${response.statusText}`, response.status)
    }

    return response.blob()
  }

  // Utility methods
  async healthCheck() {
    try {
      const response = await this.request("health.php")
      return response.status === "ok"
    } catch {
      return false
    }
  }

  // Batch operations
  async batchRequest(requests) {
    const promises = requests.map((req) =>
      this.request(req.endpoint, req.options).catch((error) => ({ error, request: req })),
    )

    return Promise.all(promises)
  }
}

// Custom Error class for API errors
class APIError extends Error {
  constructor(message, status, data = null) {
    super(message)
    this.name = "APIError"
    this.status = status
    this.data = data
  }
}

// Form Handler utility class
class FormHandler {
  constructor(apiClient) {
    this.api = apiClient
    this.loadingElements = new Set()
  }

  async handleFormSubmission(form, apiMethod, options = {}) {
    const formData = new FormData(form)
    const data = Object.fromEntries(formData.entries())

    // Show loading state
    this.showLoading(form)

    try {
      // Validate form data
      if (options.validate) {
        const validation = options.validate(data)
        if (!validation.valid) {
          this.showValidationErrors(form, validation.errors)
          return { success: false, errors: validation.errors }
        }
      }

      // Transform data if needed
      if (options.transform) {
        Object.assign(data, options.transform(data))
      }

      // Make API call
      const result = await apiMethod.call(this.api, data)

      if (result.success) {
        this.showSuccess(form, options.successMessage || "Operation completed successfully")
        if (options.onSuccess) {
          options.onSuccess(result, form)
        }
      } else {
        this.showError(form, result.message || "Operation failed")
      }

      return result
    } catch (error) {
      console.error("Form submission error:", error)
      this.showError(form, error.message || "An unexpected error occurred")
      return { success: false, error: error.message }
    } finally {
      this.hideLoading(form)
    }
  }

  showLoading(form) {
    const submitBtn = form.querySelector('button[type="submit"]')
    if (submitBtn) {
      submitBtn.disabled = true
      submitBtn.dataset.originalText = submitBtn.innerHTML
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'
      this.loadingElements.add(submitBtn)
    }
  }

  hideLoading(form) {
    const submitBtn = form.querySelector('button[type="submit"]')
    if (submitBtn && this.loadingElements.has(submitBtn)) {
      submitBtn.disabled = false
      submitBtn.innerHTML = submitBtn.dataset.originalText || "Submit"
      this.loadingElements.delete(submitBtn)
    }
  }

  showValidationErrors(form, errors) {
    // Clear previous errors
    form.querySelectorAll(".error-message").forEach((el) => el.remove())
    form.querySelectorAll(".error").forEach((el) => el.classList.remove("error"))

    // Show new errors
    Object.entries(errors).forEach(([field, message]) => {
      const input = form.querySelector(`[name="${field}"]`)
      if (input) {
        input.classList.add("error")
        const errorEl = document.createElement("div")
        errorEl.className = "error-message"
        errorEl.textContent = message
        input.parentNode.appendChild(errorEl)
      }
    })
  }

  showSuccess(form, message) {
    this.showMessage(message, "success")
    form.reset()
  }

  showError(form, message) {
    this.showMessage(message, "error")
  }

  showMessage(message, type) {
    // This would integrate with your toast/notification system
    if (window.app && window.app.showToast) {
      window.app.showToast(message, type)
    } else {
      console.log(`${type.toUpperCase()}: ${message}`)
    }
  }
}

// Data Manager for caching and state management
class DataManager {
  constructor(apiClient) {
    this.api = apiClient
    this.cache = new Map()
    this.cacheTimeout = 5 * 60 * 1000 // 5 minutes
  }

  async getData(key, fetcher, forceRefresh = false) {
    if (!forceRefresh && this.cache.has(key)) {
      const cached = this.cache.get(key)
      if (Date.now() - cached.timestamp < this.cacheTimeout) {
        return cached.data
      }
    }

    try {
      const data = await fetcher()
      this.cache.set(key, {
        data: data,
        timestamp: Date.now(),
      })
      return data
    } catch (error) {
      // Return cached data if available, even if expired
      if (this.cache.has(key)) {
        console.warn("Using cached data due to fetch error:", error)
        return this.cache.get(key).data
      }
      throw error
    }
  }

  invalidateCache(pattern) {
    if (pattern) {
      for (const key of this.cache.keys()) {
        if (key.includes(pattern)) {
          this.cache.delete(key)
        }
      }
    } else {
      this.cache.clear()
    }
  }

  // Specific data fetchers with caching
  async getServices(forceRefresh = false) {
    return this.getData("services", () => this.api.getServices(), forceRefresh)
  }

  async getBookings(filters = {}, forceRefresh = false) {
    const key = `bookings_${JSON.stringify(filters)}`
    return this.getData(key, () => this.api.getBookings(filters), forceRefresh)
  }

  async getFeedback(filters = {}, forceRefresh = false) {
    const key = `feedback_${JSON.stringify(filters)}`
    return this.getData(key, () => this.api.getFeedback(filters), forceRefresh)
  }

  async getUsers(filters = {}, forceRefresh = false) {
    const key = `users_${JSON.stringify(filters)}`
    return this.getData(key, () => this.api.getUsers(filters), forceRefresh)
  }
}

// Initialize global instances
const apiClient = new APIClient()
const formHandler = new FormHandler(apiClient)
const dataManager = new DataManager(apiClient)

// Export for use in other scripts
window.apiClient = apiClient
window.formHandler = formHandler
window.dataManager = dataManager
window.APIError = APIError

// Auto-retry failed requests on network recovery
window.addEventListener("online", () => {
  console.log("Network recovered, retrying failed requests...")
  // Could implement request queue retry logic here
})

// Handle authentication errors globally
window.addEventListener("unhandledrejection", (event) => {
  if (event.reason instanceof APIError && event.reason.status === 401) {
    console.warn("Authentication expired, redirecting to login...")
    apiClient.clearAuthToken()
    if (window.location.pathname.includes("/admin/")) {
      window.location.href = "/admin/login.php"
    }
  }
})
