<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../../includes/json_connect.php';

$error_message = null;
$success_message = null;


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=access_denied');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $nom = trim($_POST['nom']);
    $cognoms = trim($_POST['cognoms']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del email no es válido.";
    } else {
        $updateData = [
            "email" => $email,
            "nom" => $nom,
            "cognoms" => $cognoms
        ];

        $updatedUser = updateUser($user_id, $updateData);

        if ($updatedUser) {
            $success_message = "¡Perfil actualizado con éxito!";
            $_SESSION['user_email'] = $updatedUser['email'];
        } else {
            $error_message = "Error del servidor: No se pudo actualizar el perfil.";
        }
    }
}



$userData = getUserById($user_id);

if (!$userData) {

    session_destroy();
    header('Location: login.php?error=user_not_found');
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    
</head>
<body>

    <div class="container">
        <h2>Mi Perfil</h2>
        <p>Bienvenido, <strong><?= htmlspecialchars($userData['nom_usuari']) ?></strong>.</p>
        <p>Aquí puedes actualizar tus datos personales.</p>
        
        <hr>

        <?php if ($error_message): ?>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <p class="success"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <form action="profile.php" method="POST">
            <div>
                <label for="username">Nombre de usuario (no editable):</label>
                <input type="text" id="username" name="username" 
                       value="<?= htmlspecialchars($userData['nom_usuari']) ?>" readonly>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required
                       value="<?= htmlspecialchars($userData['email']) ?>">
            </div>
            <div>
                <label for="nom">Nombre:</label>
                <input type="text" id="nom" name="nom"
                       value="<?= htmlspecialchars($userData['nom']) ?>">
            </div>
            <div>
                <label for="cognoms">Apellidos:</label>
                <input type="text" id="cognoms" name="cognoms"
                       value="<?= htmlspecialchars($userData['cognoms']) ?>">
            </div>
            <div>
                <button type="submit">Guardar Cambios</button>
            </div>
        </form>

        <div class="logout-link">
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

</body>
</html>