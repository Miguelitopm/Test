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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'update_status':
            $product_id = intval($input['product_id'] ?? 0);
            $status = $input['status'] ?? '';
            
            $valid_statuses = ['active', 'archived', 'deleted'];
            if (!in_array($status, $valid_statuses)) {
                echo json_encode(['success' => false, 'message' => 'Estado no válido']);
                exit;
            }
            
            // Verificar que el producto pertenece al usuario
            $check_query = "SELECT id FROM products WHERE id = :product_id AND user_id = :user_id";
            $stmt = $db->prepare($check_query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                exit;
            }
            
            // Actualizar estado
            $update_query = "UPDATE products SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :product_id";
            $stmt = $db->prepare($update_query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':product_id', $product_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar producto']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}
?>
