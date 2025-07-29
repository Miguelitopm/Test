<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                
                showAlert('¡Bienvenido de vuelta!', 'success');
                redirect(isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php');
            } else {
                $error = 'Credenciales incorrectas';
            }
        } else {
            $error = 'Credenciales incorrectas';
        }
    }
}

$page_title = 'Iniciar Sesión';
include 'includes/header.php';
?>

<div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0;">
    <div class="card" style="width: 100%; max-width: 400px;">
        <div class="card-header text-center">
            <h1 class="card-title" style="font-size: 2rem; margin-bottom: 0.5rem;">Iniciar Sesión</h1>
            <p style="color: var(--gray-600);">Ingresa a tu cuenta de PUBLICATE</p>
        </div>
        
        <div class="card-content">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-input" required 
                           placeholder="Tu contraseña">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                        Iniciar Sesión
                    </button>
                </div>
                
                <div class="text-center">
                    <p style="color: var(--gray-600);">
                        ¿No tienes cuenta? 
                        <a href="register.php" style="color: var(--primary-600); text-decoration: none; font-weight: 500;">
                            Regístrate aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    if (!validateForm('loginForm')) {
        e.preventDefault();
        showAlert('Por favor completa todos los campos', 'warning');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
