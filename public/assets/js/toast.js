// Toast notification system
class ToastManager {
  constructor() {
    this.container = this.createContainer()
    this.toasts = []
  }

  createContainer() {
    let container = document.getElementById("toastContainer")
    if (!container) {
      container = document.createElement("div")
      container.id = "toastContainer"
      container.className = "toast-container"
      document.body.appendChild(container)
    }
    return container
  }

  show(message, type = "info", duration = 5000) {
    const toast = this.createToast(message, type, duration)
    this.container.appendChild(toast)
    this.toasts.push(toast)

    // Trigger animation
    setTimeout(() => {
      toast.classList.add("show")
    }, 10)

    // Auto remove
    if (duration > 0) {
      setTimeout(() => {
        this.remove(toast)
      }, duration)
    }

    return toast
  }

  createToast(message, type, duration) {
    const toast = document.createElement("div")
    toast.className = `toast toast-${type}`

    const icon = this.getIcon(type)
    const closeBtn =
      duration > 0
        ? ""
        : '<button class="toast-close" onclick="toastManager.remove(this.parentElement)">&times;</button>'

    toast.innerHTML = `
      <div class="toast-content">
        <div class="toast-icon">${icon}</div>
        <div class="toast-message">${message}</div>
        ${closeBtn}
      </div>
      <div class="toast-progress"></div>
    `

    // Add click to dismiss
    toast.addEventListener("click", () => {
      this.remove(toast)
    })

    // Add progress bar animation
    if (duration > 0) {
      const progressBar = toast.querySelector(".toast-progress")
      progressBar.style.animationDuration = `${duration}ms`
    }

    return toast
  }

  getIcon(type) {
    const icons = {
      success: '<i class="fas fa-check-circle"></i>',
      error: '<i class="fas fa-exclamation-circle"></i>',
      warning: '<i class="fas fa-exclamation-triangle"></i>',
      info: '<i class="fas fa-info-circle"></i>',
    }
    return icons[type] || icons.info
  }

  remove(toast) {
    if (!toast || !toast.parentElement) return

    toast.classList.add("removing")

    setTimeout(() => {
      if (toast.parentElement) {
        toast.parentElement.removeChild(toast)
      }

      const index = this.toasts.indexOf(toast)
      if (index > -1) {
        this.toasts.splice(index, 1)
      }
    }, 300)
  }

  clear() {
    this.toasts.forEach((toast) => this.remove(toast))
  }

  success(message, duration = 5000) {
    return this.show(message, "success", duration)
  }

  error(message, duration = 7000) {
    return this.show(message, "error", duration)
  }

  warning(message, duration = 6000) {
    return this.show(message, "warning", duration)
  }

  info(message, duration = 5000) {
    return this.show(message, "info", duration)
  }
}

// Create global instance
const toastManager = new ToastManager()

// Global function for easy access
function showToast(message, type = "info", duration = 5000) {
  return toastManager.show(message, type, duration)
}

// Add CSS styles
const toastStyles = `
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 10000;
  pointer-events: none;
}

.toast {
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  margin-bottom: 10px;
  min-width: 300px;
  max-width: 500px;
  opacity: 0;
  transform: translateX(100%);
  transition: all 0.3s ease;
  pointer-events: auto;
  position: relative;
  overflow: hidden;
  cursor: pointer;
}

.toast.show {
  opacity: 1;
  transform: translateX(0);
}

.toast.removing {
  opacity: 0;
  transform: translateX(100%);
}

.toast-content {
  display: flex;
  align-items: center;
  padding: 15px;
  gap: 12px;
}

.toast-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.toast-message {
  flex: 1;
  font-size: 14px;
  line-height: 1.4;
  color: #333;
}

.toast-close {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s;
}

.toast-close:hover {
  background-color: rgba(0, 0, 0, 0.1);
}

.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background: rgba(255, 255, 255, 0.3);
  animation: progress linear;
  transform-origin: left;
}

@keyframes progress {
  from { transform: scaleX(1); }
  to { transform: scaleX(0); }
}

.toast-success {
  border-left: 4px solid #4caf50;
}

.toast-success .toast-icon {
  color: #4caf50;
}

.toast-error {
  border-left: 4px solid #f44336;
}

.toast-error .toast-icon {
  color: #f44336;
}

.toast-warning {
  border-left: 4px solid #ff9800;
}

.toast-warning .toast-icon {
  color: #ff9800;
}

.toast-info {
  border-left: 4px solid #2196f3;
}

.toast-info .toast-icon {
  color: #2196f3;
}

@media (max-width: 768px) {
  .toast-container {
    top: 10px;
    right: 10px;
    left: 10px;
  }
  
  .toast {
    min-width: auto;
    max-width: none;
  }
}
`

// Inject styles
const styleSheet = document.createElement("style")
styleSheet.textContent = toastStyles
document.head.appendChild(styleSheet)
