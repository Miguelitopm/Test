<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Vendedor - PUBLICATE</title>
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
                        <a href="../buyer/index.php" class="dropdown-item">Perfil de Comprador</a>
                        <a href="index.php" class="dropdown-item">Perfil de Vendedor</a>
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
                                <div class="avatar-fallback" id="avatar-fallback">JS</div>
                            </div>
                            <h2 class="seller-name" id="seller-name">Juan Seller</h2>
                            <div class="seller-rating">
                                <svg class="star" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="rating-value" id="seller-rating">4.8</span>
                                <span class="reviews-count" id="reviews-count">(89 reseñas)</span>
                            </div>
                            <div class="seller-location">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span id="seller-location">Buenos Aires, Argentina</span>
                            </div>
                            <div class="seller-since">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span id="seller-since">Vendedor desde 2022</span>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="wallet-card">
                                <div class="wallet-info">
                                    <p class="wallet-label">Saldo en Wallet</p>
                                    <p class="wallet-balance" id="wallet-balance">$2,450.50</p>
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
                                    <p class="stat-value" id="total-sales">156</p>
                                    <p class="stat-label">Ventas Totales</p>
                                </div>
                                <div class="stat-card secondary">
                                    <p class="stat-value" id="active-listings">12</p>
                                    <p class="stat-label">Publicaciones</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seller Hero -->
                    <div class="seller-hero">
                        <div class="seller-hero-bg"></div>
                        <div class="seller-hero-content">
                            <h2>¡Haz crecer tu negocio!</h2>
                            <p>Gestiona tus ventas, productos y ganancias desde un solo lugar</p>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="seller-main-content">
                    <div class="seller-header">
                        <h1>Panel de Vendedor</h1>
                        <a href="../../sell.php" class="btn btn-primary new-listing-btn">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Nueva Publicación ($1)
                        </a>
                    </div>

                    <div class="seller-tabs">
                        <div class="tabs-header">
                            <button class="tab-trigger active" data-tab="active">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                    <path
                                        d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707L16 7.586A1 1 0 0015.414 7H14z" />
                                </svg>
                                <span>Activas (<span id="active-count">0</span>)</span>
                            </button>
                            <button class="tab-trigger" data-tab="completed">
                                <span>Vendidas (<span id="completed-count">0</span>)</span>
                            </button>
                            <button class="tab-trigger" data-tab="archived">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                                    <path fill-rule="evenodd"
                                        d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Archivadas (<span id="archived-count">0</span>)</span>
                            </button>
                            <button class="tab-trigger" data-tab="deleted">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Eliminadas (<span id="deleted-count">0</span>)</span>
                            </button>
                        </div>

                        <div class="tabs-content">
                            <!-- Active Listings Tab -->
                            <div class="tab-content active" id="active-tab">
                                <div class="listings-container" id="active-listings-container">
                                    <!-- Active listings will be loaded here -->
                                    <div class="loading-spinner">Cargando...</div>
                                </div>
                            </div>

                            <!-- Completed Sales Tab -->
                            <div class="tab-content" id="completed-tab">
                                <div class="listings-container" id="completed-listings-container">
                                    <!-- Completed sales will be loaded here -->
                                    <div class="loading-spinner">Cargando...</div>
                                </div>
                            </div>

                            <!-- Archived Listings Tab -->
                            <div class="tab-content" id="archived-tab">
                                <div class="listings-container" id="archived-listings-container">
                                    <!-- Archived listings will be loaded here -->
                                    <div class="loading-spinner">Cargando...</div>
                                </div>
                            </div>

                            <!-- Deleted Listings Tab -->
                            <div class="tab-content" id="deleted-tab">
                                <div class="listings-container" id="deleted-listings-container">
                                    <!-- Deleted listings will be loaded here -->
                                    <div class="loading-spinner">Cargando...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer corregido -->
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
                            <li><a href="index.php">Panel de vendedor</a></li>
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

    <!-- Modal para confirmar acciones -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2 id="modal-title">Confirmar acción</h2>
            <p id="modal-message">¿Estás seguro de que deseas realizar esta acción?</p>
            <div class="modal-actions">
                <button id="modal-cancel" class="btn">Cancelar</button>
                <button id="modal-confirm" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/seller-profile.js"></script>
</body>

</html>