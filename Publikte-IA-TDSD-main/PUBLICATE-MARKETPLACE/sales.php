<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Parámetros de filtrado
$search_term = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Obtener estadísticas del vendedor
$stats_query = "SELECT 
                    COALESCE(SUM(o.total_amount), 0) as total_revenue,
                    COUNT(o.id) as total_sales,
                    (SELECT COUNT(*) FROM products WHERE user_id = :user_id AND status = 'active') as active_listings,
                    COALESCE(SUM(p.views), 0) as total_views
                FROM orders o 
                JOIN products p ON o.product_id = p.id 
                WHERE o.seller_id = :user_id";

$stmt = $db->prepare($stats_query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Construir consulta de ventas con filtros
$where_conditions = ["o.seller_id = :user_id"];
$params = [':user_id' => $user_id];

if (!empty($search_term)) {
    $where_conditions[] = "(p.title LIKE :search OR u.full_name LIKE :search OR u.username LIKE :search)";
    $params[':search'] = '%' . $search_term . '%';
}

if ($filter_status !== 'all') {
    $where_conditions[] = "o.status = :status";
    $params[':status'] = $filter_status;
}

$where_clause = implode(' AND ', $where_conditions);

// Obtener ventas
$sales_query = "SELECT o.*, p.title as product_title, p.price, 
                u.full_name as buyer_name, u.username as buyer_username,
                (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as product_image
                FROM orders o
                JOIN products p ON o.product_id = p.id
                JOIN users u ON o.buyer_id = u.id
                WHERE $where_clause
                ORDER BY o.created_at DESC
                LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($sales_query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar total de ventas para paginación
$count_query = "SELECT COUNT(*) as total FROM orders o
                JOIN products p ON o.product_id = p.id
                JOIN users u ON o.buyer_id = u.id
                WHERE $where_clause";

$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_sales = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

$page_title = 'Mis Ventas';
include 'includes/header.php';
?>

<div style="padding: 2rem 0;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-size: 2.5rem; font-weight: 900; color: var(--gray-800); margin-bottom: 1rem;">Mis Ventas</h1>
            <p style="color: var(--gray-600);">Gestiona y monitorea todas tus ventas</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-4 mb-8">
            <div class="card" style="background: linear-gradient(135deg, var(--primary-500), var(--primary-600)); color: white; border: none; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 6rem; height: 6rem; background: rgba(255, 255, 255, 0.1); border-radius: 50%; transform: translate(1.5rem, -1.5rem);"></div>
                <div class="card-content" style="position: relative; z-index: 10;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 0.875rem; opacity: 0.9; font-weight: 500; margin-bottom: 0.5rem;">Ingresos Totales</p>
                            <p style="font-size: 2rem; font-weight: 900;"><?php echo formatPrice($stats['total_revenue']); ?></p>
                        </div>
                        <svg width="40" height="40" style="opacity: 0.8;" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card" style="background: linear-gradient(135deg, var(--secondary-500), var(--secondary-600)); color: white; border: none; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 6rem; height: 6rem; background: rgba(255, 255, 255, 0.1); border-radius: 50%; transform: translate(1.5rem, -1.5rem);"></div>
                <div class="card-content" style="position: relative; z-index: 10;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 0.875rem; opacity: 0.9; font-weight: 500; margin-bottom: 0.5rem;">Ventas Totales</p>
                            <p style="font-size: 2rem; font-weight: 900;"><?php echo $stats['total_sales']; ?></p>
                        </div>
                        <svg width="40" height="40" style="opacity: 0.8;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-content">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 0.875rem; color: var(--gray-600);">Publicaciones Activas</p>
                            <p style="font-size: 2rem; font-weight: 900; color: var(--primary-600);"><?php echo $stats['active_listings']; ?></p>
                        </div>
                        <svg width="32" height="32" style="color: var(--primary-600);" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 11-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 11-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 112 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 110 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 110-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15.586 13V12a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-content">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 0.875rem; color: var(--gray-600);">Vistas Totales</p>
                            <p style="font-size: 2rem; font-weight: 900; color: var(--secondary-600);"><?php echo number_format($stats['total_views']); ?></p>
                        </div>
                        <svg width="32" height="32" style="color: var(--secondary-600);" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-6">
            <div class="card-content">
                <form method="GET" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: end;">
                    <div style="flex: 1; min-width: 250px;">
                        <div style="position: relative;">
                            <svg style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" name="search" class="form-input" style="padding-left: 2.5rem;" 
                                   placeholder="Buscar por producto o comprador..." 
                                   value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <select name="status" class="form-select" style="min-width: 160px;">
                            <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>Todos</option>
                            <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pendientes</option>
                            <option value="processing" <?php echo $filter_status == 'processing' ? 'selected' : ''; ?>>Procesando</option>
                            <option value="shipped" <?php echo $filter_status == 'shipped' ? 'selected' : ''; ?>>Enviadas</option>
                            <option value="delivered" <?php echo $filter_status == 'delivered' ? 'selected' : ''; ?>>Entregadas</option>
                            <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Canceladas</option>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="margin-right: 0.25rem;">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                            </svg>
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Ventas -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Historial de Ventas (<?php echo $total_sales; ?>)</h2>
            </div>
            <div class="card-content">
                <?php if (count($sales) > 0): ?>
                    <div style="margin-bottom: 2rem;">
                        <?php foreach ($sales as $sale): ?>
                            <div style="display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border: 1px solid var(--gray-200); border-radius: 0.75rem; margin-bottom: 1rem; transition: all 0.2s; background: white;">
                                <div style="width: 4rem; height: 4rem; flex-shrink: 0;">
                                    <img src="<?php echo $sale['product_image'] ?: 'assets/images/placeholder.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($sale['product_title']); ?>"
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;"
                                         onerror="this.src='assets/images/placeholder.jpg'">
                                </div>

                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: start; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <div>
                                            <h3 style="font-weight: 600; color: var(--gray-800); margin-bottom: 0.25rem; line-height: 1.4;"><?php echo htmlspecialchars($sale['product_title']); ?></h3>
                                            <p style="font-size: 0.875rem; color: var(--gray-600);">Comprador: <?php echo htmlspecialchars($sale['buyer_name']); ?></p>
                                            <p style="font-size: 0.875rem; color: var(--gray-500);">Orden #<?php echo $sale['id']; ?></p>
                                        </div>
                                        <span class="badge" style="<?php echo getStatusStyle($sale['status']); ?>">
                                            <?php echo getStatusText($sale['status']); ?>
                                        </span>
                                    </div>

                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <div style="display: flex; align-items: center; gap: 2rem;">
                                            <div>
                                                <p style="font-size: 1.25rem; font-weight: bold; color: var(--gray-900);"><?php echo formatPrice($sale['total_amount']); ?></p>
                                                <p style="font-size: 0.875rem; color: var(--primary-600);">Comisión: <?php echo formatPrice($sale['commission']); ?></p>
                                            </div>
                                            <div>
                                                <p style="font-size: 0.875rem; color: var(--gray-500);">Fecha: <?php echo date('d/m/Y', strtotime($sale['created_at'])); ?></p>
                                                <p style="font-size: 0.875rem; color: var(--gray-500);">Cantidad: <?php echo $sale['quantity']; ?></p>
                                            </div>
                                        </div>

                                        <div style="display: flex; gap: 0.5rem;">
                                            <button onclick="viewOrderDetails(<?php echo $sale['id']; ?>)" 
                                                    class="btn" style="background: none; border: 1px solid var(--gray-300); font-size: 0.875rem; padding: 0.5rem 1rem;">
                                                Ver detalles
                                            </button>
                                            <?php if ($sale['status'] == 'processing'): ?>
                                                <button onclick="markAsShipped(<?php echo $sale['id']; ?>)" 
                                                        class="btn btn-primary" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                                                    Marcar como enviado
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginación -->
                    <?php if ($total_sales > $per_page): ?>
                        <?php $total_pages = ceil($total_sales / $per_page); ?>
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
                    <div class="text-center" style="padding: 3rem 0;">
                        <svg width="64" height="64" style="color: var(--gray-300); margin: 0 auto 1rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 11-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 11-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 112 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 110 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 110-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15.586 13V12a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        <h3 style="font-weight: 600; color: var(--gray-800); margin-bottom: 0.5rem;">No se encontraron ventas</h3>
                        <p style="color: var(--gray-600); margin-bottom: 1.5rem;">
                            <?php echo !empty($search_term) || $filter_status != 'all' ? 'Intenta ajustar los filtros de búsqueda' : 'Aún no has realizado ninguna venta'; ?>
                        </p>
                        <?php if (empty($search_term) && $filter_status == 'all'): ?>
                            <a href="sell.php" class="btn btn-primary">Crear primera publicación</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles de orden -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('orderModal')">&times;</span>
        <div id="orderDetails">
            <!-- Los detalles se cargarán aquí -->
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    fetch(`api/orders.php?action=details&id=${orderId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('orderDetails').innerHTML = `
                <h2 style="margin-bottom: 1rem;">Detalles de la Orden #${data.order.id}</h2>
                <div style="display: grid; gap: 1rem;">
                    <div><strong>Producto:</strong> ${data.order.product_title}</div>
                    <div><strong>Comprador:</strong> ${data.order.buyer_name}</div>
                    <div><strong>Cantidad:</strong> ${data.order.quantity}</div>
                    <div><strong>Total:</strong> ${formatPrice(data.order.total_amount)}</div>
                    <div><strong>Comisión:</strong> ${formatPrice(data.order.commission)}</div>
                    <div><strong>Estado:</strong> <span class="badge" style="${getStatusStyleJS(data.order.status)}">${getStatusTextJS(data.order.status)}</span></div>
                    <div><strong>Fecha:</strong> ${new Date(data.order.created_at).toLocaleDateString()}</div>
                    ${data.order.tracking_number ? `<div><strong>Número de seguimiento:</strong> ${data.order.tracking_number}</div>` : ''}
                    ${data.order.shipping_address ? `<div><strong>Dirección de envío:</strong> ${data.order.shipping_address}</div>` : ''}
                </div>
            `;
            openModal('orderModal');
        } else {
            showAlert('Error al cargar detalles', 'error');
        }
    })
    .catch(error => {
        showAlert('Error al cargar detalles', 'error');
    });
}

function markAsShipped(orderId) {
    const trackingNumber = prompt('Número de seguimiento (opcional):');
    
    fetch('api/orders.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'mark_shipped',
            order_id: orderId,
            tracking_number: trackingNumber
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Orden marcada como enviada', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Error al actualizar orden', 'error');
        }
    })
    .catch(error => {
        showAlert('Error al actualizar orden', 'error');
    });
}

function getStatusStyleJS(status) {
    switch (status) {
        case 'delivered':
            return 'background: #d1fae5; color: #065f46;';
        case 'shipped':
            return 'background: #dbeafe; color: #1e40af;';
        case 'processing':
            return 'background: #fef3c7; color: #92400e;';
        case 'cancelled':
            return 'background: #fee2e2; color: #991b1b;';
        default:
            return 'background: #f3f4f6; color: #374151;';
    }
}

function getStatusTextJS(status) {
    switch (status) {
        case 'delivered': return 'Entregada';
        case 'shipped': return 'Enviada';
        case 'processing': return 'Procesando';
        case 'cancelled': return 'Cancelada';
        default: return 'Pendiente';
    }
}
</script>

<?php 
function getStatusStyle($status) {
    switch ($status) {
        case 'delivered':
            return 'background: #d1fae5; color: #065f46;';
        case 'shipped':
            return 'background: #dbeafe; color: #1e40af;';
        case 'processing':
            return 'background: #fef3c7; color: #92400e;';
        case 'cancelled':
            return 'background: #fee2e2; color: #991b1b;';
        default:
            return 'background: #f3f4f6; color: #374151;';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'delivered': return 'Entregada';
        case 'shipped': return 'Enviada';
        case 'processing': return 'Procesando';
        case 'cancelled': return 'Cancelada';
        default: return 'Pendiente';
    }
}

include 'includes/footer.php'; 
?>
