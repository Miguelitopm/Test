// JavaScript para la página de compras

document.addEventListener("DOMContentLoaded", () => {
  // Inicializar funcionalidades
  initializePurchases()
  setupEventListeners()
})

function initializePurchases() {
  // Actualizar contadores si es necesario
  updatePurchaseStats()

  // Configurar filtros automáticos
  setupAutoFilters()

  // Configurar tooltips si existen
  setupTooltips()
}

function setupEventListeners() {
  // Escuchar cambios en los filtros
  const statusFilter = document.getElementById("status")
  const dateFilter = document.getElementById("date")

  if (statusFilter) {
    statusFilter.addEventListener("change", function () {
      // Auto-submit del formulario cuando cambia el filtro
      if (this.value !== "") {
        this.form.submit()
      }
    })
  }

  if (dateFilter) {
    dateFilter.addEventListener("change", function () {
      // Auto-submit del formulario cuando cambia el filtro
      if (this.value !== "") {
        this.form.submit()
      }
    })
  }

  // Configurar botones de acción
  setupActionButtons()
}

function setupActionButtons() {
  // Configurar botones de ver detalles
  const detailButtons = document.querySelectorAll('[onclick*="viewPurchaseDetails"]')
  detailButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()
      const orderId = this.getAttribute("onclick").match(/\d+/)[0]
      viewPurchaseDetails(orderId)
    })
  })

  // Configurar botones de contactar vendedor
  const contactButtons = document.querySelectorAll('[onclick*="contactSeller"]')
  contactButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()
      const email = this.getAttribute("onclick").match(/'([^']+)'/)[1]
      contactSeller(email)
    })
  })
}

function setupAutoFilters() {
  // Guardar filtros en localStorage para persistencia
  const form = document.querySelector(".filters-form")
  if (form) {
    const formData = new FormData(form)
    const filters = {}

    for (const [key, value] of formData.entries()) {
      if (value) {
        filters[key] = value
      }
    }

    localStorage.setItem("purchaseFilters", JSON.stringify(filters))
  }
}

function setupTooltips() {
  // Configurar tooltips para estados
  const statusBadges = document.querySelectorAll(".status-badge")
  statusBadges.forEach((badge) => {
    const status = badge.classList[1].replace("status-", "")
    let tooltipText = ""

    switch (status) {
      case "processing":
        tooltipText = "La compra está siendo procesada por el vendedor"
        break
      case "shipped":
        tooltipText = "El producto ha sido enviado y está en camino"
        break
      case "delivered":
        tooltipText = "El producto ha sido entregado exitosamente"
        break
      case "cancelled":
        tooltipText = "La compra fue cancelada"
        break
    }

    if (tooltipText) {
      badge.setAttribute("title", tooltipText)
    }
  })
}

function viewPurchaseDetails(orderId) {
  // Mostrar loading
  showLoading()

  // Hacer petición AJAX para obtener detalles
  fetch(`api/purchase-details.php?order_id=${orderId}`)
    .then((response) => response.json())
    .then((data) => {
      hideLoading()

      if (data.success) {
        displayPurchaseDetails(data.purchase)
      } else {
        showAlert(data.message || "Error al cargar los detalles", "error")
      }
    })
    .catch((error) => {
      hideLoading()
      console.error("Error:", error)
      showAlert("Error al cargar los detalles de la compra", "error")
    })
}

function displayPurchaseDetails(purchase) {
  const modalBody = document.getElementById("purchaseModalBody")

  const statusText = {
    processing: "Procesando",
    shipped: "Enviado",
    delivered: "Entregado",
    cancelled: "Cancelado",
  }

  const statusClass = {
    processing: "warning",
    shipped: "info",
    delivered: "success",
    cancelled: "error",
  }

  modalBody.innerHTML = `
        <div class="purchase-detail-card">
            <div class="detail-header">
                <h3>${purchase.product_title}</h3>
                <span class="status-badge status-${purchase.status}">
                    ${statusText[purchase.status] || purchase.status}
                </span>
            </div>
            
            <div class="detail-grid">
                <div class="detail-section">
                    <h4><i class="fas fa-box"></i> Información del Producto</h4>
                    <div class="detail-item">
                        <strong>Título:</strong> ${purchase.product_title}
                    </div>
                    <div class="detail-item">
                        <strong>Descripción:</strong> ${purchase.product_description || "No disponible"}
                    </div>
                    <div class="detail-item">
                        <strong>Precio unitario:</strong> $${Number.parseFloat(purchase.product_price).toFixed(2)}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4><i class="fas fa-user"></i> Información del Vendedor</h4>
                    <div class="detail-item">
                        <strong>Nombre:</strong> ${purchase.seller_name}
                    </div>
                    <div class="detail-item">
                        <strong>Email:</strong> ${purchase.seller_email}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4><i class="fas fa-receipt"></i> Detalles de la Compra</h4>
                    <div class="detail-item">
                        <strong>ID de Orden:</strong> #${purchase.order_id}
                    </div>
                    <div class="detail-item">
                        <strong>Cantidad:</strong> ${purchase.quantity}
                    </div>
                    <div class="detail-item">
                        <strong>Total pagado:</strong> $${Number.parseFloat(purchase.total_amount).toFixed(2)}
                    </div>
                    <div class="detail-item">
                        <strong>Fecha de compra:</strong> ${formatDate(purchase.created_at)}
                    </div>
                    <div class="detail-item">
                        <strong>Estado:</strong> <span class="status-badge status-${purchase.status}">${statusText[purchase.status]}</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-actions">
                <button class="btn btn-primary" onclick="contactSeller('${purchase.seller_email}')">
                    <i class="fas fa-envelope"></i> Contactar Vendedor
                </button>
                <a href="product.php?id=${purchase.product_id}" class="btn btn-outline" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Ver Producto
                </a>
                ${
                  purchase.status === "processing"
                    ? `
                    <button class="btn btn-danger" onclick="cancelPurchase(${purchase.order_id})">
                        <i class="fas fa-times"></i> Cancelar Compra
                    </button>
                `
                    : ""
                }
                ${
                  purchase.status === "delivered"
                    ? `
                    <button class="btn btn-success" onclick="markAsReceived(${purchase.order_id})">
                        <i class="fas fa-check"></i> Confirmar Recepción
                    </button>
                `
                    : ""
                }
            </div>
        </div>
    `

  openModal("purchaseModal")
}

function contactSeller(email) {
  // Crear enlace mailto
  const subject = encodeURIComponent("Consulta sobre producto comprado en PUBLICATE")
  const body = encodeURIComponent("Hola,\n\nTe contacto respecto a un producto que compré en PUBLICATE.\n\nSaludos.")

  const mailtoLink = `mailto:${email}?subject=${subject}&body=${body}`

  // Intentar abrir cliente de correo
  window.location.href = mailtoLink

  // Mostrar mensaje alternativo
  setTimeout(() => {
    showAlert(
      "Se abrió tu cliente de correo. Si no se abrió automáticamente, puedes contactar al vendedor en: " + email,
      "info",
    )
  }, 1000)
}

function cancelPurchase(orderId) {
  if (!confirm("¿Estás seguro de que quieres cancelar esta compra?")) {
    return
  }

  showLoading()

  fetch("api/cancel-purchase.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      order_id: orderId,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      hideLoading()

      if (data.success) {
        showAlert("Compra cancelada exitosamente", "success")
        setTimeout(() => {
          location.reload()
        }, 1500)
      } else {
        showAlert(data.message || "Error al cancelar la compra", "error")
      }
    })
    .catch((error) => {
      hideLoading()
      console.error("Error:", error)
      showAlert("Error al cancelar la compra", "error")
    })
}

function markAsReceived(orderId) {
  if (!confirm("¿Confirmas que has recibido el producto correctamente?")) {
    return
  }

  showLoading()

  fetch("api/confirm-delivery.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      order_id: orderId,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      hideLoading()

      if (data.success) {
        showAlert("Recepción confirmada exitosamente", "success")
        setTimeout(() => {
          location.reload()
        }, 1500)
      } else {
        showAlert(data.message || "Error al confirmar la recepción", "error")
      }
    })
    .catch((error) => {
      hideLoading()
      console.error("Error:", error)
      showAlert("Error al confirmar la recepción", "error")
    })
}

function updatePurchaseStats() {
  // Actualizar estadísticas en tiempo real si es necesario
  const statCards = document.querySelectorAll(".stat-card")

  statCards.forEach((card) => {
    // Agregar animación de conteo
    const numberElement = card.querySelector("h3")
    if (numberElement) {
      animateNumber(numberElement)
    }
  })
}

function animateNumber(element) {
  const finalNumber = Number.parseInt(element.textContent.replace(/[^\d]/g, ""))
  const duration = 1000
  const steps = 30
  const increment = finalNumber / steps
  let current = 0

  const timer = setInterval(() => {
    current += increment
    if (current >= finalNumber) {
      current = finalNumber
      clearInterval(timer)
    }

    // Mantener formato original
    if (element.textContent.includes("$")) {
      element.textContent = "$" + Math.floor(current).toLocaleString()
    } else {
      element.textContent = Math.floor(current).toLocaleString()
    }
  }, duration / steps)
}

function formatDate(dateString) {
  const date = new Date(dateString)
  const options = {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }
  return date.toLocaleDateString("es-ES", options)
}

function showLoading() {
  // Crear overlay de loading si no existe
  let loadingOverlay = document.getElementById("loadingOverlay")
  if (!loadingOverlay) {
    loadingOverlay = document.createElement("div")
    loadingOverlay.id = "loadingOverlay"
    loadingOverlay.innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando...</p>
            </div>
        `
    loadingOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        `

    const spinner = loadingOverlay.querySelector(".loading-spinner")
    spinner.style.cssText = `
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        `

    document.body.appendChild(loadingOverlay)
  }

  loadingOverlay.style.display = "flex"
}

function hideLoading() {
  const loadingOverlay = document.getElementById("loadingOverlay")
  if (loadingOverlay) {
    loadingOverlay.style.display = "none"
  }
}

// Función para exportar compras (funcionalidad adicional)
function exportPurchases() {
  showLoading()

  fetch("api/export-purchases.php")
    .then((response) => response.blob())
    .then((blob) => {
      hideLoading()

      // Crear enlace de descarga
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement("a")
      a.href = url
      a.download = `mis-compras-${new Date().toISOString().split("T")[0]}.csv`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      window.URL.revokeObjectURL(url)

      showAlert("Compras exportadas exitosamente", "success")
    })
    .catch((error) => {
      hideLoading()
      console.error("Error:", error)
      showAlert("Error al exportar las compras", "error")
    })
}

// Función para buscar compras
function searchPurchases(query) {
  const purchaseCards = document.querySelectorAll(".purchase-card")

  purchaseCards.forEach((card) => {
    const title = card.querySelector(".purchase-header h3").textContent.toLowerCase()
    const seller = card.querySelector(".purchase-details p:nth-child(1)").textContent.toLowerCase()

    if (title.includes(query.toLowerCase()) || seller.includes(query.toLowerCase())) {
      card.style.display = "grid"
    } else {
      card.style.display = "none"
    }
  })
}

// Configurar búsqueda en tiempo real si existe campo de búsqueda
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("searchPurchases")
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      searchPurchases(this.value)
    })
  }
})

// Funciones auxiliares
function showAlert(message, type) {
  // Crear alerta personalizada
  const alertDiv = document.createElement("div")
  alertDiv.className = `alert alert-${type}`
  alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        max-width: 400px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.3s ease-out;
    `

  // Colores según el tipo
  const colors = {
    success: "#28a745",
    error: "#dc3545",
    warning: "#ffc107",
    info: "#17a2b8",
  }

  alertDiv.style.backgroundColor = colors[type] || colors.info
  alertDiv.textContent = message

  document.body.appendChild(alertDiv)

  // Remover después de 5 segundos
  setTimeout(() => {
    alertDiv.style.animation = "slideOut 0.3s ease-in"
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.parentNode.removeChild(alertDiv)
      }
    }, 300)
  }, 5000)
}

function openModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.style.display = "block"
    document.body.style.overflow = "hidden"
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.style.display = "none"
    document.body.style.overflow = "auto"
  }
}

// Cerrar modal al hacer clic fuera
window.addEventListener("click", (event) => {
  const modals = document.querySelectorAll(".modal")
  modals.forEach((modal) => {
    if (event.target === modal) {
      closeModal(modal.id)
    }
  })
})

// Agregar estilos de animación
const style = document.createElement("style")
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`
document.head.appendChild(style)
