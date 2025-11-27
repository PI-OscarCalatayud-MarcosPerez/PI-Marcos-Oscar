<?php
$nombre = $correo = $asunto = $telefono = $mensaje = "";
$consentimiento = false;
$errores = [];
$enviado = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $asunto = trim($_POST["asunto"] ?? ""); 
    $telefono = trim($_POST['telefono'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? ''); 
    $consentimiento = isset($_POST['consentimiento']);

    if (empty($nombre)) {
        $errores[] = "Por favor, escribe tu nombre.";
    }

    if (empty($correo)) {
        $errores[] = "Por favor, escribe tu correo electrónico.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    }

    if (empty($asunto)) {
        $errores[] = "Por favor, seleccione un asunto.";
    }

    if (empty($telefono)) {
        $errores[] = "Por favor, ingrese un número de teléfono.";
    } elseif (!preg_match("/^[0-9]{9,15}$/", $telefono)) { 
        $errores[] = "El teléfono debe contener solo números (mínimo 9).";
    }

    if (empty($mensaje)) {
        $errores[] = "Por favor, escriba su mensaje o consulta.";
    }

    if (!$consentimiento) {
        $errores[] = "Debes aceptar el tratamiento de los datos.";
    }

    if (empty($errores)) {
        $enviado = true;
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Estado del Envío - MOKeys</title>
    
    <link rel="stylesheet" href="../css/estilos.css" />
    <link rel="icon" type="img/png" href="img/icono.png" />
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        .mensaje-exito {
            border: 2px solid #4CAF50;
            background-color: #e8f5e9;
            padding: 20px;
            border-radius: 10px;
            color: #2e7d32;
        }
        .mensaje-error {
            border: 2px solid #fa4841;
            background-color: #ffebee;
            padding: 20px;
            border-radius: 10px;
            color: #c62828;
        }
        .btn-volver {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0e273f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-volver:hover {
            background-color: #1f2e43;
        }
        .datos-recibidos p {
            margin: 10px 0;
            font-size: 1.1em;
            color: #333;
        }
    </style>
</head>

<body class="pagina-contacto">

    <div class="aviso-slider">
      <div class="aviso-slider-content">
        <div>Clave de juegos con un 70% de descuento</div>
        <div>¡Nuevas ofertas cada día!</div>
        <div>¡Las claves más baratas de la web!</div>
        <div>Clave de juegos con un 70% de descuento</div>
      </div>
    </div>

    <header>
      <img src="img/imagencolor.webp" alt="Logo" />
      <a href="#" class="users"></a>
      <a href="#" class="carro"></a>
      <nav>
        <ul class="enlaces_navegacion">
          <li><a href="index.html">Inicio</a></li>
          <li><a href="">Comprar</a></li>
          <li><a href="">Vender</a></li>
          <li><a href="contacto.html">Contacto</a></li>
        </ul>
      </nav>
      <form class="busqueda" action="#" method="get">
        <input type="text" placeholder="Buscar producto..." name="q" />
      </form>
    </header>

    <main class="contenedor-formulario-principal">
      <div class="caja-formulario">
        
        <?php if ($enviado): ?>
            <div class="mensaje-exito">
                <h1>¡Formulario enviado correctamente!</h1>
                <p>Hemos recibido tus datos. Nos pondremos en contacto contigo pronto.</p>
                <hr style="border: 0; border-top: 1px solid #ccc; margin: 20px 0;">
                <div class="datos-recibidos">
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($correo); ?></p>
                    <p><strong>Asunto:</strong> <?php echo htmlspecialchars($asunto); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($telefono); ?></p>
                    <p><strong>Mensaje:</strong> <?php echo htmlspecialchars($mensaje); ?></p>
                    <p><strong>Consentimiento:</strong> <?php echo ($consentimiento ? "Aceptado" : "No aceptado"); ?></p>
                </div>
            </div>
            <a href="index.html" class="btn-volver">Volver al Inicio</a>

        <?php elseif (!empty($errores)): ?>
            <div class="mensaje-error">
                <h1>Se han encontrado errores</h1>
                <p>Por favor, revisa los siguientes campos:</p>
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="javascript:history.back()" class="btn-volver" style="background-color: #fa4841;">Volver al Formulario</a>

        <?php else: ?>
            <div style="text-align: center;">
                <h1>Acceso no permitido</h1>
                <p>Debes rellenar el formulario primero.</p>
                <a href="contacto.html" class="btn-volver">Ir a Contacto</a>
            </div>
        <?php endif; ?>

      </div>
    </main>

    <footer>
      <div class="footer-contenedor">
        <div class="footer-logo">
          <img src="img/imagensincolor.png" alt="MOKeys" />
        </div>
        <div class="footer-suscripcion">
          <input type="email" placeholder="Insert your email address here" />
          <button>Comprar ahora</button>
        </div>
        <div class="footer-enlaces">
          <div>
            <h4>Help</h4>
            <a href="#">FAQ</a><br />
            <a href="#">Customer service</a><br />
            <a href="#">How to guides</a>
          </div>
          <div>
            <h4>Other</h4>
            <a href="#">Privacy Policy</a><br />
            <a href="#">Sitemap</a>
          </div>
        </div>
      </div>
    </footer>

</body>
</html>