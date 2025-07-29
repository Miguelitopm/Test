<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $location = trim($_POST['location']);
    
    // Validaciones
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($location)) {
        $error = 'Por favor completa todos los campos';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Verificar si el email ya existe
        $query = "SELECT id FROM users WHERE email = :email OR username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'El email o nombre de usuario ya están registrados';
        } else {
            // Crear usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (username, email, password, full_name, location, wallet_balance) 
                      VALUES (:username, :email, :password, :full_name, :location, 100.00)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':location', $location);
            
            if ($stmt->execute()) {
                $user_id = $db->lastInsertId();
                
                // Agregar transacción de bienvenida
                $query = "INSERT INTO wallet_transactions (user_id, type, amount, description) 
                          VALUES (:user_id, 'recharge', 100.00, 'Bono de bienvenida')";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                $success = '¡Cuenta creada exitosamente! Recibes $100 de bono de bienvenida.';
                
                // Auto login
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;
                
                showAlert('¡Bienvenido a PUBLICATE! Recibes $100 de bono de bienvenida.', 'success');
                redirect('index.php');
            } else {
                $error = 'Error al crear la cuenta. Intenta nuevamente.';
            }
        }
    }
}

$page_title = 'Registrarse';
include 'includes/header.php';
?>

<div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0;">
    <div class="card" style="width: 100%; max-width: 500px;">
        <div class="card-header text-center">
            <h1 class="card-title" style="font-size: 2rem; margin-bottom: 0.5rem;">Crear Cuenta</h1>
            <p style="color: var(--gray-600);">Únete a PUBLICATE y recibe $100 de bono</p>
        </div>
        
        <div class="card-content">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label for="full_name" class="form-label">Nombre completo</label>
                    <input type="text" id="full_name" name="full_name" class="form-input" required 
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                           placeholder="Tu nombre completo">
                </div>
                
                <div class="form-group">
                    <label for="username" class="form-label">Nombre de usuario</label>
                    <input type="text" id="username" name="username" class="form-input" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           placeholder="usuario123">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="location" class="form-label">Ubicación</label>
                    <input type="text" id="location" name="location" class="form-input" required 
                           value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>"
                           placeholder="Ciudad, Provincia">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-input" required 
                           placeholder="Mínimo 6 caracteres">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required 
                           placeholder="Repite tu contraseña">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                        Crear Cuenta
                    </button>
                </div>
                
                <div class="text-center">
                    <p style="color: var(--gray-600);">
                        ¿Ya tienes cuenta? 
                        <a href="login.php" style="color: var(--primary-600); text-decoration: none; font-weight: 500;">
                            Inicia sesión aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    if (!validateForm('registerForm')) {
        e.preventDefault();
        showAlert('Por favor completa todos los campos', 'warning');
        return;
    }
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        showAlert('Las contraseñas no coinciden', 'error');
        return;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        showAlert('La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
