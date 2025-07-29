<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Obtener items del carrito
$query = "SELECT c.*, p.title, p.price, p.original_price, p.shipping_type, p.status,
          u.username as seller_name, u.location as seller_location,
          (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
          FROM cart c
          JOIN products p ON c.product_id = p.id
          JOIN users u ON p.user_id = u.id
          WHERE c.user_id = :user_id AND p.status = 'active'
          ORDER BY c.created_at DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = 0; // Envío gratis
$total = $subtotal + $shipping;

$page_title = 'Mi Carrito';
include 'includes/header.php';
?>

<div style="padding: 2rem 0;">
    <?php if (count($cart_items) == 0): ?>
        <div style="text-align: center; padding: 4rem 0;">
            <svg width="96" height="96" style="color: var(--gray-300); margin: 0 auto 1.5rem;" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
            </svg>
            <h1 style="font-size: 1.875rem; font-weight: bold; color: var(--gray-800); margin-bottom: 1rem;">Tu carrito está vacío</h1>
            <p style="color: var(--gray-600); margin-bottom: 2rem;">¡Descubre productos increíbles y comienza a comprar!</p>
            <a href="search.php" class="btn btn-primary btn-lg">Explorar productos</a>
        </div>
    <?php else: ?>
        <div style="max-width: 1200px; margin: 0 auto;">
            <h1 style="font-size: 2.5rem; font-weight: 900; color: var(--gray-800); margin-bottom: 2rem;">Mi Carrito</h1>

            <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
                <!-- Items del carrito -->
                <div>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="card" style="margin-bottom: 1rem;">
                            <div class="card-content">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="width: 6rem; height: 6rem; flex-shrink: 0;">
                                        <img src="<?php echo $item['primary_image'] ?: 'assets/images/placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['title']); ?>"
                                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;"
                                             onerror="this.src='assets/images/placeholder.jpg'">
                                    </div>

                                    <div style="flex: 1; min-width: 0;">
                                        <a href="product.php?id=<?php echo $item['product_id']; ?>" style="text-decoration: none;">
                                            <h3 style="font-weight: 600; color: var(--gray-800); margin-bottom: 0.25rem; line-height: 1.4;"><?php echo htmlspecialchars($item['title']); ?></h3>
                                        </a>
                                        <p style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.25rem;">por <?php echo htmlspecialchars($item['seller_name']); ?></p>
                                        <p style="font-size: 0.875rem; color: var(--primary-600); margin-bottom: 0.5rem;"><?php echo $item['shipping_type'] == 'free' ? 'Envío gratis' : 'Envío pago'; ?></p>

                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-size: 1.25rem; font-weight: bold; color: var(--gray-900);"><?php echo formatPrice($item['price']); ?></span>
                                            <?php if ($item['original_price'] && $item['original_price'] > $item['price']): ?>
                                                <span style="font-size: 0.875rem; color: var(--gray-500); text-decoration: line-through;"><?php echo formatPrice($item['original_price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div style="display: flex; flex-direction: column; align-items: end; gap: 1rem;">
                                        <!-- Controles de cantidad -->
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <button onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)" 
                                                    class="btn" style="width: 2rem; height: 2rem; padding: 0; background: none; border: 1px solid var(--gray-300);">
                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                            <span style="width: 2rem; text-align: center; font-weight: 500;"><?php echo $item['quantity']; ?></span>
                                            <button onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)" 
                                                    class="btn" style="width: 2rem; height: 2rem; padding: 0; background: none; border: 1px solid var(--gray-300);">
                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Botón eliminar -->
                                        <button onclick="removeFromCart(<?php echo $item['product_id']; ?>)" 
                                                class="btn" style="background: none; border: none; color: var(--red-500); font-size: 0.875rem; padding: 0.25rem 0;">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="margin-right: 0.25rem;">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Resumen del pedido -->
                <div>
                    <div class="card" style="position: sticky; top: 1rem;">
                        <div class="card-header">
                            <h2 class="card-title">Resumen del pedido</h2>
                        </div>
                        <div class="card-content">
                            <div style="margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Subtotal (<?php echo count($cart_items); ?> productos)</span>
                                    <span><?php echo formatPrice($subtotal); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Envío</span>
                                    <span style="color: var(--green-600); font-weight: 500;">Gratis</span>
                                </div>
                            </div>

                            <div style="border-top: 1px solid var(--gray-200); padding-top: 1rem; margin-bottom: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 1.125rem; font-weight: bold;">
                                    <span>Total</span>
                                    <span><?php echo formatPrice($total); ?></span>
                                </div>
                            </div>

                            <button onclick="proceedToCheckout()" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: 1rem;">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="margin-right: 0.5rem;">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                </svg>
                                Proceder al pago
                            </button>

                            <div class="text-center">
                                <a href="search.php" style="color: var(--gray-600); text-decoration: none;">Continuar comprando</a>
                            </div>

                            <!-- Badges de seguridad -->
                            <div style="padding-top: 1rem; border-top: 1px solid var(--gray-200); margin-top: 1rem;">
                                <div style="display: flex; justify-content: center; gap: 0.5rem; font-size: 0.75rem;">
                                    <span class="badge" style="background: none; border: 1px solid var(--gray-300); color: var(--gray-600);">
                                        🔒 Compra Protegida
                                    </span>
                                    <span class="badge" style="background: none; border: 1px solid var(--gray-300); color: var(--gray-600);">
                                        ✅ Pago Seguro
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(productId, newQuantity) {
    if (newQuantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    updateCartQuantity(productId, newQuantity);
}

function proceedToCheckout() {
    // Verificar saldo del wallet
    fetch('api/wallet.php?action=balance')
    .then(response => response.json())
    .then(data => {
        const total = <?php echo $total; ?>;
        if (data.balance >= total) {
            if (confirm(`¿Confirmar compra por ${formatPrice(total)}? Se descontará de tu wallet.`)) {
                processOrder();
            }
        } else {
            showAlert(`Saldo insuficiente. Necesitas ${formatPrice(total - data.balance)} más en tu wallet.`, 'warning');
            setTimeout(() => {
                window.location.href = 'wallet.php';
            }, 3000);
        }
    });
}

function processOrder() {
    fetch('api/orders.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'create_from_cart'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('¡Pedido realizado exitosamente!', 'success');
            setTimeout(() => {
                window.location.href = 'purchases.php';
            }, 2000);
        } else {
            showAlert(data.message || 'Error al procesar el pedido', 'error');
        }
    })
    .catch(error => {
        showAlert('Error al procesar el pedido', 'error');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
