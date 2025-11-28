<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once('../includes/json_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $error_message = null;

    if (empty($username) || empty($password)) {
        $error_message = "Debes rellenar el usuario y la contraseña.";
    } else {
        $user = getUserByUsername($username);

        if (!$user) {
            $error_message = "El nombre de usuario '{$username}' no está registrado.";
        } else {
            if (password_verify($password, $user['contrasenya'])) {
                
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nom_usuari'];
                $_SESSION['user_email'] = $user['email'];

                setcookie('user_id', $user['id'], time() + 3600, "/");


                header('Location: profile.php');
                exit;

            } else {
                $error_message = "La contraseña es incorrecta.";
            }
        }
    }

    // Si llegamos aquí, es que hubo error
    if ($error_message) {
        echo "<h3 style='color:red'>Error: " . htmlspecialchars($error_message) . "</h3>";
        echo "<p>Hash en base de datos: " . ($user['contrasenya'] ?? 'No usuario') . "</p>"; // Depuración
        echo "<a href='/login.html'>Volver a intentar</a>";
    }

} else {
    header('Location: /login.html');
    exit;
}
?>