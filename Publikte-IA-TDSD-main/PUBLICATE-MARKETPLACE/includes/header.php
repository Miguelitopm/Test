<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PUBLICATE - Tu Marketplace de Confianza</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>

<body>
    <?php
    $alert = getAlert();
    if ($alert):
        ?>
        <div class="alert alert-<?php echo $alert['type']; ?>"
            style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <?php echo htmlspecialchars($alert['message']); ?>
        </div>
        <script>
            setTimeout(() => {
                document.querySelector('.alert').remove();
            }, 5000);
        </script>
    <?php endif; ?>

    <header class="header">
        <div class="container header-container">
            <!-- Logo -->
            <a href="index.php" class="logo">
                <div class="logo-icon">P</div>
                <span class="logo-text">PUBLICATE</span>
            </a>

            <!-- Search Bar -->
            <div class="search-container">
                <form action="search.php" method="GET" class="search-form">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="q" class="search-input" placeholder="Buscar productos, marcas y más..."
                        value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                </form>
            </div>

            <!-- Navigation -->
            <div class="nav-items">
                <a href="sell.php" class="nav-button sell">Vender</a>

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
                <a href="cart.php" class="nav-button">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                    </svg>
                    <span class="badge blue" id="cart-count">
                        <?php
                        if (isLoggedIn()) {
                            $db = (new Database())->getConnection();
                            $query = "SELECT COUNT(*) as count FROM cart WHERE user_id = :user_id";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':user_id', $_SESSION['user_id']);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['count'] ?: 0;
                        } else {
                            echo '0';
                        }
                        ?>
                    </span>
                </a>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="nav-button">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><?php echo isLoggedIn() ? 'Mi cuenta' : 'Ingresar'; ?></span>
                    </button>
                    <div class="dropdown-content">
                        <?php if (isLoggedIn()): ?>
                            <a href="profile/buyer/index.php" class="dropdown-item">Perfil de Comprador</a>
                            <a href="profile/seller/index.php" class="dropdown-item">Perfil de Vendedor</a>
                            <div class="dropdown-separator"></div>
                            <a href="wallet.php" class="dropdown-item">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"
                                    style="margin-right: 0.5rem;">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                    <path fill-rule="evenodd"
                                        d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                Mi Wallet
                            </a>
                            <a href="sales.php" class="dropdown-item">Mis Ventas</a>
                            <a href="purchases.php" class="dropdown-item">Mis Compras</a>
                            <div class="dropdown-separator"></div>
                            <a href="logout.php" class="dropdown-item">Cerrar Sesión</a>
                        <?php else: ?>
                            <a href="login.php" class="dropdown-item">Iniciar Sesión</a>
                            <a href="register.php" class="dropdown-item">Registrarse</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container">