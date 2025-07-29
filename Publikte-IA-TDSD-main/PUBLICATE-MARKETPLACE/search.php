<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Parámetros de búsqueda
$search_query = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$price_range = $_GET['price_range'] ?? '';
$sort = $_GET['sort'] ?? 'relevance';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Construir consulta base
$where_conditions = ["p.status = 'active'"];
$params = [];

if (!empty($search_query)) {
    $where_conditions[] = "(p.title LIKE :search OR p.description LIKE :search OR u.username LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

if (!empty($category)) {
    $where_conditions[] = "c.slug = :category";
    $params[':category'] = $category;
}

if (!empty($price_range)) {
    switch ($price_range) {
        case '0-100':
            $where_conditions[] = "p.price <= 100";
            break;
        case '100-500':
            $where_conditions[] = "p.price > 100 AND p.price <= 500";
            break;
        case '500-1000':
            $where_conditions[] = "p.price > 500 AND p.price <= 1000";
            break;
        case '1000+':
            $where_conditions[] = "p.price > 1000";
            break;
    }
}

$where_clause = implode(' AND ', $where_conditions);

// Ordenamiento
$order_clause = "ORDER BY ";
switch ($sort) {
    case 'price-low':
        $order_clause .= "p.price ASC";
        break;
    case 'price-high':
        $order_clause .= "p.price DESC";
        break;
    case 'rating':
        $order_clause .= "u.rating DESC";
        break;
    case 'reviews':
        $order_clause .= "review_count DESC";
        break;
    case 'newest':
        $order_clause .= "p.created_at DESC";
        break;
    default:
        $order_clause .= "p.views DESC, p.created_at DESC";
}

// Consulta principal
$query = "SELECT p.*, u.username as seller_name, u.location as seller_location, u.rating as seller_rating,
          c.name as category_name, c.slug as category_slug,
          (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
          (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
          FROM products p 
          JOIN users u ON p.user_id = u.id 
          JOIN categories c ON p.category_id = c.id
          WHERE $where_clause 
          $order_clause
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar total de resultados
$count_query = "SELECT COUNT(*) as total
                FROM products p 
                JOIN users u ON p.user_id = u.id 
                JOIN categories c ON p.category_id = c.id
                WHERE $where_clause";

$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_results = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_results / $per_page);

// Obtener categorías para filtros
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = $search_query ? "Resultados para \"$search_query\"" : 'Buscar productos';
include 'includes/header.php';
?>

<div style="padding: 2rem 0;">
    <div class="mb-8">
        <h1 style="font-size: 2.5rem; font-weight: 900; color: var(--gray-800); margin-bottom: 1rem;">
            <?php echo $search_query ? "Resultados para \"" . htmlspecialchars($search_query) . "\"" : "Todos los productos"; ?>
        </h1>
        <p style="color: var(--gray-600);"><?php echo number_format($total_results); ?> productos encontrados</p>
    </div>

    <!-- Filtros -->
    <div class="card mb-8">
        <div class="card-content">
            <form method="GET" id="searchForm" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: end;">
                <!-- Búsqueda -->
                <div style="flex: 1; min-width: 250px;">
                    <label class="form-label">Buscar productos</label>
                    <div style="position: relative;">
                        <svg style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="q" class="form-input" style="padding-left: 2.5rem;" 
                               placeholder="Buscar productos..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                </div>

                <!-- Ordenar -->
                <div style="min-width: 180px;">
                    <label class="form-label">Ordenar por</label>
                    <select name="sort" class="form-select">
                        <option value="relevance" <?php echo $sort == 'relevance' ? 'selected' : ''; ?>>Relevancia</option>
                        <option value="price-low" <?php echo $sort == 'price-low' ? 'selected' : ''; ?>>Precio: menor a mayor</option>
                        <option value="price-high" <?php echo $sort == 'price-high' ? 'selected' : ''; ?>>Precio: mayor a menor</option>
                        <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Mejor calificados</option>
                        <option value="reviews" <?php echo $sort == 'reviews' ? 'selected' : ''; ?>>Más reseñas</option>
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Más recientes</option>
                    </select>
                </div>

                <!-- Categoría -->
                <div style="min-width: 150px;">
                    <label class="form-label">Categoría</label>
                    <select name="category" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['slug']; ?>" <?php echo $category == $cat['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Precio -->
                <div style="min-width: 150px;">
                    <label class="form-label">Precio</label>
                    <select name="price_range" class="form-select">
                        <option value="">Todos los precios</option>
                        <option value="0-100" <?php echo $price_range == '0-100' ? 'selected' : ''; ?>>$0 - $100</option>
                        <option value="100-500" <?php echo $price_range == '100-500' ? 'selected' : ''; ?>>$100 - $500</option>
                        <option value="500-1000" <?php echo $price_range == '500-1000' ? 'selected' : ''; ?>>$500 - $1,000</option>
                        <option value="1000+" <?php echo $price_range == '1000+' ? 'selected' : ''; ?>>$1,000+</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>

            <!-- Filtros activos -->
            <?php if ($category || $price_range): ?>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                    <span style="color: var(--gray-600); font-size: 0.875rem; margin-right: 0.5rem;">Filtros activos:</span>
                    <?php if ($category): ?>
                        <span class="badge" style="background: var(--secondary-100); color: var(--secondary-800); margin-right: 0.5rem;">
                            <?php 
                            $cat_name = array_filter($categories, fn($c) => $c['slug'] == $category);
                            echo htmlspecialchars(reset($cat_name)['name'] ?? $category);
                            ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => ''])); ?>" style="margin-left: 0.25rem; color: inherit;">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if ($price_range): ?>
                        <span class="badge" style="background: var(--secondary-100); color: var(--secondary-800);">
                            <?php echo htmlspecialchars($price_range); ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['price_range' => ''])); ?>" style="margin-left: 0.25rem; color: inherit;">×</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resultados -->
    <?php if (count($products) > 0): ?>
        <div class="grid grid-4 mb-8">
            <?php foreach ($products as $product): ?>
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
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span><?php echo number_format($product['seller_rating'], 1); ?></span>
                            <span>(<?php echo $product['review_count']; ?>)</span>
                        </div>
                        
                        <div class="product-location">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span><?php echo htmlspecialchars($product['seller_location']); ?></span>
                        </div>
                        
                        <div class="product-shipping">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707L16 7.586A1 1 0 0015.414 7H14z"/>
                            </svg>
                            <span><?php echo $product['shipping_type'] == 'free' ? 'Envío gratis' : 'Envío pago'; ?></span>
                        </div>
                        
                        <div class="product-seller">
                            por <a href="seller.php?id=<?php echo $product['user_id']; ?>" class="seller-link"><?php echo htmlspecialchars($product['seller_name']); ?></a>
                        </div>
                        
                        <button class="btn btn-primary" style="width: 100%;" onclick="addToCart(<?php echo $product['id']; ?>)">
                            Agregar al carrito
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 2rem;">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                       class="btn" style="background: none; border: 1px solid var(--gray-300);">Anterior</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                       class="btn <?php echo $i == $page ? 'btn-primary' : ''; ?>" 
                       style="<?php echo $i != $page ? 'background: none; border: 1px solid var(--gray-300); color: var(--gray-700);' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                       class="btn" style="background: none; border: 1px solid var(--gray-300);">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="text-center" style="padding: 4rem 0;">
            <svg width="64" height="64" style="color: var(--gray-300); margin: 0 auto 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--gray-800); margin-bottom: 0.5rem;">No se encontraron productos</h3>
            <p style="color: var(--gray-600); margin-bottom: 1.5rem;">Intenta con otros términos de búsqueda o ajusta los filtros</p>
            <button onclick="clearFilters()" class="btn btn-primary">Limpiar filtros</button>
        </div>
    <?php endif; ?>
</div>

<script>
function clearFilters() {
    window.location.href = 'search.php';
}

// Auto-submit form when filters change
document.querySelectorAll('#searchForm select').forEach(select => {
    select.addEventListener('change', () => {
        document.getElementById('searchForm').submit();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
