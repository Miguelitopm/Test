// Buyer Profile JavaScript

function showAlert(message, type) {
  const alertContainer = document.createElement("div")
  alertContainer.className = `alert alert-${type}`
  alertContainer.textContent = message
  alertContainer.style.position = 'fixed'
  alertContainer.style.top = '20px'
  alertContainer.style.right = '20px'
  alertContainer.style.zIndex = '9999'
  alertContainer.style.minWidth = '300px'

  document.body.appendChild(alertContainer)

  setTimeout(() => {
    if (document.body.contains(alertContainer)) {
      document.body.removeChild(alertContainer)
    }
  }, 5000)
}

document.addEventListener("DOMContentLoaded", () => {
  // Load buyer profile data
  loadBuyerProfile()

  // Load purchases
  loadPurchases()

  // Set up tab switching
  setupTabs()
})

// Load buyer profile data
function loadBuyerProfile() {
  fetch("../../api/buyer-profile.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update profile information
        document.getElementById("buyer-name").textContent = data.profile.full_name
        document.getElementById("buyer-rating").textContent = data.profile.rating ? data.profile.rating.toFixed(1) : "0.0"
        document.getElementById("reviews-count").textContent = `(${data.profile.total_reviews || 0} reseñas)`
        document.getElementById("buyer-location").textContent = data.profile.location
        document.getElementById("buyer-since").textContent =
          `Miembro desde ${new Date(data.profile.member_since).getFullYear()}`
        document.getElementById("wallet-balance").textContent = formatPrice(data.profile.wallet_balance)
        document.getElementById("total-purchases").textContent = data.profile.total_purchases || 0
        document.getElementById("favorite-products").textContent = data.profile.favorite_count || 0

        // Update counts in tabs
        document.getElementById("purchases-count").textContent = data.counts?.purchases || 0
        document.getElementById("favorites-count").textContent = data.counts?.favorites || 0
        document.getElementById("my-reviews-count").textContent = data.counts?.reviews || 0

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

        // Update form fields
        document.getElementById("full_name").value = data.profile.full_name
        document.getElementById("email").value = data.profile.email
        document.getElementById("location").value = data.profile.location
        document.getElementById("phone").value = data.profile.phone || ""
      } else {
        showAlert(data.message || "Error al cargar el perfil", "error")
      }
    })
    .catch((error) => {
      console.error("Error loading profile:", error)
      showAlert("Error al cargar el perfil", "error")
    })
}

// Load purchases
function loadPurchases() {
  const container = document.getElementById("purchases-container")
  container.innerHTML = '<div class="loading-spinner">Cargando...</div>'

  fetch("../../api/buyer-purchases.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        if (data.purchases.length > 0) {
          container.innerHTML = ""
          data.purchases.forEach((purchase) => {
            container.appendChild(createPurchaseCard(purchase))
          })
        } else {
          container.innerHTML = createEmptyState("purchases")
        }
      } else {
        container.innerHTML = `<div class="alert alert-error">${data.message || "Error al cargar las compras"}</div>`
      }
    })
    .catch((error) => {
      console.error("Error loading purchases:", error)
      container.innerHTML = createEmptyState("purchases")
    })
}

// Load favorites
function loadFavorites() {
  const container = document.getElementById("favorites-container")
  container.innerHTML = '<div class="loading-spinner">Cargando...</div>'

  fetch("../../api/buyer-favorites.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        if (data.favorites.length > 0) {
          container.innerHTML = ""
          data.favorites.forEach((favorite) => {
            container.appendChild(createFavoriteCard(favorite))
          })
        } else {
          container.innerHTML = createEmptyState("favorites")
        }
      } else {
        container.innerHTML = `<div class="alert alert-error">${data.message || "Error al cargar favoritos"}</div>`
      }
    })
    .catch((error) => {
      console.error("Error loading favorites:", error)
      container.innerHTML = createEmptyState("favorites")
    })
}

// Load reviews
function loadReviews() {
  const container = document.getElementById("reviews-container")
  container.innerHTML = '<div class="loading-spinner">Cargando...</div>'

  fetch("../../api/buyer-reviews.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        if (data.reviews.length > 0) {
          container.innerHTML = ""
          data.reviews.forEach((review) => {
            container.appendChild(createReviewCard(review))
          })
        } else {
          container.innerHTML = createEmptyState("reviews")
        }
      } else {
        container.innerHTML = `<div class="alert alert-error">${data.message || "Error al cargar reseñas"}</div>`
      }
    })
    .catch((error) => {
      console.error("Error loading reviews:", error)
      container.innerHTML = createEmptyState("reviews")
    })
}

// Create purchase card
function createPurchaseCard(purchase) {
  const card = document.createElement("div")
  card.className = "listing-card"

  const statusBadge = getStatusBadge(purchase.status)

  card.innerHTML = `
    <div class="listing-image">
      <img src="${purchase.image_url || "../../assets/images/placeholder.png"}" alt="${purchase.title}" onerror="this.src='../../assets/images/placeholder.png'">
    </div>
    <div class="listing-info">
      <h3 class="listing-title">${purchase.title}</h3>
      <p class="listing-price">${formatPrice(purchase.price)}</p>
      <div class="listing-meta">
        ${statusBadge}
        <span>Cantidad: ${purchase.quantity}</span>
        <span>Comprado: ${formatDate(purchase.created_at)}</span>
        <span>Vendedor: ${purchase.seller_name}</span>
      </div>
    </div>
    <div class="listing-actions">
      ${purchase.status === 'delivered' ? `<button class="btn" onclick="reviewProduct(${purchase.product_id}, ${purchase.id})">Reseñar</button>` : ''}
      <a href="../../product.php?id=${purchase.product_id}" class="btn">Ver Producto</a>
    </div>
  `

  return card
}

// Create favorite card
function createFavoriteCard(favorite) {
  const card = document.createElement("div")
  card.className = "listing-card"

  card.innerHTML = `
    <div class="listing-image">
      <img src="${favorite.image_url || "../../assets/images/placeholder.png"}" alt="${favorite.title}" onerror="this.src='../../assets/images/placeholder.png'">
    </div>
    <div class="listing-info">
      <h3 class="listing-title">${favorite.title}</h3>
      <p class="listing-price">${formatPrice(favorite.price)}</p>
      <div class="listing-meta">
        <span class="status-badge status-active">Disponible</span>
        <span>Vendedor: ${favorite.seller_name}</span>
        <span>Agregado: ${formatDate(favorite.created_at)}</span>
      </div>
    </div>
    <div class="listing-actions">
      <button class="btn btn-destructive" onclick="removeFavorite(${favorite.product_id})">Quitar</button>
      <a href="../../product.php?id=${favorite.product_id}" class="btn btn-primary">Ver Producto</a>
    </div>
  `

  return card
}

// Create review card
function createReviewCard(review) {
  const card = document.createElement("div")
  card.className = "listing-card"

  const stars = generateStars(review.rating)

  card.innerHTML = `
    <div class="listing-image">
      <img src="${review.product_image || "../../assets/images/placeholder.png"}" alt="${review.product_title}" onerror="this.src='../../assets/images/placeholder.png'">
    </div>
    <div class="listing-info">
      <h3 class="listing-title">${review.product_title}</h3>
      <div class="product-rating" style="margin-bottom: 0.5rem;">
        ${stars}
      </div>
      <p style="color: var(--gray-700); margin-bottom: 0.5rem;">${review.comment}</p>
      <div class="listing-meta">
        <span>Reseña del: ${formatDate(review.created_at)}</span>
        <span>${review.helpful_count} personas encontraron útil</span>
      </div>
    </div>
    <div class="listing-actions">
      <a href="../../product.php?id=${review.product_id}" class="btn">Ver Producto</a>
    </div>
  `

  return card
}

// Create empty state message
function createEmptyState(type) {
  let icon, title, message

  switch (type) {
    case "purchases":
      icon = '<svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>'
      title = "No tienes compras realizadas"
      message = "Explora nuestro catálogo y realiza tu primera compra"
      break
    case "favorites":
      icon = '<svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20"><path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/></svg>'
      title = "No tienes productos favoritos"
      message = "Guarda productos que te interesen para encontrarlos fácilmente"
      break
    case "reviews":
      icon = '<svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>'
      title = "No has realizado reseñas"
      message = "Compra productos y comparte tu experiencia con otros usuarios"
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
      document.getElementById(`${tabName}-tab`).classList.add("active")

      // Load content if not already loaded
      if (!document.getElementById(`${tabName}-tab`).getAttribute("data-loaded")) {
        switch (tabName) {
          case "favorites":
            loadFavorites()
            break
          case "reviews":
            loadReviews()
            break
        }
        document.getElementById(`${tabName}-tab`).setAttribute("data-loaded", "true")
      }
    })
  })

  // Handle profile form submission
  const profileForm = document.getElementById("profile-form")
  if (profileForm) {
    profileForm.addEventListener("submit", (e) => {
      e.preventDefault()
      updateProfile()
    })
  }
}

// Helper functions
function formatPrice(price) {
  return "$" + Number.parseFloat(price).toLocaleString("es-AR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
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

function getStatusBadge(status) {
  const statusMap = {
    'pending': '<span class="status-badge" style="background: var(--yellow-100); color: var(--yellow-800);">Pendiente</span>',
    'processing': '<span class="status-badge" style="background: var(--secondary-100); color: var(--secondary-800);">Procesando</span>',
    'shipped': '<span class="status-badge" style="background: var(--primary-100); color: var(--primary-800);">Enviado</span>',
    'delivered': '<span class="status-badge status-completed">Entregado</span>',
    'cancelled': '<span class="status-badge" style="background: var(--red-100); color: var(--red-800);">Cancelado</span>'
  }
  return statusMap[status] || '<span class="status-badge">Desconocido</span>'
}

function generateStars(rating) {
  let stars = ""
  for (let i = 1; i <= 5; i++) {
    const color = i <= rating ? "#fbbf24" : "#d1d5db"
    stars += `<svg class="star" fill="currentColor" viewBox="0 0 20 20" style="width: 1rem; height: 1rem; color: ${color};"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>`
  }
  return stars
}

// Actions
function removeFavorite(productId) {
  if (confirm("¿Estás seguro de que deseas quitar este producto de favoritos?")) {
    fetch("../../api/favorites.php", {
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
        showAlert("Producto eliminado de favoritos", "success")
        loadFavorites()
        loadBuyerProfile()
      } else {
        showAlert(data.message || "Error al eliminar de favoritos", "error")
      }
    })
    .catch((error) => {
      showAlert("Error al eliminar de favoritos", "error")
    })
  }
}

function reviewProduct(productId, orderId) {
  // Redirect to review page or open modal
  window.location.href = `../../review.php?product_id=${productId}&order_id=${orderId}`
}

function updateProfile() {
  const formData = new FormData(document.getElementById("profile-form"))
  
  fetch("../../api/update-profile.php", {
    method: "POST",
    body: formData,
  })
  .then((response) => response.json())
  .then((data) => {
    if (data.success) {
      showAlert("Perfil actualizado correctamente", "success")
      loadBuyerProfile()
    } else {
      showAlert(data.message || "Error al actualizar perfil", "error")
    }
  })
  .catch((error) => {
    showAlert("Error al actualizar perfil", "error")
  })
}
