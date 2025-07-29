<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$listing_id = $input['listing_id'] ?? 0;
$status = $input['status'] ?? '';

// Validate input using existing products table status values
if (!$listing_id || !in_array($status, ['active', 'archived', 'deleted'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    // Verify ownership using existing products table
    $check_query = "SELECT id FROM products WHERE id = :listing_id AND user_id = :user_id";
    $stmt = $db->prepare($check_query);
    $stmt->bindParam(':listing_id', $listing_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para modificar esta publicación']);
        exit;
    }
    
    // Update status in existing products table
    $update_query = "UPDATE products SET status = :status, updated_at = NOW() WHERE id = :listing_id";
    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':listing_id', $listing_id);
    $stmt->execute();
    
    // Get status message
    $status_messages = [
        'active' => 'Publicación activada correctamente',
        'archived' => 'Publicación archivada correctamente',
        'deleted' => 'Publicación eliminada correctamente'
    ];
    
    echo json_encode([
        'success' => true,
        'message' => $status_messages[$status] ?? 'Publicación actualizada correctamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la publicación: ' . $e->getMessage()]);
}
?>
