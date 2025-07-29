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

// Get status from query parameter
$status = isset($_GET['status']) ? $_GET['status'] : 'active';

// Map status to database values using existing products table statuses
$status_map = [
    'active' => 'active',
    'completed' => 'sold',
    'archived' => 'archived',
    'deleted' => 'deleted'
];

if (!isset($status_map[$status])) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit;
}

$db_status = $status_map[$status];

try {
    // Get listings by status with primary image
    $query = "SELECT p.*, 
                    COALESCE(pi.image_url, '') as image_url,
                    COALESCE(p.views, 0) as views,
                    COALESCE(p.likes, 0) as likes
              FROM products p
              LEFT JOIN (
                  SELECT product_id, image_url
                  FROM product_images 
                  WHERE is_primary = 1
                  
                  UNION ALL
                  
                  SELECT pi2.product_id, pi2.image_url
                  FROM product_images pi2
                  WHERE pi2.product_id NOT IN (
                      SELECT DISTINCT product_id 
                      FROM product_images 
                      WHERE is_primary = 1
                  )
                  AND pi2.id = (
                      SELECT MIN(id) 
                      FROM product_images pi3 
                      WHERE pi3.product_id = pi2.product_id
                  )
              ) pi ON p.id = pi.product_id
              WHERE p.user_id = ? AND p.status = ?
              ORDER BY p.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute([$user_id, $db_status]);

    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process listings to ensure proper image URLs
    foreach ($listings as &$listing) {
        // Set default values for missing fields
        $listing['views'] = intval($listing['views']);
        $listing['likes'] = intval($listing['likes']);

        // Handle image URL
        if (!empty($listing['image_url'])) {
            // If it's already a full URL, use it as is
            if (filter_var($listing['image_url'], FILTER_VALIDATE_URL)) {
                $listing['image_url'] = $listing['image_url'];
            } else {
                // If it's a relative path, make it absolute
                $listing['image_url'] = '../../' . ltrim($listing['image_url'], '/');
            }
        } else {
            // Use placeholder if no image
            $listing['image_url'] = '../../assets/images/placeholder.png';
        }

        // Ensure price is formatted correctly
        $listing['price'] = number_format((float) $listing['price'], 2, '.', '');

        // Ensure title is not empty
        if (empty($listing['title'])) {
            $listing['title'] = 'Sin título';
        }

        // Ensure description is not empty
        if (empty($listing['description'])) {
            $listing['description'] = 'Sin descripción';
        }
    }

    echo json_encode([
        'success' => true,
        'listings' => $listings
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al cargar los productos: ' . $e->getMessage()]);
}
?>