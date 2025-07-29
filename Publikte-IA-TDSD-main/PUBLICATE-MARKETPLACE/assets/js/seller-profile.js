// Seller Profile JavaScript

function showAlert(message, type) {
  const alertContainer = document.createElement("div")
  alertContainer.className = `alert alert-${type}`
  alertContainer.textContent = message

  document.body.appendChild(alertContainer)

  setTimeout(() => {
    if (document.body.contains(alertContainer)) {
      document.body.removeChild(alertContainer)
    }
  }, 3000)
}

document.addEventListener("DOMContentLoaded", () => {
  // Load seller profile data
  loadSellerProfile()

  // Load listings
  loadListings("active")

  // Set up tab switching
  setupTabs()

  // Set up modal
  setupModal()
})

// Load seller profile data
function loadSellerProfile() {
  fetch("../../api/seller-profile.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update profile information
        document.getElementById("seller-name").textContent = data.profile.full_name
        document.getElementById("seller-rating").textContent = data.profile.rating.toFixed(1)
        document.getElementById("reviews-count").textContent = `(${data.profile.total_reviews} reseñas)`
        document.getElementById("seller-location").textContent = data.profile.location
        document.getElementById("seller-since").textContent =
          `Vendedor desde ${new Date(data.profile.member_since).getFullYear()}`
        document.getElementById("wallet-balance").textContent = formatPrice(data.profile.wallet_balance)
        document.getElementById("total-sales").textContent = data.profile.total_sales
        document.getElementById("active-listings").textContent = data.profile.active_listings

        // Update counts in tabs
        document.getElementById("active-count").textContent = data.counts.active || 0
        document.getElementById("completed-count").textContent = data.counts.completed || 0
        document.getElementById("archived-count").textContent = data.counts.archived || 0
        document.getElementById("deleted-count").textContent = data.counts.deleted || 0

        // Update avatar
        if (data.profile.avatar) {
          document.getElementById("profile-image").src = data.profile.avatar
          document.getElementById("profile-image").style.display = "block"
          document.getElementById("avatar-fallback").style.display = "none"
        } else {
          document.getElementById("profile-image").style.display = "none"
          document.getElementById("avatar-fallback").style.display = "flex"
          document.getElementById("avatar-fallback").textContent = getInitials(data.profile.full_name)
        }
      } else {
        showAlert(data.message || "Error al cargar el perfil", "error")
      }
    })
    .catch((error) => {
      console.error("Error loading profile:", error)
      showAlert("Error al cargar el perfil", "error")
    })
}

// Load listings based on status
function loadListings(status) {
  const container = document.getElementById(`${status}-listings-container`)
  if (!container) {
    console.error(`Container not found: ${status}-listings-container`)
    return
  }

  container.innerHTML = '<div class="loading-spinner">Cargando...</div>'

  fetch(`../../api/seller-listings.php?status=${status}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        if (data.listings.length > 0) {
          container.innerHTML = ""
          data.listings.forEach((listing) => {
            container.appendChild(createListingCard(listing, status))
          })
        } else {
          container.innerHTML = createEmptyState(status)
        }
      } else {
        container.innerHTML = `<div class="alert alert-error">${data.message || "Error al cargar los productos"}</div>`
      }
    })
    .catch((error) => {
      console.error(`Error loading ${status} listings:`, error)
      container.innerHTML = '<div class="alert alert-error">Error al cargar los productos</div>'
    })
}

// Create a listing card
function createListingCard(listing, status) {
  const card = document.createElement("div")
  card.className = "listing-card"

  // Status-specific styling
  let statusBadge = ""
  let actions = ""

  switch (status) {
    case "active":
      statusBadge = `<span class="status-badge status-active">Activo</span>`
      actions = `
                <button class="btn" onclick="editListing(${listing.id})">Editar</button>
                <button class="btn" onclick="archiveListing(${listing.id})">Archivar</button>
                <button class="btn btn-destructive" onclick="deleteListing(${listing.id})">Eliminar</button>
            `
      break
    case "completed":
      statusBadge = `<span class="status-badge status-completed">Vendido</span>`
      actions = "" // No actions for completed listings
      break
    case "archived":
      statusBadge = `<span class="status-badge status-archived">Archivado</span>`
      actions = `
                <button class="btn" onclick="reactivateListing(${listing.id})">Reactivar</button>
                <button class="btn btn-destructive" onclick="deleteListing(${listing.id})">Eliminar</button>
            `
      break
    case "deleted":
      statusBadge = `<span class="status-badge status-deleted">Eliminado</span>`
      actions = "" // No actions for deleted listings
      break
  }

  // Ensure we have a valid image URL
  const imageUrl = listing.image_url || "../../assets/images/placeholder.jpg"
  const title = listing.title || "Sin título"
  const price = listing.price || 0
  const views = listing.views || 0
  const likes = listing.likes || 0
  const createdAt = listing.created_at || new Date().toISOString()

  card.innerHTML = `
        <div class="listing-image">
            <img src="${imageUrl}" alt="${title}" onerror="this.src='../../assets/images/placeholder.jpg'; this.onerror=null;">
        </div>
        <div class="listing-info">
            <h3 class="listing-title">${title}</h3>
            <p class="listing-price">${formatPrice(price)}</p>
            <div class="listing-meta">
                ${statusBadge}
                <span>${views} vistas</span>
                <span>${likes} me gusta</span>
                <span>Publicado: ${formatDate(createdAt)}</span>
            </div>
        </div>
        <div class="listing-actions">
            ${actions}
        </div>
    `

  return card
}

// Create empty state message
function createEmptyState(status) {
  let icon, title, message

  switch (status) {
    case "active":
      icon =
        '<svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20"><path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/><path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707L16 7.586A1 1 0 0015.414 7H14z"/></svg>'
      title = "No tienes publicaciones activas"
      message = "Crea tu primera publicación para comenzar a vender"
      break
    case "completed":
      icon =
        '<svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
      title = "No tienes ventas completadas"
      message = "Tus ventas completadas aparecerán aquí"
      break
    case "archived":
      icon =
        '<svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/><path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>'
      title = "No tienes publicaciones archivadas"
      message = "Las publicaciones que archives aparecerán aquí"
      break
    case "deleted":
      icon =
        '<svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>'
      title = "No tienes publicaciones eliminadas"
      message = "Las publicaciones que elimines aparecerán aquí"
      break
  }

  return `
        <div class="empty-state">
            ${icon}
            <h3>${title}</h3>
            <p>${message}</p>
        </div>
    `
}

// Set up tab switching
function setupTabs() {
  const tabTriggers = document.querySelectorAll(".tab-trigger")
  const tabContents = document.querySelectorAll(".tab-content")

  tabTriggers.forEach((trigger) => {
    trigger.addEventListener("click", () => {
      const tabName = trigger.getAttribute("data-tab")

      // Update active tab trigger
      tabTriggers.forEach((t) => t.classList.remove("active"))
      trigger.classList.add("active")

      // Update active tab content
      tabContents.forEach((content) => content.classList.remove("active"))
      const targetTab = document.getElementById(`${tabName}-tab`)
      if (targetTab) {
        targetTab.classList.add("active")
      }

      // Load content if not already loaded
      if (targetTab && !targetTab.getAttribute("data-loaded")) {
        loadListings(tabName)
        targetTab.setAttribute("data-loaded", "true")
      }
    })
  })
}

// Set up modal
function setupModal() {
  const modal = document.getElementById("confirmModal")
  const closeBtn = document.querySelector(".modal-close")
  const cancelBtn = document.getElementById("modal-cancel")

  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      modal.style.display = "none"
    })
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      modal.style.display = "none"
    })
  }

  window.addEventListener("click", (event) => {
    if (event.target === modal) {
      modal.style.display = "none"
    }
  })
}

// Show confirmation modal
function showConfirmModal(title, message, confirmCallback) {
  const modal = document.getElementById("confirmModal")
  const modalTitle = document.getElementById("modal-title")
  const modalMessage = document.getElementById("modal-message")
  const confirmBtn = document.getElementById("modal-confirm")

  if (!modal || !modalTitle || !modalMessage || !confirmBtn) {
    console.error("Modal elements not found")
    return
  }

  modalTitle.textContent = title
  modalMessage.textContent = message

  // Remove previous event listener
  const newConfirmBtn = confirmBtn.cloneNode(true)
  confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn)

  // Add new event listener
  newConfirmBtn.addEventListener("click", () => {
    confirmCallback()
    modal.style.display = "none"
  })

  modal.style.display = "block"
}

// Listing actions
function editListing(id) {
  window.location.href = `../../edit-listing.php?id=${id}`
}

function archiveListing(id) {
  showConfirmModal(
    "Archivar publicación",
    "¿Estás seguro de que deseas archivar esta publicación? Ya no será visible para los compradores.",
    () => {
      updateListingStatus(id, "archived")
    },
  )
}

function reactivateListing(id) {
  showConfirmModal(
    "Reactivar publicación",
    "¿Estás seguro de que deseas reactivar esta publicación? Será visible nuevamente para los compradores.",
    () => {
      updateListingStatus(id, "active")
    },
  )
}

function deleteListing(id) {
  showConfirmModal(
    "Eliminar publicación",
    "¿Estás seguro de que deseas eliminar esta publicación? Esta acción no se puede deshacer.",
    () => {
      updateListingStatus(id, "deleted")
    },
  )
}

// Update listing status
function updateListingStatus(id, status) {
  fetch("../../api/update-listing.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      listing_id: id,
      status: status,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert(data.message || "Publicación actualizada correctamente", "success")

        // Reload listings and update counts
        loadSellerProfile()

        // Reload all tabs
        const tabs = ["active", "archived", "deleted", "completed"]
        tabs.forEach((tab) => {
          const tabElement = document.getElementById(`${tab}-tab`)
          if (tabElement) {
            tabElement.removeAttribute("data-loaded")
          }
        })

        // Reload current active tab
        const activeTab = document.querySelector(".tab-trigger.active")
        if (activeTab) {
          const tabName = activeTab.getAttribute("data-tab")
          loadListings(tabName)
        }
      } else {
        showAlert(data.message || "Error al actualizar la publicación", "error")
      }
    })
    .catch((error) => {
      console.error("Error updating listing:", error)
      showAlert("Error al actualizar la publicación", "error")
    })
}

// Helper functions
function formatPrice(price) {
  return (
    "$" +
    Number.parseFloat(price).toLocaleString("es-AR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  )
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("es-AR")
}

function getInitials(name) {
  return name
    .split(" ")
    .map((part) => part.charAt(0))
    .join("")
    .toUpperCase()
    .substring(0, 2)
}
