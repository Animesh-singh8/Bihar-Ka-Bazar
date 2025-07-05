// Main JavaScript file for Bihar ka Bazar

// Initialize AOS (Animate On Scroll)
document.addEventListener("DOMContentLoaded", () => {
  AOS.init({
    duration: 1000,
    easing: "ease-in-out",
    once: true,
    mirror: false,
  })
})

// Toast notification system
function showToast(message, type = "info", duration = 5000) {
  // Create toast container if it doesn't exist
  let toastContainer = document.querySelector(".toast-container")
  if (!toastContainer) {
    toastContainer = document.createElement("div")
    toastContainer.className = "toast-container"
    document.body.appendChild(toastContainer)
  }

  // Create toast element
  const toastId = "toast-" + Date.now()
  const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type === "success" ? "success" : type === "danger" ? "danger" : type === "warning" ? "warning" : "primary"} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === "success" ? "check-circle" : type === "danger" ? "exclamation-circle" : type === "warning" ? "exclamation-triangle" : "info-circle"} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `

  toastContainer.insertAdjacentHTML("beforeend", toastHTML)

  const toastElement = document.getElementById(toastId)
  const toast = new bootstrap.Toast(toastElement, {
    autohide: true,
    delay: duration,
  })

  toast.show()

  // Remove toast element after it's hidden
  toastElement.addEventListener("hidden.bs.toast", () => {
    toastElement.remove()
  })
}

// Form validation
function validateForm(formId) {
  const form = document.getElementById(formId)
  if (!form) return false

  let isValid = true
  const requiredFields = form.querySelectorAll("[required]")

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      field.classList.add("is-invalid")
      isValid = false
    } else {
      field.classList.remove("is-invalid")
      field.classList.add("is-valid")
    }
  })

  // Email validation
  const emailFields = form.querySelectorAll('input[type="email"]')
  emailFields.forEach((field) => {
    if (field.value && !isValidEmail(field.value)) {
      field.classList.add("is-invalid")
      isValid = false
    }
  })

  // Phone validation
  const phoneFields = form.querySelectorAll('input[type="tel"]')
  phoneFields.forEach((field) => {
    if (field.value && !isValidPhone(field.value)) {
      field.classList.add("is-invalid")
      isValid = false
    }
  })

  return isValid
}

// Email validation
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

// Phone validation
function isValidPhone(phone) {
  const phoneRegex = /^[+]?[1-9][\d]{0,15}$/
  return phoneRegex.test(phone.replace(/[\s\-$$$$]/g, ""))
}

// Loading state management
function setLoadingState(element, isLoading = true) {
  if (isLoading) {
    element.disabled = true
    element.innerHTML = '<span class="loading-spinner me-2"></span>Loading...'
  } else {
    element.disabled = false
    element.innerHTML = element.getAttribute("data-original-text") || "Submit"
  }
}

// Image lazy loading
function lazyLoadImages() {
  const images = document.querySelectorAll("img[data-src]")
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target
        img.src = img.dataset.src
        img.classList.remove("lazy")
        imageObserver.unobserve(img)
      }
    })
  })

  images.forEach((img) => imageObserver.observe(img))
}

// Initialize lazy loading
document.addEventListener("DOMContentLoaded", lazyLoadImages)

// Smooth scrolling for anchor links
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

// Search functionality
function initializeSearch() {
  const searchInput = document.querySelector('input[name="search"]')
  if (searchInput) {
    let searchTimeout
    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        // Implement search suggestions here
        console.log("Searching for:", this.value)
      }, 300)
    })
  }
}

// Initialize search
document.addEventListener("DOMContentLoaded", initializeSearch)

// Price formatting
function formatPrice(amount, currency = "₹") {
  return (
    currency +
    Number.parseFloat(amount).toLocaleString("en-IN", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  )
}

// Date formatting
function formatDate(dateString) {
  const options = {
    year: "numeric",
    month: "long",
    day: "numeric",
  }
  return new Date(dateString).toLocaleDateString("en-IN", options)
}

// Time ago function
function timeAgo(dateString) {
  const now = new Date()
  const date = new Date(dateString)
  const diffInSeconds = Math.floor((now - date) / 1000)

  if (diffInSeconds < 60) return "just now"
  if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + " minutes ago"
  if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + " hours ago"
  if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + " days ago"
  if (diffInSeconds < 31536000) return Math.floor(diffInSeconds / 2592000) + " months ago"
  return Math.floor(diffInSeconds / 31536000) + " years ago"
}

// Local storage helpers
const Storage = {
  set: (key, value) => {
    try {
      localStorage.setItem(key, JSON.stringify(value))
    } catch (e) {
      console.error("Error saving to localStorage:", e)
    }
  },

  get: (key) => {
    try {
      const item = localStorage.getItem(key)
      return item ? JSON.parse(item) : null
    } catch (e) {
      console.error("Error reading from localStorage:", e)
      return null
    }
  },

  remove: (key) => {
    try {
      localStorage.removeItem(key)
    } catch (e) {
      console.error("Error removing from localStorage:", e)
    }
  },
}

// API helper functions
const API = {
  get: async (url) => {
    try {
      const response = await fetch(url)
      if (!response.ok) throw new Error("Network response was not ok")
      return await response.json()
    } catch (error) {
      console.error("API GET error:", error)
      throw error
    }
  },

  post: async (url, data) => {
    try {
      const response = await fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      })
      if (!response.ok) throw new Error("Network response was not ok")
      return await response.json()
    } catch (error) {
      console.error("API POST error:", error)
      throw error
    }
  },
}

// Image upload preview
function previewImage(input, previewElement) {
  if (input.files && input.files[0]) {
    const reader = new FileReader()
    reader.onload = (e) => {
      previewElement.src = e.target.result
      previewElement.style.display = "block"
    }
    reader.readAsDataURL(input.files[0])
  }
}

// Copy to clipboard
function copyToClipboard(text) {
  if (navigator.clipboard) {
    navigator.clipboard
      .writeText(text)
      .then(() => {
        showToast("Copied to clipboard!", "success")
      })
      .catch((err) => {
        console.error("Failed to copy: ", err)
        showToast("Failed to copy to clipboard", "danger")
      })
  } else {
    // Fallback for older browsers
    const textArea = document.createElement("textarea")
    textArea.value = text
    document.body.appendChild(textArea)
    textArea.select()
    try {
      document.execCommand("copy")
      showToast("Copied to clipboard!", "success")
    } catch (err) {
      console.error("Failed to copy: ", err)
      showToast("Failed to copy to clipboard", "danger")
    }
    document.body.removeChild(textArea)
  }
}

// Debounce function
function debounce(func, wait, immediate) {
  let timeout
  return function executedFunction() {
    
    const args = arguments
    const later = () => {
      timeout = null
      if (!immediate) func.apply(this, args)
    }
    const callNow = immediate && !timeout
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
    if (callNow) func.apply(this, args)
  }
}

// Throttle function
function throttle(func, limit) {
  let inThrottle
  return function () {
    const args = arguments
    
    if (!inThrottle) {
      func.apply(this, args)
      inThrottle = true
      setTimeout(() => (inThrottle = false), limit)
    }
  }
}

// Initialize tooltips and popovers
document.addEventListener("DOMContentLoaded", () => {
  // Initialize Bootstrap tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))

  // Initialize Bootstrap popovers
  const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  popoverTriggerList.map((popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl))
})

// Handle network status
window.addEventListener("online", () => {
  showToast("Connection restored!", "success")
})

window.addEventListener("offline", () => {
  showToast("Connection lost. Some features may not work.", "warning")
})

// Performance monitoring
function measurePerformance(name, fn) {
  const start = performance.now()
  const result = fn()
  const end = performance.now()
  console.log(`${name} took ${end - start} milliseconds`)
  return result
}

// Error handling
window.addEventListener("error", (e) => {
  console.error("Global error:", e.error)
  // You can send error reports to your server here
})

window.addEventListener("unhandledrejection", (e) => {
  console.error("Unhandled promise rejection:", e.reason)
  // You can send error reports to your server here
})

// Export functions for use in other scripts
window.BiharKaBazar = {
  showToast,
  validateForm,
  setLoadingState,
  formatPrice,
  formatDate,
  timeAgo,
  Storage,
  API,
  previewImage,
  copyToClipboard,
  debounce,
  throttle,
}
