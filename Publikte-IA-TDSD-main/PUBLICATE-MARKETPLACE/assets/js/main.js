// Funciones principales de PUBLICATE

// Función para mostrar alertas
function showAlert(message, type = "success") {
  const alertDiv = document.createElement("div")
  alertDiv.className = `alert alert-${type}`
  alertDiv.textContent = message
  alertDiv.style.position = "fixed"
  alertDiv.style.top = "20px"
  alertDiv.style.right = "20px"
  alertDiv.style.zIndex = "9999"
  alertDiv.style.minWidth = "300px"
  alertDiv.style.padding = "1rem"
  alertDiv.style.borderRadius = "0.5rem"
  alertDiv.style.color = "white"
  alertDiv.style.fontWeight = "600"

  // Colores según el tipo
  switch (type) {
    case "success":
      alertDiv.style.backgroundColor = "var(--green-500)"
      break
    case "error":
      alertDiv.style.backgroundColor = "var(--red-500)"
      break
    case "warning":
      alertDiv.style.backgroundColor = "var(--yellow-500)"
      alertDiv.style.color = "var(--gray-800)"
      break
    default:
      alertDiv.style.backgroundColor = "var(--blue-500)"
  }

  document.body.appendChild(alertDiv)

  setTimeout(() => {
    if (document.body.contains(alertDiv)) {
      alertDiv.remove()
    }
  }, 5000)
}

// Función para actualizar contador del carrito
function updateCartCount() {
  fetch("api/cart.php?action=count")
    .then((response) => response.json())
    .then((data) => {
      const cartBadge = document.getElementById("cart-count")
      if (cartBadge) {
        cartBadge.textContent = data.count || 0
      }
    })
    .catch((error) => {
      console.error("Error updating cart count:", error)
    })
}

// Función para agregar al carrito
function addToCart(productId, quantity = 1) {
  fetch("api/cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "add",
      product_id: productId,
      quantity: quantity,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Producto agregado al carrito", "success")
        updateCartCount()
      } else {
        showAlert(data.message || "Error al agregar al carrito", "error")
      }
    })
    .catch((error) => {
      showAlert("Error al agregar al carrito", "error")
    })
}

// Función para remover del carrito
function removeFromCart(productId) {
  fetch("api/cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "remove",
      product_id: productId,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Producto eliminado del carrito", "success")
        updateCartCount()
        location.reload()
      } else {
        showAlert(data.message || "Error al eliminar del carrito", "error")
      }
    })
    .catch((error) => {
      showAlert("Error al eliminar del carrito", "error")
    })
}

// Función para actualizar cantidad en carrito
function updateCartQuantity(productId, quantity) {
  fetch("api/cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "update",
      product_id: productId,
      quantity: quantity,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateCartCount()
        location.reload()
      } else {
        showAlert(data.message || "Error al actualizar cantidad", "error")
      }
    })
    .catch((error) => {
      showAlert("Error al actualizar cantidad", "error")
    })
}

// Función para agregar/quitar de favoritos
function toggleFavorite(productId) {
  fetch("api/favorites.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "toggle",
      product_id: productId,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const heartIcon = document.querySelector(`[data-product-id="${productId}"] .heart-icon`)
        if (heartIcon) {
          heartIcon.style.color = data.is_favorite ? "#ef4444" : "#6b7280"
        }
        showAlert(data.is_favorite ? "Agregado a favoritos" : "Eliminado de favoritos", "success")
      } else {
        showAlert(data.message || "Error al actualizar favoritos", "error")
      }
    })
    .catch((error) => {
      showAlert("Error al actualizar favoritos", "error")
    })
}

// Función para validar formularios
function validateForm(formId) {
  const form = document.getElementById(formId)
  const inputs = form.querySelectorAll("input[required], textarea[required], select[required]")
  let isValid = true

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      input.style.borderColor = "#ef4444"
      isValid = false
    } else {
      input.style.borderColor = "#d1d5db"
    }
  })

  return isValid
}

// Función para formatear precios
function formatPrice(price) {
  return "$" + new Intl.NumberFormat("es-AR").format(price)
}

// Función para manejar subida de imágenes (versión mejorada)
function handleImageUpload(input, previewContainer, maxImages = 5) {
  const files = Array.from(input.files)
  const currentImages = previewContainer.querySelectorAll(".image-preview").length

  if (currentImages + files.length > maxImages) {
    showAlert(`Máximo ${maxImages} imágenes permitidas`, "warning")
    return
  }

  files.forEach((file, index) => {
    if (file.type.startsWith("image/")) {
      // Verificar tamaño del archivo (10MB máximo)
      const maxSize = 10 * 1024 * 1024 // 10MB
      if (file.size > maxSize) {
        showAlert(`La imagen ${file.name} es muy grande (máximo 10MB)`, "error")
        return
      }

      const reader = new FileReader()
      reader.onload = (e) => {
        const imagePreview = document.createElement("div")
        imagePreview.className = "image-preview"
        imagePreview.style.position = "relative"
        imagePreview.style.display = "inline-block"
        imagePreview.style.margin = "0.5rem"

        imagePreview.innerHTML = `
          <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 0.5rem;">
          <button type="button" onclick="this.parentElement.remove()" style="position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer;">×</button>
          ${currentImages + index === 0 ? '<span style="position: absolute; bottom: 0; left: 0; background: var(--primary-500); color: white; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 0 0.5rem 0 0;">Principal</span>' : ""}
        `

        previewContainer.appendChild(imagePreview)
      }
      reader.readAsDataURL(file)
    } else {
      showAlert(`El archivo ${file.name} no es una imagen válida`, "error")
    }
  })
}

// Función para confirmar acciones
function confirmAction(message, callback) {
  if (confirm(message)) {
    callback()
  }
}

// Modal functions
function openModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.style.display = "block"
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.style.display = "none"
  }
}

// Cerrar modal al hacer clic fuera
window.onclick = (event) => {
  if (event.target.classList.contains("modal")) {
    event.target.style.display = "none"
  }
}

// Función para buscar productos
function searchProducts(query, filters = {}) {
  const params = new URLSearchParams()
  if (query) params.append("q", query)

  Object.keys(filters).forEach((key) => {
    if (filters[key]) params.append(key, filters[key])
  })

  window.location.href = "search.php?" + params.toString()
}

// Función para filtrar productos en tiempo real
function filterProducts() {
  const searchInput = document.getElementById("search-input")
  const categorySelect = document.getElementById("category-filter")
  const priceSelect = document.getElementById("price-filter")
  const sortSelect = document.getElementById("sort-filter")

  if (searchInput && categorySelect && priceSelect && sortSelect) {
    const filters = {
      category: categorySelect.value,
      price_range: priceSelect.value,
      sort: sortSelect.value,
    }

    searchProducts(searchInput.value, filters)
  }
}

// Función para cargar más productos (paginación)
function loadMoreProducts(page) {
  const currentUrl = new URL(window.location)
  currentUrl.searchParams.set("page", page)
  window.location.href = currentUrl.toString()
}

// Función para actualizar perfil
function updateProfile(formData) {
  fetch("api/profile.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Perfil actualizado correctamente", "success")
      } else {
        showAlert(data.message || "Error al actualizar perfil", "error")
      }
    })
    .catch((error) => {
      showAlert("Error al actualizar perfil", "error")
    })
}

// Función para recargar wallet
function rechargeWallet(amount) {
  fetch("api/wallet.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "recharge",
      amount: amount,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Wallet recargado correctamente", "success")
        setTimeout(() => {
          location.reload()
        }, 1500)
      } else {
        showAlert(data.message || "Error al recargar wallet", "error")
      }
    })
    .catch((error) => {
      showAlert("Error al recargar wallet", "error")
    })
}

// Inicialización cuando se carga la página
document.addEventListener("DOMContentLoaded", () => {
  // Actualizar contador del carrito al cargar la página
  updateCartCount()

  // Manejar formularios de búsqueda
  const searchForms = document.querySelectorAll(".search-form")
  searchForms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const input = form.querySelector('input[name="q"]')
      if (!input.value.trim()) {
        e.preventDefault()
        showAlert("Ingresa un término de búsqueda", "warning")
      }
    })
  })

  // Manejar botones de cantidad en carrito
  const quantityButtons = document.querySelectorAll(".quantity-btn")
  quantityButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const productId = this.dataset.productId
      const action = this.dataset.action
      const quantitySpan = document.querySelector(`[data-quantity="${productId}"]`)
      let currentQuantity = Number.parseInt(quantitySpan.textContent)

      if (action === "increase") {
        currentQuantity++
      } else if (action === "decrease" && currentQuantity > 1) {
        currentQuantity--
      }

      updateCartQuantity(productId, currentQuantity)
    })
  })

  // Auto-hide alerts
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    setTimeout(() => {
      if (alert.parentNode) {
        alert.remove()
      }
    }, 5000)
  })
})

// Función para manejar errores de imágenes
function handleImageError(img) {
  img.src = "/placeholder.svg?height=300&width=300"
  img.onerror = null
}

// Función para copiar al portapapeles
function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(
    () => {
      showAlert("Copiado al portapapeles", "success")
    },
    () => {
      showAlert("Error al copiar", "error")
    },
  )
}

// Función para compartir producto
function shareProduct(productId, title) {
  if (navigator.share) {
    navigator.share({
      title: title,
      url: window.location.origin + "/product.php?id=" + productId,
    })
  } else {
    copyToClipboard(window.location.origin + "/product.php?id=" + productId)
  }
}

// Función para reportar producto
function reportProduct(productId) {
  const reason = prompt("¿Por qué quieres reportar este producto?")
  if (reason) {
    fetch("api/report.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        product_id: productId,
        reason: reason,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showAlert("Reporte enviado correctamente", "success")
        } else {
          showAlert(data.message || "Error al enviar reporte", "error")
        }
      })
      .catch((error) => {
        showAlert("Error al enviar reporte", "error")
      })
  }
}
