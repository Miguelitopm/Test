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

try {
    // Get seller profile information
    $query = "SELECT id, username, email, full_name, location, phone, avatar, 
                     wallet_balance, rating, total_reviews, total_sales, member_since, is_active
              FROM users 
              WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        echo json_encode(['success' => false, 'message' => 'Perfil no encontrado']);
        exit;
    }

    // Get active listings count
    $query = "SELECT COUNT(*) as count FROM products WHERE user_id = ? AND status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $active_listings = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get counts for each status
    $counts = [];
    $statuses = ['active', 'sold', 'archived', 'deleted'];

    foreach ($statuses as $status) {
        $query = "SELECT COUNT(*) as count FROM products WHERE user_id = ? AND status = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id, $status]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Map database status to frontend status
        $frontend_status = $status;
        if ($status === 'sold') {
            $frontend_status = 'completed';
        }

        $counts[$frontend_status] = intval($count);
    }

    // Add active listings to profile
    $profile['active_listings'] = intval($active_listings);

    // Ensure numeric fields are properly formatted
    $profile['wallet_balance'] = number_format((float) $profile['wallet_balance'], 2, '.', '');
    $profile['rating'] = number_format((float) $profile['rating'], 1, '.', '');
    $profile['total_reviews'] = intval($profile['total_reviews']);
    $profile['total_sales'] = intval($profile['total_sales']);

    // Handle avatar URL
    if (!empty($profile['avatar'])) {
        if (!filter_var($profile['avatar'], FILTER_VALIDATE_URL)) {
            $profile['avatar'] = '../../' . ltrim($profile['avatar'], '/');
        }
    }

    echo json_encode([
        'success' => true,
        'profile' => $profile,
        'counts' => $counts
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al cargar el perfil: ' . $e->getMessage()]);
}
?>