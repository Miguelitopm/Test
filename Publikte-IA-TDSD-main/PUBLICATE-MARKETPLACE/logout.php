<?php
require_once 'config/config.php';

session_destroy();
showAlert('Sesión cerrada correctamente', 'success');
redirect('index.php');
?>
