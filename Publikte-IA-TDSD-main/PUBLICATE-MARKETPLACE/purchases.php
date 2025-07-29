<?php
require_once 'config/config.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Obtener información del usuario
$user_query = "SELECT username, email FROM users WHERE id = :user_id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindValue(':user_id', $user_id);
$user_stmt->execute();
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Obtener estadísticas de compras
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
    SUM(total_amount) as total_spent
    FROM orders WHERE buyer_id = :user_id";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->bindValue(':user_id', $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Paginación
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtros
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Construir query con filtros
$where_conditions = ["o.buyer_id = :user_id"];
$params = [':user_id' => $user_id];

if ($status_filter && $status_filter !== 'all') {
    $where_conditions[] = "o.status = :status";
    $params[':status'] = $status_filter;
}

if ($date_filter) {
    switch ($date_filter) {
        case 'today':
            $where_conditions[] = "DATE(o.created_at) = CURDATE()";
            break;
        case 'week':
            $where_conditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $where_conditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
}

$where_clause = implode(' AND ', $where_conditions);

// Obtener compras con información del producto y vendedor
$purchases_query = "SELECT 
    o.id as order_id,
    o.quantity,
    o.total_amount,
    o.status,
    o.created_at,
    p.id as product_id,
    p.title as product_title,
    p.price as product_price,
    p.description as product_description,
    u.username as seller_name,
    u.email as seller_email,
    (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as product_image
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN users u ON o.seller_id = u.id
    WHERE $where_clause
    ORDER BY o.created_at DESC
    LIMIT :limit OFFSET :offset";

$purchases_stmt = $db->prepare($purchases_query);
foreach ($params as $key => $value) {
    $purchases_stmt->bindValue($key, $value);
}
$purchases_stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$purchases_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$purchases_stmt->execute();
$purchases = $purchases_stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar total de registros para paginación
$count_query = "SELECT COUNT(*) as total FROM orders o WHERE $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Compras - PUBLICATE</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/purchases.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="purchases-container">
        <div class="purchases-header">
            <h1><i class="fas fa-shopping-bag"></i> Mis Compras</h1>
            <p>Gestiona y revisa todas tus compras realizadas</p>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_orders'] ?? 0); ?></h3>
                    <p>Total Compras</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['delivered_orders'] ?? 0); ?></h3>
                    <p>Entregados</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['shipped_orders'] ?? 0); ?></h3>
                    <p>Enviados</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['processing_orders'] ?? 0); ?></h3>
                    <p>Procesando</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></h3>
                    <p>Total Gastado</p>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="status">Estado:</label>
                    <select name="status" id="status">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Todos</option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>
                            Procesando</option>
                        <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Enviados
                        </option>
                        <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>
                            Entregados</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>
                            Cancelados</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="date">Fecha:</label>
                    <select name="date" id="date">
                        <option value="" <?php echo $date_filter === '' ? 'selected' : ''; ?>>Todas</option>
                        <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Hoy</option>
                        <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>Última semana
                        </option>
                        <option value="month" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>Último mes
                        </option>
                    </select>
                </div>
                <button type="submit" class="filter-btn">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="purchases.php" class="clear-filters-btn">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </form>
        </div>

        <!-- Lista de compras -->
        <div class="purchases-list">
            <?php if (empty($purchases)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>No tienes compras</h3>
                    <p>Cuando realices compras, aparecerán aquí.</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> Ir a comprar
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($purchases as $purchase): ?>
                    <div class="purchase-card" data-order-id="<?php echo $purchase['order_id']; ?>">
                        <div class="purchase-image">
                            <img src="<?php echo $purchase['product_image'] ? htmlspecialchars($purchase['product_image']) : 'assets/images/placeholder.png'; ?>"
                                alt="<?php echo htmlspecialchars($purchase['product_title']); ?>"
                                onerror="this.src='assets/images/placeholder.png'">
                        </div>
                        <div class="purchase-info">
                            <div class="purchase-header">
                                <h3><?php echo htmlspecialchars($purchase['product_title']); ?></h3>
                                <span class="status-badge status-<?php echo $purchase['status']; ?>">
                                    <?php
                                    $status_text = [
                                        'processing' => 'Procesando',
                                        'shipped' => 'Enviado',
                                        'delivered' => 'Entregado',
                                        'cancelled' => 'Cancelado'
                                    ];
                                    echo $status_text[$purchase['status']] ?? $purchase['status'];
                                    ?>
                                </span>
                            </div>
                            <div class="purchase-details">
                                <p><strong>Vendedor:</strong> <?php echo htmlspecialchars($purchase['seller_name']); ?></p>
                                <p><strong>Cantidad:</strong> <?php echo $purchase['quantity']; ?></p>
                                <p><strong>Precio unitario:</strong>
                                    $<?php echo number_format($purchase['product_price'], 2); ?></p>
                                <p><strong>Total:</strong> $<?php echo number_format($purchase['total_amount'], 2); ?></p>
                                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($purchase['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <div class="purchase-actions">
                            <button class="btn btn-secondary"
                                onclick="viewPurchaseDetails(<?php echo $purchase['order_id']; ?>)">
                                <i class="fas fa-eye"></i> Ver detalles
                            </button>
                            <a href="product.php?id=<?php echo $purchase['product_id']; ?>" class="btn btn-outline">
                                <i class="fas fa-external-link-alt"></i> Ver producto
                            </a>
                            <?php if ($purchase['status'] === 'delivered'): ?>
                                <button class="btn btn-primary"
                                    onclick="contactSeller('<?php echo htmlspecialchars($purchase['seller_email']); ?>')">
                                    <i class="fas fa-envelope"></i> Contactar vendedor
                                </button>
                            <?php endif; ?>
                            <?php if ($purchase['status'] === 'processing'): ?>
                                <button class="btn btn-danger" onclick="cancelPurchase(<?php echo $purchase['order_id']; ?>)">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>"
                        class="pagination-btn">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>"
                        class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>"
                        class="pagination-btn">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para detalles de compra -->
    <div id="purchaseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detalles de la compra</h2>
                <span class="close" onclick="closeModal('purchaseModal')">&times;</span>
            </div>
            <div class="modal-body" id="purchaseModalBody">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/purchases.js"></script>
</body>

</html>