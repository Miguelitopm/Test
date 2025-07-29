<?php
session_start();

// Configuración del sitio
define('SITE_NAME', 'PUBLICATE');
define('SITE_URL', 'http://localhost/publicate');
define('UPLOAD_PATH', 'assets/uploads/');
define('MAX_IMAGES_PER_PRODUCT', 5);
define('LISTING_COST', 1.00);

// Configuración de la base de datos
require_once 'database.php';

// Funciones auxiliares
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function formatPrice($price) {
    return '$' . number_format($price, 0, ',', '.');
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Hace ' . $time . ' segundos';
    if ($time < 3600) return 'Hace ' . floor($time/60) . ' minutos';
    if ($time < 86400) return 'Hace ' . floor($time/3600) . ' horas';
    if ($time < 2592000) return 'Hace ' . floor($time/86400) . ' días';
    if ($time < 31536000) return 'Hace ' . floor($time/2592000) . ' meses';
    return 'Hace ' . floor($time/31536000) . ' años';
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function showAlert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}
?>
