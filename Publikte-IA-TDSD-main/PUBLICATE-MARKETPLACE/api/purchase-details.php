<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de orden requerido']);
    exit;
}

$order_id = (int)$_GET['order_id'];

try {
    // Obtener detalles completos de la compra
    $query = "SELECT 
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
        WHERE o.id = :order_id AND o.buyer_id = :user_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':order_id', $order_id);
    $stmt->bindValue(':user_id', $user_id);
    $stmt->execute();
    
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$purchase) {
        echo json_encode(['success' => false, 'message' => 'Compra no encontrada']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'purchase' => $purchase
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener detalles: ' . $e->getMessage()]);
}
?>
