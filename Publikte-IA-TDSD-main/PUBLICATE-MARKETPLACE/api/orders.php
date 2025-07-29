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

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'details':
            $order_id = intval($_GET['id'] ?? 0);
            
            // Verificar que la orden pertenece al usuario (como vendedor o comprador)
            $query = "SELECT o.*, p.title as product_title, 
                      buyer.full_name as buyer_name, buyer.username as buyer_username,
                      seller.full_name as seller_name, seller.username as seller_username
                      FROM orders o
                      JOIN products p ON o.product_id = p.id
                      JOIN users buyer ON o.buyer_id = buyer.id
                      JOIN users seller ON o.seller_id = seller.id
                      WHERE o.id = :order_id AND (o.buyer_id = :user_id OR o.seller_id = :user_id)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'order' => $order]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'create_from_cart':
            try {
                $db->beginTransaction();
                
                // Obtener items del carrito
                $cart_query = "SELECT c.*, p.title, p.price, p.user_id as seller_id
                               FROM cart c
                               JOIN products p ON c.product_id = p.id
                               WHERE c.user_id = :user_id AND p.status = 'active'";
                $stmt = $db->prepare($cart_query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($cart_items) == 0) {
                    throw new Exception('El carrito está vacío');
                }
                
                // Calcular total
                $total = 0;
                foreach ($cart_items as $item) {
                    $total += $item['price'] * $item['quantity'];
                }
                
                // Verificar saldo del wallet
                $wallet_query = "SELECT wallet_balance FROM users WHERE id = :user_id";
                $stmt = $db->prepare($wallet_query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user_data['wallet_balance'] < $total) {
                    throw new Exception('Saldo insuficiente en el wallet');
                }
                
                // Crear órdenes (una por vendedor)
                $orders_created = [];
                $sellers = [];
                
                foreach ($cart_items as $item) {
                    if (!isset($sellers[$item['seller_id']])) {
                        $sellers[$item['seller_id']] = [];
                    }
                    $sellers[$item['seller_id']][] = $item;
                }
                
                foreach ($sellers as $seller_id => $seller_items) {
                    foreach ($seller_items as $item) {
                        $item_total = $item['price'] * $item['quantity'];
                        $commission = $item_total * 0.05; // 5% de comisión
                        
                        // Crear orden
                        $order_query = "INSERT INTO orders (buyer_id, seller_id, product_id, quantity, total_amount, commission, status) 
                                        VALUES (:buyer_id, :seller_id, :product_id, :quantity, :total_amount, :commission, 'processing')";
                        $stmt = $db->prepare($order_query);
                        $stmt->bindParam(':buyer_id', $user_id);
                        $stmt->bindParam(':seller_id', $seller_id);
                        $stmt->bindParam(':product_id', $item['product_id']);
                        $stmt->bindParam(':quantity', $item['quantity']);
                        $stmt->bindParam(':total_amount', $item_total);
                        $stmt->bindParam(':commission', $commission);
                        $stmt->execute();
                        
                        $order_id = $db->lastInsertId();
                        $orders_created[] = $order_id;
                        
                        // Actualizar stock si es necesario (marcar como vendido si quantity = 1)
                        if ($item['quantity'] == 1) {
                            $update_product = "UPDATE products SET status = 'sold' WHERE id = :product_id";
                            $stmt = $db->prepare($update_product);
                            $stmt->bindParam(':product_id', $item['product_id']);
                            $stmt->execute();
                        }
                    }
                }
                
                // Descontar del wallet del comprador
                $update_buyer_wallet = "UPDATE users SET wallet_balance = wallet_balance - :amount WHERE id = :user_id";
                $stmt = $db->prepare($update_buyer_wallet);
                $stmt->bindParam(':amount', $total);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                // Agregar transacción del comprador
                $buyer_transaction = "INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type) 
                                      VALUES (:user_id, 'expense', :amount, 'Compra de productos', 'order')";
                $stmt = $db->prepare($buyer_transaction);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':amount', $total);
                $stmt->execute();
                
                // Agregar al wallet de cada vendedor (menos comisión)
                foreach ($sellers as $seller_id => $seller_items) {
                    $seller_total = 0;
                    $seller_commission = 0;
                    
                    foreach ($seller_items as $item) {
                        $item_total = $item['price'] * $item['quantity'];
                        $commission = $item_total * 0.05;
                        $seller_total += $item_total - $commission;
                        $seller_commission += $commission;
                    }
                    
                    // Actualizar wallet del vendedor
                    $update_seller_wallet = "UPDATE users SET wallet_balance = wallet_balance + :amount WHERE id = :seller_id";
                    $stmt = $db->prepare($update_seller_wallet);
                    $stmt->bindParam(':amount', $seller_total);
                    $stmt->bindParam(':seller_id', $seller_id);
                    $stmt->execute();
                    
                    // Agregar transacción del vendedor
                    $seller_transaction = "INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type) 
                                           VALUES (:user_id, 'income', :amount, 'Venta de productos (menos comisión)', 'order')";
                    $stmt = $db->prepare($seller_transaction);
                    $stmt->bindParam(':user_id', $seller_id);
                    $stmt->bindParam(':amount', $seller_total);
                    $stmt->execute();
                }
                
                // Limpiar carrito
                $clear_cart = "DELETE FROM cart WHERE user_id = :user_id";
                $stmt = $db->prepare($clear_cart);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                $db->commit();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Pedido creado exitosamente',
                    'orders' => $orders_created
                ]);
                
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'mark_shipped':
            $order_id = intval($input['order_id'] ?? 0);
            $tracking_number = trim($input['tracking_number'] ?? '');
            
            // Verificar que la orden pertenece al vendedor
            $verify_query = "SELECT id FROM orders WHERE id = :order_id AND seller_id = :user_id AND status = 'processing'";
            $stmt = $db->prepare($verify_query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                echo json_encode(['success' => false, 'message' => 'Orden no encontrada o no se puede actualizar']);
                exit;
            }
            
            // Actualizar orden
            $update_query = "UPDATE orders SET status = 'shipped', tracking_number = :tracking_number, updated_at = NOW() 
                             WHERE id = :order_id";
            $stmt = $db->prepare($update_query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':tracking_number', $tracking_number);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Orden marcada como enviada']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}
?>
