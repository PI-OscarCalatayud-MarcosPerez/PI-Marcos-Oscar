<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 3600);
// ------------------------------------------------------------------------

session_start();

require_once __DIR__ . '/../includes/json_connect.php';

$error_message = null;
$success_message = null;

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.html?error=Sesion_caducada_o_perdia'); 
    exit;
}

$user_id = $_SESSION['user_id'];

// Procesar Formulario
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
            $error_message = "Error: No se pudo guardar en el JSON.";
        }
    }
}

// Cargar datos actuales
$userData = getUserById($user_id);

if (!$userData) {
    session_destroy();
    header('Location: /login.html?error=Usuario_no_encontrado');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - MOKeys</title>
    
    <link rel="stylesheet" href="/css/estilos.css">
    <link rel="stylesheet" href="/css/formulario.css">
    <link rel="icon" type="img/png" href="/img/icono.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body class="pagina-contacto">

    <div class="aviso-slider">
      <div class="aviso-slider-content">
        <div>Clave de juegos con un 70% de descuento</div>
        <div>¡Nuevas ofertas cada día!</div>
        <div>¡Las claves más baratas de la web!</div>
      </div>
    </div>

    <header>
      <img src="/img/imagencolor.webp" alt="Logo" />
      <a href="#" class="users"></a>
      <a href="#" class="carro"></a>
      <nav>
        <ul class="enlaces_navegacion">
          <li><a href="/index.html">Inicio</a></li>
          <li><a href="#">Comprar</a></li>
          <li><a href="#">Vender</a></li>
          <li><a href="/contacto.html">Contacto</a></li>
          <li><a href="../formulario.html">Subir productos</a></li>
        </ul>
      </nav>
      <form class="busqueda" action="#" method="get">
        <input type="text" placeholder="Buscar producto..." name="q" />
      </form>
    </header>

    <main class="contenedor-formulario-principal">
        <div class="caja-formulario" style="max-width: 500px;">
            <h2 style="color: #0e273f; text-align: center;">Mi Perfil</h2>
            <p style="text-align: center;">Hola, <strong><?= htmlspecialchars($userData['nom_usuari']) ?></strong> 👋</p>
            
            <?php if ($error_message): ?>
                <p class="error" style="text-align:center; color:#fa4841;"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div style="background-color: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px;">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <form action="profile.php" method="POST">
                
                <div class="grupo-input">
                    <label>Usuario (No editable):</label>
                    <input type="text" value="<?= htmlspecialchars($userData['nom_usuari']) ?>" disabled style="background-color: #eee; color: #666;">
                </div>

                <div class="grupo-input">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($userData['email']) ?>">
                </div>

                <div class="grupo-input">
                    <label for="nom">Nombre:</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($userData['nom'] ?? '') ?>">
                </div>

                <div class="grupo-input">
                    <label for="cognoms">Apellidos:</label>
                    <input type="text" id="cognoms" name="cognoms" value="<?= htmlspecialchars($userData['cognoms'] ?? '') ?>">
                </div>

                <button type="submit" class="btn-enviar">Guardar Cambios</button>
            </form>

            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <a href="logout.php" style="color: #fa4841; font-weight: bold; text-decoration: none;">Cerrar Sesión</a>
            </div>
        </div>
    </main>

    <footer>
      <div class="footer-contenedor">
        <div class="footer-logo">
          <img src="/img/imagensincolor.png" alt="MOKeys" />
        </div>
        <div class="footer-suscripcion">
          <input type="email" placeholder="Insert your email address here" />
          <button>Comprar ahora</button>
        </div>
        <div class="footer-enlaces">
          <div>
            <h4>Help</h4>
            <a href="#">FAQ</a>
            <br><a href="#">Customer service</a>
          </div>
          <div>
            <h4>Other</h4>
            <a href="#">Privacy Policy</a>
            <br><a href="#">Sitemap</a>
          </div>
        </div>
      </div>
    </footer>

</body>
</html>