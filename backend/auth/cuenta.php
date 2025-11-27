<?php
// Configuración de sesión (¡Igual que en los otros archivos!)
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 3600);

session_start();

// EL SEMÁFORO 🚦
if (isset($_SESSION['user_id'])) {
    // Si la sesión existe, vamos al perfil
    header('Location: profile.php');
} else {
    // Si no existe, vamos al formulario de login
    header('Location: /login.html');
}
exit;
?>