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
    if (isset($_GET['action']) && $_GET['action'] == 'count') {
        $query = "SELECT COUNT(*) as count FROM cart WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['count' => $result['count']]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $product_id = $input['product_id'] ?? 0;
            $quantity = $input['quantity'] ?? 1;
            
            // Verificar que el producto existe y está activo
            $query = "SELECT id, user_id FROM products WHERE id = :product_id AND status = 'active'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                exit;
            }
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // No permitir agregar productos propios
            if ($product['user_id'] == $user_id) {
                echo json_encode(['success' => false, 'message' => 'No puedes agregar tus propios productos']);
                exit;
            }
            
            // Verificar si ya está en el carrito
            $query = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Actualizar cantidad
                $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
                $new_quantity = $cart_item['quantity'] + $quantity;
                
                $query = "UPDATE cart SET quantity = :quantity WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':quantity', $new_quantity);
                $stmt->bindParam(':id', $cart_item['id']);
                $stmt->execute();
            } else {
                // Agregar nuevo item
                $query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':quantity', $quantity);
                $stmt->execute();
            }
            
            echo json_encode(['success' => true]);
            break;
            
        case 'remove':
            $product_id = $input['product_id'] ?? 0;
            
            $query = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
            break;
            
        case 'update':
            $product_id = $input['product_id'] ?? 0;
            $quantity = $input['quantity'] ?? 1;
            
            if ($quantity <= 0) {
                $query = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->execute();
            } else {
                $query = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':quantity', $quantity);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->execute();
            }
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}
?>
