<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../../includes/json_connect.php';

$error_message = null;


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

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
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    
</head>
<body>

    <main>
        <h2>Iniciar Sesión</h2>

        <?php if ($error_message): ?>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST" novalidate>
            <div>
                <label for="username">Nombre de usuario:</label>
                <input type="text" id="username" name="username" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div>
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <button type="submit">Entrar</button>
            </div>
        </form>
        <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>.</p>

    </main>

</body>
</html>