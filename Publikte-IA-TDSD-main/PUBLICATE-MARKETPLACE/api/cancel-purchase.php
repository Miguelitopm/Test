<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$order_id = (int)($input['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'ID de orden requerido']);
    exit;
}

try {
    $db->beginTransaction();
    
    // Verificar que la orden pertenece al usuario y está pendiente
    $check_query = "SELECT o.*, p.title as product_title 
                    FROM orders o 
                    JOIN products p ON o.product_id = p.id 
                    WHERE o.id = :order_id AND o.buyer_id = :user_id AND o.status = 'pending'";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindValue(':order_id', $order_id);
    $check_stmt->bindValue(':user_id', $user_id);
    $check_stmt->execute();
    
    $order = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception('Orden no encontrada o no se puede cancelar');
    }
    
    // Cancelar la orden
    $cancel_query = "UPDATE orders SET status = 'cancelled' WHERE id = :order_id";
    $cancel_stmt = $db->prepare($cancel_query);
    $cancel_stmt->bindValue(':order_id', $order_id);
    $cancel_stmt->execute();
    
    // Devolver dinero al wallet del comprador
    $refund_query = "UPDATE users SET wallet_balance = wallet_balance + :amount WHERE id = :user_id";
    $refund_stmt = $db->prepare($refund_query);
    $refund_stmt->bindValue(':amount', $order['total_amount']);
    $refund_stmt->bindValue(':user_id', $user_id);
    $refund_stmt->execute();
    
    // Registrar transacción de reembolso
    $transaction_query = "INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type) 
                          VALUES (:user_id, 'refund', :amount, :description, 'order_cancellation')";
    $transaction_stmt = $db->prepare($transaction_query);
    $transaction_stmt->bindValue(':user_id', $user_id);
    $transaction_stmt->bindValue(':amount', $order['total_amount']);
    $transaction_stmt->bindValue(':description', 'Reembolso por cancelación de: ' . $order['product_title']);
    $transaction_stmt->execute();
    
    // Reactivar el producto si estaba marcado como vendido
    $reactivate_query = "UPDATE products SET status = 'active' WHERE id = :product_id AND status = 'sold'";
    $reactivate_stmt = $db->prepare($reactivate_query);
    $reactivate_stmt->bindValue(':product_id', $order['product_id']);
    $reactivate_stmt->execute();
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Compra cancelada exitosamente. El dinero ha sido devuelto a tu wallet.'
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
