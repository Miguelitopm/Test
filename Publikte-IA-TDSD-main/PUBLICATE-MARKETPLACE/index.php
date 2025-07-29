<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Obtener productos destacados
$query = "SELECT p.*, u.username as seller_name, u.location as seller_location, u.rating as seller_rating,
          (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
          (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
          FROM products p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.status = 'active' 
          ORDER BY p.views DESC, p.created_at DESC 
          LIMIT 8";
$stmt = $db->prepare($query);
$stmt->execute();
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías
$query = "SELECT * FROM categories ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Bienvenido a <span style="color: #bfdbfe;">PUBLICATE</span></h1>
            <p>El marketplace más moderno donde comprar y vender es fácil, seguro y confiable</p>
            <div class="hero-buttons">
                <a href="search.php" class="btn btn-outline btn-lg">Explorar productos</a>
                <a href="sell.php" class="btn btn-outline btn-lg">Comenzar a vender</a>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features">
        <div class="grid grid-4">
            <div class="feature-card">
                <div class="feature-icon">
                    <img src="assets/images/secure-payment.png" alt="Compra Protegida"
                        onerror="this.style.display='none'">
                </div>
                <h3 class="feature-title">Compra Protegida</h3>
                <p class="feature-description">Tu dinero está seguro hasta recibir el producto</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <img src="assets/images/fast-delivery.png" alt="Envío Gratis" onerror="this.style.display='none'">
                </div>
                <h3 class="feature-title">Envío Gratis</h3>
                <p class="feature-description">En miles de productos seleccionados</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <img src="assets/images/quality-products.png" alt="Calidad Garantizada"
                        onerror="this.style.display='none'">
                </div>
                <h3 class="feature-title">Calidad Garantizada</h3>
                <p class="feature-description">Vendedores verificados y productos auténticos</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <img src="assets/images/best-price.png" alt="Calidad Garantizada"
                        onerror="this.style.display='none'">
                </div>
                <h3 class="feature-title">Mejores Precios</h3>
                <p class="feature-description">Compara y encuentra las mejores ofertas</p>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories">
        <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 2rem; color: var(--gray-800);">Explora por
            CategorÃ­as</h2>
        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <a href="search.php?category=<?php echo $category['slug']; ?>" class="category-button">
                    <div class="category-icon <?php echo $category['color']; ?>">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20" style="display: block;">
                            <?php
                            $icons = [
                                'smartphone' => '<path fill-rule="evenodd" d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zM9 4h2v1H9V4zm0 11h2v1H9v-1z" clip-rule="evenodd"/>',
                                'laptop' => '<path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm12 2H5v6h10V7z" clip-rule="evenodd"/>',
                                'car' => '<path d="M8 19a3 3 0 100-6 3 3 0 000 6zM18 19a3 3 0 100-6 3 3 0 000 6zM1.5 4.5A1.5 1.5 0 013 3h1.5l.5 2h9.5a1 1 0 01.8 1.6L13 10H6l-1-4H3a1 1 0 110-2z"/>',
                                'home' => '<path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd"/>',
                                'shirt' => '<path d="M3 4a1 1 0 011-1h1a1 1 0 011 1v1h2V4a3 3 0 116 0v1h2V4a1 1 0 011-1h1a1 1 0 011 1v2.5l-2 1.5v9a1 1 0 01-1 1H5a1 1 0 01-1-1V8l-2-1.5V4z"/>',
                                'book' => '<path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/>',
                                'gamepad2' => '<path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm5.5 1A1.5 1.5 0 007 7.5v1A1.5 1.5 0 008.5 10h1A1.5 1.5 0 0011 8.5v-1A1.5 1.5 0 009.5 6h-1zM13 7a1 1 0 100 2 1 1 0 000-2zm-1 4a1 1 0 112 0 1 1 0 01-2 0z" clip-rule="evenodd"/>',
                                'heart' => '<path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>'
                            ];
                            echo $icons[$category['icon']] ?? $icons['smartphone'];
                            ?>
                        </svg>
                    </div>
                    <span class="category-name"><?php echo htmlspecialchars($category['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 style="font-size: 1.5rem; font-weight: bold;">Productos Destacados</h2>
            <a href="search.php" class="btn"
                style="background: none; border: 1px solid var(--gray-300); color: var(--gray-700);">Ver todos</a>
        </div>
        <div class="grid grid-4">
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <a href="product.php?id=<?php echo $product['id']; ?>">
                        <div class="product-image">
                            <img src="<?php echo $product['primary_image'] ?: 'assets/images/placeholder.jpg'; ?>"
                                alt="<?php echo htmlspecialchars($product['title']); ?>"
                                onerror="this.src='assets/images/placeholder.jpg'">
                            <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                                <?php $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100); ?>
                                <div class="discount-badge">-<?php echo $discount; ?>%</div>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div class="product-info">
                        <a href="product.php?id=<?php echo $product['id']; ?>">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                        </a>

                        <div class="product-price">
                            <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                            <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                                <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="product-rating">
                            <svg class="star" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span><?php echo number_format($product['seller_rating'], 1); ?></span>
                            <span>(<?php echo $product['review_count']; ?>)</span>
                        </div>

                        <div class="product-location">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span><?php echo htmlspecialchars($product['seller_location']); ?></span>
                        </div>

                        <div class="product-shipping">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                <path
                                    d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707L16 7.586A1 1 0 0015.414 7H14z" />
                            </svg>
                            <span><?php echo $product['shipping_type'] == 'free' ? 'Envío gratis' : 'Envío pago'; ?></span>
                        </div>

                        <div class="product-seller">
                            por <a href="seller.php?id=<?php echo $product['user_id']; ?>"
                                class="seller-link"><?php echo htmlspecialchars($product['seller_name']); ?></a>
                        </div>

                        <button class="btn btn-primary" style="width: 100%;"
                            onclick="addToCart(<?php echo $product['id']; ?>)">
                            Agregar al carrito
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<script>
    function addToCart(productId) {
        <?php if (isLoggedIn()): ?>
            fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: 1
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Producto agregado al carrito', 'success');
                        updateCartCount();
                    } else {
                        showAlert(data.message || 'Error al agregar al carrito', 'error');
                    }
                })
                .catch(error => {
                    showAlert('Error al agregar al carrito', 'error');
                });
        <?php else: ?>
            showAlert('Debes iniciar sesión para agregar productos al carrito', 'warning');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        <?php endif; ?>
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    function updateCartCount() {
        fetch('api/cart.php?action=count')
            .then(response => response.json())
            .then(data => {
                const cartBadge = document.querySelector('.nav-button .badge.blue');
                if (cartBadge) {
                    cartBadge.textContent = data.count || 0;
                }
            });
    }
</script>

<?php include 'includes/footer.php'; ?>