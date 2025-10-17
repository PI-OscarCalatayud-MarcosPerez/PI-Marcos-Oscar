<h1>backend</h1>
<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $ciclo = trim($_POST["ciclo"] ?? "");
    $telefono = $_POST['telefono'] ?? '';
    $consentimiento = isset($_POST['consentimiento']) ? true : false;


    $errores = [];

    if (empty($nombre)) {
        $errores[] = "Por favor, escribe tu nombre.";
    }

    if (empty($correo)) {
        $errores[] = "Por favor, escribe tu correo electrónico.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    }
    if (empty($ciclo)) {
        $errores[] = "Por favor, selecione un ciclo.";
    }
    if (empty($telefono)) {
        $errores[] = "Por favor, increse un numero de telefono.";
    } elseif (!preg_match("/^[0-9]+$/", $telefono)) {
        $errores[] = "El teléfono solo puede contener números";
    }
    if (!$consentimiento) {
        $errores[] = "Debes aceptar el tratamiento de los datos.";
    }
}
if (empty($errores)) {
    echo "<h2>Formulario enviado correctamente</h2>";
    echo "<p><strong>Nombre:</strong> " . htmlspecialchars($nombre) . "</p>";
    echo "<p><strong>correo:</strong> " . htmlspecialchars($correo) . "</p>";
    echo "<p><strong>Ciclo:</strong> " . htmlspecialchars($ciclo) . "</p>";
    echo "<p><strong>Telefono:</strong> " . htmlspecialchars($telefono) . "</p>";
    echo "<p><strong>Consentimiento:</strong> " . ($consentimiento ? "Sí" : "No") . "</p>";
} else {
    echo "<h3>Se han encontrado errores:</h3><ul>";
    foreach ($errores as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
}


?>