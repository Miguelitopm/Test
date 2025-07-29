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
    if (isset($_GET['action']) && $_GET['action'] == 'balance') {
        $query = "SELECT wallet_balance FROM users WHERE id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['balance' => floatval($result['wallet_balance'])]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'recharge':
            $amount = floatval($input['amount'] ?? 0);
            
            if ($amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'Monto inválido']);
                exit;
            }
            
            if ($amount > 10000) {
                echo json_encode(['success' => false, 'message' => 'Monto máximo: $10,000']);
                exit;
            }
            
            try {
                $db->beginTransaction();
                
                // Actualizar wallet
                $query = "UPDATE users SET wallet_balance = wallet_balance + :amount WHERE id = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':amount', $amount);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                // Agregar transacción
                $transaction_query = "INSERT INTO wallet_transactions (user_id, type, amount, description) 
                                      VALUES (:user_id, 'recharge', :amount, 'Recarga de wallet')";
                $stmt = $db->prepare($transaction_query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':amount', $amount);
                $stmt->execute();
                
                $db->commit();
                
                echo json_encode(['success' => true, 'message' => 'Wallet recargado exitosamente']);
                
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Error al recargar wallet']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}
?>
