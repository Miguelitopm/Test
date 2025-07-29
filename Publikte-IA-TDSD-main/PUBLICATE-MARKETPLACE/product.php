<?php
require_once 'config/config.php';

$product_id = intval($_GET['id'] ?? 0);

if (!$product_id) {
    redirect('search.php');
}

$database = new Database();
$db = $database->getConnection();

// Obtener producto
$query = "SELECT p.*, u.username as seller_name, u.full_name as seller_full_name, 
          u.location as seller_location, u.rating as seller_rating, u.total_reviews, u.total_sales,
          u.member_since, c.name as category_name
          FROM products p 
          JOIN users u ON p.user_id = u.id 
          JOIN categories c ON p.category_id = c.id
          WHERE p.id = :product_id AND p.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(':product_id', $product_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    redirect('search.php');
}

$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Incrementar vistas
$update_views = "UPDATE products SET views = views + 1 WHERE id = :product_id";
$stmt = $db->prepare($update_views);
$stmt->bindParam(':product_id', $product_id);
$stmt->execute();

// Obtener imágenes del producto
$images_query = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY is_primary DESC, sort_order ASC";
$stmt = $db->prepare($images_query);
$stmt->bindParam(':product_id', $product_id);
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener comentarios/reseñas
$reviews_query = "SELECT r.*, u.username, u.full_name, o.id as order_id
                  FROM reviews r 
                  JOIN users u ON r.reviewer_id = u.id 
                  JOIN orders o ON r.order_id = o.id
                  WHERE r.product_id = :product_id 
                  ORDER BY r.created_at DESC";
$stmt = $db->prepare($reviews_query);
$stmt->bindParam(':product_id', $product_id);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si está en favoritos (si el usuario está logueado)
$is_favorite = false;
if (isLoggedIn()) {
    $fav_query = "SELECT id FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
    $stmt = $db->prepare($fav_query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $is_favorite = $stmt->rowCount() > 0;
}

$page_title = $product['title'];
include 'includes/header.php';
?>

<div style="padding: 2rem 0;">
    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Imágenes -->
        <div>
            <div id="mainImage"
                style="aspect-ratio: 1; border-radius: 0.5rem; overflow: hidden; background: var(--gray-100); margin-bottom: 1rem;">
                <img src="<?php echo $images[0]['image_url'] ?? 'assets/images/placeholder.jpg'; ?>"
                    alt="<?php echo htmlspecialchars($product['title']); ?>"
                    style="width: 100%; height: 100%; object-fit: cover;"
                    onerror="this.src='assets/images/placeholder.jpg'">
            </div>

            <?php if (count($images) > 1): ?>
                <div style="display: flex; gap: 0.5rem; overflow-x: auto;">
                    <?php foreach ($images as $index => $image): ?>
                        <button onclick="changeMainImage('<?php echo $image['image_url']; ?>', <?php echo $index; ?>)"
                            class="image-thumb <?php echo $index == 0 ? 'active' : ''; ?>"
                            style="flex-shrink: 0; width: 5rem; height: 5rem; border-radius: 0.5rem; overflow: hidden; border: 2px solid transparent; cursor: pointer;">
                            <img src="<?php echo $image['image_url']; ?>"
                                alt="<?php echo htmlspecialchars($product['title']); ?> <?php echo $index + 1; ?>"
                                style="width: 100%; height: 100%; object-fit: cover;"
                                onerror="this.src='assets/images/placeholder.jpg'">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Información del producto -->
        <div>
            <div style="margin-bottom: 1rem;">
                <span class="badge"
                    style="background: var(--secondary-100); color: var(--secondary-800); margin-bottom: 0.5rem;">
                    <?php
                    $conditions = [
                        'new' => 'Nuevo',
                        'like-new' => 'Como nuevo',
                        'excellent' => 'Excelente estado',
                        'good' => 'Buen estado',
                        'fair' => 'Estado regular'
                    ];
                    echo $conditions[$product['condition_type']] ?? $product['condition_type'];
                    ?>
                </span>
                <h1 style="font-size: 1.875rem; font-weight: bold; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($product['title']); ?>
                </h1>

                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center;">
                        <svg class="star" fill="currentColor" viewBox="0 0 20 20"
                            style="width: 1.25rem; height: 1.25rem; color: #fbbf24; margin-right: 0.25rem;">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span
                            style="font-weight: 600;"><?php echo number_format($product['seller_rating'], 1); ?></span>
                        <span
                            style="color: var(--gray-500); margin-left: 0.25rem;">(<?php echo $product['total_reviews']; ?>
                            reseñas)</span>
                    </div>
                    <div style="display: flex; align-items: center; color: var(--gray-600);">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"
                            style="margin-right: 0.25rem;">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><?php echo htmlspecialchars($product['seller_location']); ?></span>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: baseline; gap: 1rem;">
                    <span
                        style="font-size: 3rem; font-weight: 900; color: var(--primary-600);"><?php echo formatPrice($product['price']); ?></span>
                    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                        <span
                            style="font-size: 1.5rem; color: var(--gray-500); text-decoration: line-through;"><?php echo formatPrice($product['original_price']); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                    <?php $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100); ?>
                    <span class="badge"
                        style="background: var(--secondary-100); color: var(--secondary-800); margin-top: 0.5rem;">
                        <?php echo $discount; ?>% OFF
                    </span>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; color: var(--primary-600); margin-bottom: 0.75rem;">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="margin-right: 0.5rem;">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                        <path
                            d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707L16 7.586A1 1 0 0015.414 7H14z" />
                    </svg>
                    <span
                        style="font-weight: 500;"><?php echo $product['shipping_type'] == 'free' ? 'Envío gratis' : 'Envío pago'; ?></span>
                </div>
                <div style="display: flex; align-items: center; color: var(--secondary-600);">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="margin-right: 0.5rem;">
                        <path fill-rule="evenodd"
                            d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span style="font-weight: 500;">6 meses de garantía</span>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <?php if (isLoggedIn() && $_SESSION['user_id'] != $product['user_id']): ?>
                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary btn-lg"
                        style="width: 100%; margin-bottom: 1rem;">
                        Comprar ahora
                    </button>
                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-secondary btn-lg"
                        style="width: 100%; background: none; border: 2px solid var(--secondary-500); color: var(--secondary-600);">
                        Agregar al carrito
                    </button>
                <?php elseif (isLoggedIn() && $_SESSION['user_id'] == $product['user_id']): ?>
                    <div class="alert alert-warning">Este es tu producto</div>
                <?php else: ?>
                    <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                        class="btn btn-primary btn-lg" style="width: 100%; text-decoration: none; text-align: center;">
                        Iniciar sesión para comprar
                    </a>
                <?php endif; ?>
            </div>

            <!-- Información del vendedor -->
            <div class="card">
                <div class="card-header">
                    <h3 style="font-size: 1.125rem; font-weight: 600;">Información del vendedor</h3>
                </div>
                <div class="card-content">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div
                            style="width: 3rem; height: 3rem; background: var(--primary-500); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                            <?php echo strtoupper(substr($product['seller_full_name'], 0, 1)); ?>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-weight: 600;"><?php echo htmlspecialchars($product['seller_full_name']); ?>
                            </h4>
                            <div
                                style="display: flex; align-items: center; gap: 1rem; font-size: 0.875rem; color: var(--gray-600);">
                                <span><?php echo $product['total_sales']; ?> ventas</span>
                                <span>Desde <?php echo date('Y', strtotime($product['member_since'])); ?></span>
                            </div>
                        </div>
                        <a href="seller.php?id=<?php echo $product['user_id']; ?>" class="btn"
                            style="background: none; border: 1px solid var(--gray-300); font-size: 0.875rem;">
                            Ver perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Descripción -->
    <div class="card mb-8">
        <div class="card-header">
            <h2 class="card-title">Descripción</h2>
        </div>
        <div class="card-content">
            <p style="color: var(--gray-700); line-height: 1.6;">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>
        </div>
    </div>

    <!-- Comentarios -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title" style="display: flex; align-items: center;">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="margin-right: 0.5rem;">
                    <path fill-rule="evenodd"
                        d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                        clip-rule="evenodd" />
                </svg>
                Comentarios y Reseñas (<?php echo count($reviews); ?>)
            </h2>
        </div>
        <div class="card-content">
            <?php if (count($reviews) > 0): ?>
                <div style="margin-bottom: 2rem;">
                    <?php foreach ($reviews as $review): ?>
                        <div
                            style="padding: 1.5rem 0; <?php echo $review !== end($reviews) ? 'border-bottom: 1px solid var(--gray-200);' : ''; ?>">
                            <div style="display: flex; align-items: start; gap: 1rem;">
                                <div
                                    style="width: 2.5rem; height: 2.5rem; background: var(--secondary-500); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; flex-shrink: 0;">
                                    <?php echo strtoupper(substr($review['full_name'], 0, 1)); ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                        <h4 style="font-weight: 600;"><?php echo htmlspecialchars($review['full_name']); ?></h4>
                                        <div style="display: flex;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg class="star" fill="currentColor" viewBox="0 0 20 20"
                                                    style="width: 1rem; height: 1rem; color: <?php echo $i <= $review['rating'] ? '#fbbf24' : '#d1d5db'; ?>;">
                                                    <path
                                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <span
                                            style="font-size: 0.875rem; color: var(--gray-500);"><?php echo timeAgo($review['created_at']); ?></span>
                                    </div>
                                    <p style="color: var(--gray-700); line-height: 1.6; margin-bottom: 0.75rem;">
                                        <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                    </p>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <button class="btn"
                                            style="background: none; border: none; color: var(--gray-500); font-size: 0.875rem; padding: 0.25rem 0;">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"
                                                style="margin-right: 0.25rem;">
                                                <path
                                                    d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                                            </svg>
                                            Útil (<?php echo $review['helpful_count']; ?>)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center" style="padding: 2rem 0;">
                    <svg width="48" height="48" style="color: var(--gray-300); margin: 0 auto 1rem;" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                            clip-rule="evenodd" />
                    </svg>
                    <h3 style="font-weight: 600; color: var(--gray-800); margin-bottom: 0.5rem;">Aún no hay comentarios</h3>
                    <p style="color: var(--gray-600);">Sé el primero en comprar y dejar una reseña</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .image-thumb.active {
        border-color: var(--primary-500) !important;
    }
</style>

<script>
    function changeMainImage(imageUrl, index) {
        document.querySelector('#mainImage img').src = imageUrl;

        // Update active thumbnail
        document.querySelectorAll('.image-thumb').forEach(thumb => {
            thumb.classList.remove('active');
        });
        document.querySelectorAll('.image-thumb')[index].classList.add('active');
    }

    function toggleFavorite() {
        <?php if (isLoggedIn()): ?>
            fetch('api/favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'toggle',
                    product_id: <?php echo $product_id; ?>
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.is_favorite ? 'Agregado a favoritos' : 'Eliminado de favoritos', 'success');
                    } else {
                        showAlert(data.message || 'Error al actualizar favoritos', 'error');
                    }
                });
        <?php else: ?>
            showAlert('Debes iniciar sesión para agregar a favoritos', 'warning');
            setTimeout(() => {
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            }, 2000);
        <?php endif; ?>
    }
</script>

<?php include 'includes/footer.php'; ?>