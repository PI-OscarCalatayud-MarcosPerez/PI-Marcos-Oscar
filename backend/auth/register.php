<?php

session_start();


require_once '../../includes/json_connect.php';

$error_message = null;
$success_message = null;


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "Todos los campos son obligatorios.";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del email no es válido.";
    }
    else {
        $existing_error = checkUserExists($username, $email);
        
        if ($existing_error) {
            $error_message = $existing_error;
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $newUser = [
                "nom_usuari" => $username,
                "contrasenya" => $hashed_password,
                "email" => $email,
                "nom" => "", 
                "cognoms" => "", 
                "data_registre" => date('c')
            ];

            $createdUser = createUser($newUser);

            if ($createdUser) {
                $success_message = "¡Usuario registrado con éxito! Ahora puedes iniciar sesión.";
            } else {
                $error_message = "Error del servidor: No se pudo registrar el usuario. Inténtalo más tarde.";
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
    <title>Registro de Usuario</title>
   
</head>
<body>

    <main>
        <h2>Registro de Nuevo Usuario</h2>

        <?php if ($error_message): ?>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <p class="success"><?= htmlspecialchars($success_message) ?></p>
            <p><a href="login.php">Ir a Iniciar Sesión</a></p>
        <?php endif; ?>

        <?php if (!$success_message): ?>
            <form action="register.php" method="POST" novalidate>
                <div>
                    <label for="username">Nombre de usuario:</label>
                    <input type="text" id="username" name="username" required 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div>
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div>
                    <button type="submit">Registrarse</button>
                </div>
            </form>
            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>.</p>
        <?php endif; ?>

    </main>

</body>
</html>