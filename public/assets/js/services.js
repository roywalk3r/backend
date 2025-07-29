// Services Page JavaScript
class ServicesPage {
  constructor() {
    this.services = []
    this.filteredServices = []
    this.currentPage = 1
    this.servicesPerPage = 12
    this.comparedServices = []
    this.init()
  }

  init() {
    
    this.setupEventListeners()
    this.loadServices()
    this.initializeFilters()
  }

  setupEventListeners() {
    // Search functionality
    document.getElementById("serviceSearch").addEventListener("input", this.debounce(this.handleSearch.bind(this), 300))

    // Filter controls
    document.getElementById("categoryFilter").addEventListener("change", this.applyFilters.bind(this))
    document.getElementById("priceFilter").addEventListener("change", this.applyFilters.bind(this))
    document.getElementById("sortBy").addEventListener("change", this.applySorting.bind(this))

    // Load more button
    document.getElementById("loadMoreBtn").addEventListener("click", this.loadMoreServices.bind(this))

    // Modal controls
    document.addEventListener("click", this.handleModalClicks.bind(this))
    document.addEventListener("keydown", this.handleKeydown.bind(this))
  }

  async loadServices() {
    try {
      this.showLoading()
      const response = await fetch("/api/get_services.php")
      const data = await response.json()
      console.log(data, "ServiceDAta")
      if (data.success) {
        this.services = data.services
        this.filteredServices = [...this.services]
        this.displayServices()
        this.updateResultsCount()
      } else {
        this.showError("Failed to load services")
      }
    } catch (error) {
      console.error("Error loading services:", error)
      this.showError("Failed to load services")
    }
  }

  displayServices(append = false) {
    const servicesGrid = document.getElementById("servicesGrid")
    const startIndex = append ? (this.currentPage - 1) * this.servicesPerPage : 0
    const endIndex = this.currentPage * this.servicesPerPage
    const servicesToShow = this.filteredServices.slice(startIndex, endIndex)

    if (!append) {
      servicesGrid.innerHTML = ""
    }

    if (servicesToShow.length === 0 && !append) {
      servicesGrid.innerHTML = this.getNoResultsHTML()
      return
    }

    servicesToShow.forEach((service) => {
      const serviceCard = this.createServiceCard(service)
      servicesGrid.appendChild(serviceCard)
    })

    // Update load more button
    const loadMoreBtn = document.getElementById("loadMoreBtn")
    const hasMore = endIndex < this.filteredServices.length
    loadMoreBtn.style.display = hasMore ? "block" : "none"

    // Animate new cards
    if (append) {
      const newCards = servicesGrid.querySelectorAll(".service-card:nth-last-child(-n+" + servicesToShow.length + ")")
      newCards.forEach((card, index) => {
        setTimeout(() => {
          card.style.animation = "fadeInUp 0.5s ease forwards"
        }, index * 100)
      })
    }
  }

  createServiceCard(service) {
    const card = document.createElement("div")
    card.className = "service-card enhanced"
    card.dataset.category = service.category || "general"
    card.dataset.price = service.price
    card.dataset.serviceId = service.id

    card.innerHTML = `
            <div class="service-image">
                <i class="fas fa-${this.getServiceIcon(service.category)}"></i>
                <div class="service-badge">${this.getCategoryLabel(service.category)}</div>
                <div class="service-actions">
                    <button class="action-btn" onclick="servicesPage.viewServiceDetails(${service.id})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn" onclick="servicesPage.addToComparison(${service.id})" title="Compare">
                        <i class="fas fa-balance-scale"></i>
                    </button>
                    <button class="action-btn" onclick="servicesPage.shareService(${service.id})" title="Share">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
            </div>
            <div class="service-content">
                <h3 class="service-title">${this.escapeHtml(service.name)}</h3>
                <p class="service-description">${this.escapeHtml(service.description)}</p>
                
                <div class="service-features">
                    <span class="feature-tag"><i class="fas fa-check"></i> Quality Assured</span>
                    <span class="feature-tag"><i class="fas fa-truck"></i> Fast Delivery</span>
                    <span class="feature-tag"><i class="fas fa-headset"></i> 24/7 Support</span>
                </div>
                
                <div class="service-price">
                    <span class="price-amount">$${Number.parseFloat(service.price).toFixed(2)}</span>
                    <span class="price-unit">per ${service.unit || "unit"}</span>
                </div>
                
                <div class="service-rating">
                    ${this.generateStars(service.rating || 4.5)}
                    <span class="rating-count">(${service.review_count || 23} reviews)</span>
                </div>
                
                <div class="service-card-actions">
                    <button class="btn btn-primary btn-full" onclick="servicesPage.bookService('${this.escapeHtml(service.name)}')">
                        <i class="fas fa-calendar-plus"></i>
                        Book Now
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="servicesPage.viewServiceDetails(${service.id})">
                        <i class="fas fa-info-circle"></i>
                        Details
                    </button>
                </div>
            </div>
        `

    return card
  }

  handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase().trim()

    if (searchTerm === "") {
      this.filteredServices = [...this.services]
    } else {
      this.filteredServices = this.services.filter(
        (service) =>
          service.name.toLowerCase().includes(searchTerm) ||
          service.description.toLowerCase().includes(searchTerm) ||
          (service.category && service.category.toLowerCase().includes(searchTerm)),
      )
    }

    this.currentPage = 1
    this.displayServices()
    this.updateResultsCount()
  }

  applyFilters() {
    const categoryFilter = document.getElementById("categoryFilter").value
    const priceFilter = document.getElementById("priceFilter").value

    this.filteredServices = this.services.filter((service) => {
      // Category filter
      if (categoryFilter && service.category !== categoryFilter) {
        return false
      }

      // Price filter
      if (priceFilter) {
        const price = Number.parseFloat(service.price)
        const [min, max] = priceFilter.split("-").map((p) => p.replace("+", ""))

        if (priceFilter === "1000+") {
          if (price < 1000) return false
        } else {
          const minPrice = Number.parseFloat(min)
          const maxPrice = Number.parseFloat(max)
          if (price < minPrice || price > maxPrice) return false
        }
      }

      return true
    })

    this.applySorting()
    this.currentPage = 1
    this.displayServices()
    this.updateResultsCount()
  }

  applySorting() {
    const sortBy = document.getElementById("sortBy").value

    this.filteredServices.sort((a, b) => {
      switch (sortBy) {
        case "name":
          return a.name.localeCompare(b.name)
        case "price-low":
          return Number.parseFloat(a.price) - Number.parseFloat(b.price)
        case "price-high":
          return Number.parseFloat(b.price) - Number.parseFloat(a.price)
        case "popular":
          return (b.review_count || 0) - (a.review_count || 0)
        default:
          return 0
      }
    })
  }

  loadMoreServices() {
    this.currentPage++
    this.displayServices(true)
  }

  viewServiceDetails(serviceId) {
    const service = this.services.find((s) => s.id == serviceId)
    if (!service) return

    const modal = document.getElementById("serviceDetailModal")
    const title = document.getElementById("serviceDetailTitle")
    const content = document.getElementById("serviceDetailContent")

    title.textContent = service.name
    content.innerHTML = this.generateServiceDetailHTML(service)

    modal.classList.add("active")
    document.body.style.overflow = "hidden"
  }

  generateServiceDetailHTML(service) {
    return `
            <div class="service-detail-grid">
                <div class="service-detail-main">
                    <div class="service-detail-image">
                        <i class="fas fa-${this.getServiceIcon(service.category)}"></i>
                    </div>
                    
                    <div class="service-detail-info">
                        <div class="service-price-large">
                            <span class="price-amount">$${Number.parseFloat(service.price).toFixed(2)}</span>
                            <span class="price-unit">per ${service.unit || "unit"}</span>
                        </div>
                        
                        <div class="service-rating-large">
                            ${this.generateStars(service.rating || 4.5)}
                            <span class="rating-text">${service.rating || 4.5} out of 5 (${service.review_count || 23} reviews)</span>
                        </div>
                        
                        <div class="service-description-full">
                            <h4>Description</h4>
                            <p>${this.escapeHtml(service.description)}</p>
                        </div>
                        
                        <div class="service-specifications">
                            <h4>Specifications</h4>
                            <ul>
                                <li><strong>Category:</strong> ${this.getCategoryLabel(service.category)}</li>
                                <li><strong>Unit:</strong> ${service.unit || "piece"}</li>
                                <li><strong>Minimum Order:</strong> 1 ${service.unit || "unit"}</li>
                                <li><strong>Delivery Time:</strong> 3-5 business days</li>
                                <li><strong>Quality Grade:</strong> Premium</li>
                            </ul>
                        </div>
                        
                        <div class="service-features-full">
                            <h4>Features & Benefits</h4>
                            <div class="features-grid">
                                <div class="feature-item">
                                    <i class="fas fa-certificate"></i>
                                    <span>Quality Certified</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-leaf"></i>
                                    <span>Sustainably Sourced</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-truck"></i>
                                    <span>Fast Delivery</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-headset"></i>
                                    <span>Expert Support</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="service-detail-sidebar">
                    <div class="service-actions-panel">
                        <button class="btn btn-primary btn-full btn-large" onclick="servicesPage.bookService('${this.escapeHtml(service.name)}')">
                            <i class="fas fa-calendar-check"></i>
                            Book This Service
                        </button>
                        
                        <button class="btn btn-outline btn-full" onclick="servicesPage.addToComparison(${service.id})">
                            <i class="fas fa-balance-scale"></i>
                            Add to Compare
                        </button>
                        
                        <button class="btn btn-outline btn-full" onclick="openEnquiryModal()">
                            <i class="fas fa-question-circle"></i>
                            Ask Question
                        </button>
                    </div>
                    
                    <div class="related-services">
                        <h4>Related Services</h4>
                        <div class="related-services-list">
                            ${this.getRelatedServices(service)
                              .map(
                                (related) => `
                                <div class="related-service-item" onclick="servicesPage.viewServiceDetails(${related.id})">
                                    <i class="fas fa-${this.getServiceIcon(related.category)}"></i>
                                    <div>
                                        <h5>${this.escapeHtml(related.name)}</h5>
                                        <span>$${Number.parseFloat(related.price).toFixed(2)}</span>
                                    </div>
                                </div>
                            `,
                              )
                              .join("")}
                        </div>
                    </div>
                </div>
            </div>
        `
  }

  addToComparison(serviceId) {
    const service = this.services.find((s) => s.id == serviceId)
    if (!service) return

    if (this.comparedServices.length >= 3) {
      this.showToast("You can compare up to 3 services at a time", "info")
      return
    }

    if (this.comparedServices.find((s) => s.id == serviceId)) {
      this.showToast("Service already added to comparison", "info")
      return
    }

    this.comparedServices.push(service)
    this.updateComparisonSection()
    this.showToast(`${service.name} added to comparison`, "success")
  }

  updateComparisonSection() {
    const section = document.getElementById("comparisonSection")
    const table = document.getElementById("comparisonTable")

    if (this.comparedServices.length === 0) {
      section.style.display = "none"
      return
    }

    section.style.display = "block"
    table.innerHTML = this.generateComparisonTable()

    // Scroll to comparison section
    section.scrollIntoView({ behavior: "smooth", block: "start" })
  }

  generateComparisonTable() {
    if (this.comparedServices.length === 0) return ""

    return `
            <table class="comparison-table-grid">
                <thead>
                    <tr>
                        <th>Feature</th>
                        ${this.comparedServices
                          .map(
                            (service) => `
                            <th>
                                <div class="comparison-service-header">
                                    <i class="fas fa-${this.getServiceIcon(service.category)}"></i>
                                    <h4>${this.escapeHtml(service.name)}</h4>
                                    <button class="remove-comparison" onclick="servicesPage.removeFromComparison(${service.id})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </th>
                        `,
                          )
                          .join("")}
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Price</strong></td>
                        ${this.comparedServices
                          .map(
                            (service) => `
                            <td>$${Number.parseFloat(service.price).toFixed(2)} per ${service.unit}</td>
                        `,
                          )
                          .join("")}
                    </tr>
                    <tr>
                        <td><strong>Category</strong></td>
                        ${this.comparedServices
                          .map(
                            (service) => `
                            <td>${this.getCategoryLabel(service.category)}</td>
                        `,
                          )
                          .join("")}
                    </tr>
                    <tr>
                        <td><strong>Rating</strong></td>
                        ${this.comparedServices
                          .map(
                            (service) => `
                            <td>${this.generateStars(service.rating || 4.5)} ${service.rating || 4.5}/5</td>
                        `,
                          )
                          .join("")}
                    </tr>
                    <tr>
                        <td><strong>Description</strong></td>
                        ${this.comparedServices
                          .map(
                            (service) => `
                            <td>${this.escapeHtml(service.description.substring(0, 100))}...</td>
                        `,
                          )
                          .join("")}
                    </tr>
                    <tr>
                        <td><strong>Actions</strong></td>
                        ${this.comparedServices
                          .map(
                            (service) => `
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="servicesPage.bookService('${this.escapeHtml(service.name)}')">
                                    Book Now
                                </button>
                            </td>
                        `,
                          )
                          .join("")}
                    </tr>
                </tbody>
            </table>
        `
  }

  removeFromComparison(serviceId) {
    this.comparedServices = this.comparedServices.filter((s) => s.id != serviceId)
    this.updateComparisonSection()
  }

  clearComparison() {
    this.comparedServices = []
    this.updateComparisonSection()
  }

  shareService(serviceId) {
    const service = this.services.find((s) => s.id == serviceId)
    if (!service) return

    if (navigator.share) {
      navigator.share({
        title: service.name,
        text: service.description,
        url: window.location.href + "#service-" + serviceId,
      })
    } else {
      // Fallback: copy to clipboard
      const url = window.location.href + "#service-" + serviceId
      navigator.clipboard.writeText(url).then(() => {
        this.showToast("Service link copied to clipboard", "success")
      })
    }
  }

  bookService(serviceName) {
    // This would integrate with the booking modal from the main page
    window.parent.postMessage(
      {
        action: "bookService",
        serviceName: serviceName,
      },
      "*",
    )
  }

  // Utility methods
  getRelatedServices(service) {
    return this.services.filter((s) => s.id != service.id && s.category === service.category).slice(0, 3)
  }

  updateResultsCount() {
    const count = this.filteredServices.length
    const total = this.services.length

    // Update results count display if element exists
    const resultsCount = document.getElementById("resultsCount")
    if (resultsCount) {
      resultsCount.textContent = `Showing ${count} of ${total} services`
    }
  }

  getNoResultsHTML() {
    return `
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No Services Found</h3>
                <p>Try adjusting your search criteria or filters</p>
                <button class="btn btn-primary" onclick="servicesPage.clearFilters()">
                    <i class="fas fa-refresh"></i>
                    Clear Filters
                </button>
            </div>
        `
  }

  clearFilters() {
    document.getElementById("serviceSearch").value = ""
    document.getElementById("categoryFilter").value = ""
    document.getElementById("priceFilter").value = ""
    document.getElementById("sortBy").value = "name"

    this.filteredServices = [...this.services]
    this.currentPage = 1
    this.displayServices()
    this.updateResultsCount()
  }

  closeServiceDetailModal() {
    const modal = document.getElementById("serviceDetailModal")
    modal.classList.remove("active")
    document.body.style.overflow = ""
  }

  // Utility methods from main app
  showLoading() {
    const servicesGrid = document.getElementById("servicesGrid")
    servicesGrid.innerHTML = Array(6)
      .fill(0)
      .map(
        () => `
            <div class="service-skeleton">
                <div class="skeleton-image"></div>
                <div class="skeleton-content">
                    <div class="skeleton-title"></div>
                    <div class="skeleton-text"></div>
                    <div class="skeleton-price"></div>
                </div>
            </div>
        `,
      )
      .join("")
  }

  showError(message) {
    this.showToast(message, "error")
  }

  showToast(message, type = "info") {
    // Implementation similar to main app
    console.log(`${type.toUpperCase()}: ${message}`)
  }

  debounce(func, wait) {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
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

  getCategoryLabel(category) {
    const labels = {
      crude_oil: "Crude Oil",
      refined_oil: "Refined Oil",
      kernel_oil: "Kernel Oil",
      consultation: "Consultation",
      bulk_supply: "Bulk Supply",
    }
    return labels[category] || "General"
  }

  generateStars(rating) {
    let stars = ""
    for (let i = 1; i <= 5; i++) {
      if (i <= rating) {
        stars += '<i class="fas fa-star"></i>'
      } else if (i - 0.5 <= rating) {
        stars += '<i class="fas fa-star-half-alt"></i>'
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

  initializeFilters() {
    // Set up any initial filter states
    this.applySorting()
  }
}

// Initialize the services page
const servicesPage = new ServicesPage()

// Global functions for onclick handlers
function openBookingModal() {
  // Implementation for booking modal
}

function openEnquiryModal() {
  // Implementation for enquiry modal
}

function closeToast() {
  // Implementation for closing toast
}
