<?php
require_once '../../config/config.php';

if (!isLoggedIn()) {
    redirect('../../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$page_title = 'Perfil de Comprador';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Comprador - PUBLICATE</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/seller-profile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>

<body>
    <header class="header">
        <div class="container header-container">
            <!-- Logo -->
            <a href="../../index.php" class="logo">
                <div class="logo-icon">P</div>
                <span class="logo-text">PUBLICATE</span>
            </a>

            <!-- Search Bar -->
            <div class="search-container">
                <form action="../../search.php" method="GET" class="search-form">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="q" class="search-input" placeholder="Buscar productos, marcas y más...">
                </form>
            </div>

            <!-- Navigation -->
            <div class="nav-items">
                <a href="../../sell.php" class="nav-button sell">Vender</a>

                <!-- Notifications -->
                <div class="dropdown">
                    <button class="nav-button">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                        </svg>
                        <span class="badge">3</span>
                    </button>
                    <div class="dropdown-content">
                        <a href="#" class="dropdown-item">Nueva oferta recibida</a>
                        <a href="#" class="dropdown-item">Producto vendido</a>
                        <a href="#" class="dropdown-item">Nuevo seguidor</a>
                    </div>
                </div>

                <!-- Cart -->
                <a href="../../cart.php" class="nav-button">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                    </svg>
                    <span class="badge blue" id="cart-count">2</span>
                </a>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="nav-button">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Mi cuenta</span>
                    </button>
                    <div class="dropdown-content">
                        <a href="index.php" class="dropdown-item">Perfil de Comprador</a>
                        <a href="../seller/index.php" class="dropdown-item">Perfil de Vendedor</a>
                        <div class="dropdown-separator"></div>
                        <a href="../../wallet.php" class="dropdown-item">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"
                                style="margin-right: 0.5rem;">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                <path fill-rule="evenodd"
                                    d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Mi Wallet
                        </a>
                        <a href="../../sales.php" class="dropdown-item">Mis Ventas</a>
                        <a href="../../purchases.php" class="dropdown-item">Mis Compras</a>
                        <div class="dropdown-separator"></div>
                        <a href="../../logout.php" class="dropdown-item">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="seller-profile-container">
            <div class="seller-profile-grid">
                <!-- Sidebar - Perfil -->
                <div class="seller-sidebar">
                    <div class="card">
                        <div class="card-header text-center">
                            <div class="seller-avatar">
                                <img src="../../assets/images/placeholder.png" alt="Foto de perfil" id="profile-image"
                                    onerror="this.style.display='none'; document.getElementById('avatar-fallback').style.display='flex';">
                                <div class="avatar-fallback" id="avatar-fallback">JC</div>
                            </div>
                            <h2 class="seller-name" id="buyer-name">Juan Comprador</h2>
                            <div class="seller-rating">
                                <svg class="star" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="rating-value" id="buyer-rating">4.9</span>
                                <span class="reviews-count" id="reviews-count">(23 reseñas)</span>
                            </div>
                            <div class="seller-location">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span id="buyer-location">Buenos Aires, Argentina</span>
                            </div>
                            <div class="seller-since">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span id="buyer-since">Miembro desde 2023</span>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="wallet-card">
                                <div class="wallet-info">
                                    <p class="wallet-label">Saldo en Wallet</p>
                                    <p class="wallet-balance" id="wallet-balance">$1,500.00</p>
                                </div>
                                <svg class="wallet-icon" width="32" height="32" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                    <path fill-rule="evenodd"
                                        d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <a href="../../wallet.php" class="btn btn-primary wallet-btn">Recargar Wallet</a>
                            </div>

                            <div class="stats-grid">
                                <div class="stat-card primary">
                                    <p class="stat-value" id="total-purchases">45</p>
                                    <p class="stat-label">Compras Totales</p>
                                </div>
                                <div class="stat-card secondary">
                                    <p class="stat-value" id="favorite-products">18</p>
                                    <p class="stat-label">Favoritos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buyer Hero -->
                    <div class="seller-hero">
                        <div class="seller-hero-bg"></div>
                        <div class="seller-hero-content">
                            <h2>¡Encuentra lo que buscas!</h2>
                            <p>Explora miles de productos y encuentra las mejores ofertas</p>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="seller-main-content">
                    <div class="seller-header">
                        <h1>Panel de Comprador</h1>
                        <a href="../../search.php" class="btn btn-primary new-listing-btn">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Explorar Productos
                        </a>
                    </div>

                    <div class="seller-tabs">
                        <div class="tabs-header">
                            <button class="tab-trigger active" data-tab="purchases">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                </svg>
                                <span>Mis Compras (<span id="purchases-count">0</span>)</span>
                            </button>
                            <button class="tab-trigger" data-tab="favorites">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" />
                                </svg>
                                <span>Favoritos (<span id="favorites-count">0</span>)</span>
                            </button>
                            <button class="tab-trigger" data-tab="reviews">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Mis Reseñas (<span id="my-reviews-count">0</span>)</span>
                            </button>
                            <button class="tab-trigger" data-tab="settings">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Configuración</span>
                            </button>
                        </div>

                        <div class="tabs-content">
                            <!-- Purchases Tab -->
                            <div class="tab-content active" id="purchases-tab">
                                <div class="listings-container" id="purchases-container">
                                    <div class="loading-spinner">Cargando...</div>
                                </div>
                            </div>

                            <!-- Favorites Tab -->
                            <div class="tab-content" id="favorites-tab">
                                <div class="listings-container" id="favorites-container">
                                    <div class="loading-spinner">Cargando...</div>
                                </div>
                            </div>

                            <!-- Reviews Tab -->
                            <div class="tab-content" id="reviews-tab">
                                <div class="listings-container" id="reviews-container">
                                    <div class="loading-spinner">Cargando...</div>
                                </div>
                            </div>

                            <!-- Settings Tab -->
                            <div class="tab-content" id="settings-tab">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Información Personal</h3>
                                    </div>
                                    <div class="card-content">
                                        <form id="profile-form">
                                            <div class="form-group">
                                                <label for="full_name" class="form-label">Nombre completo</label>
                                                <input type="text" id="full_name" name="full_name" class="form-input"
                                                    value="Juan Comprador">
                                            </div>
                                            <div class="form-group">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" id="email" name="email" class="form-input"
                                                    value="juan@example.com">
                                            </div>
                                            <div class="form-group">
                                                <label for="location" class="form-label">Ubicación</label>
                                                <input type="text" id="location" name="location" class="form-input"
                                                    value="Buenos Aires, Argentina">
                                            </div>
                                            <div class="form-group">
                                                <label for="phone" class="form-label">Teléfono</label>
                                                <input type="tel" id="phone" name="phone" class="form-input"
                                                    value="+54 11 1234-5678">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <div class="logo-icon">P</div>
                        <span class="logo-text">PUBLICATE</span>
                    </div>
                    <p class="footer-description">El marketplace más moderno donde comprar y vender es fácil, seguro y
                        confiable.</p>
                </div>

                <div class="footer-links">
                    <div class="footer-column">
                        <h3 class="footer-title">Comprar</h3>
                        <ul class="footer-list">
                            <li><a href="../../search.php">Todos los productos</a></li>
                            <li><a href="../../search.php?category=electronics">Electrónicos</a></li>
                            <li><a href="../../search.php?category=computers">Computación</a></li>
                            <li><a href="../../search.php?category=fashion">Moda</a></li>
                        </ul>
                    </div>

                    <div class="footer-column">
                        <h3 class="footer-title">Vender</h3>
                        <ul class="footer-list">
                            <li><a href="../../sell.php">Crear publicación</a></li>
                            <li><a href="../seller/index.php">Panel de vendedor</a></li>
                            <li><a href="../../sales.php">Mis ventas</a></li>
                            <li><a href="../../wallet.php">Mi wallet</a></li>
                        </ul>
                    </div>

                    <div class="footer-column">
                        <h3 class="footer-title">Ayuda</h3>
                        <ul class="footer-list">
                            <li><a href="#">Centro de ayuda</a></li>
                            <li><a href="#">Términos y condiciones</a></li>
                            <li><a href="#">Política de privacidad</a></li>
                            <li><a href="#">Contacto</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2024 PUBLICATE. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/buyer-profile.js"></script>
</body>

</html>