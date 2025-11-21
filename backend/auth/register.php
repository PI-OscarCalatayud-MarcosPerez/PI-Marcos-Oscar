<?php
session_start();

// Ajuste de ruta para buscar en backend/includes/
require_once '../includes/json_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $error_message = null;

    // 1. Validaciones básicas
    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "Todos los campos son obligatorios.";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del email no es válido.";
    }
    else {
        // 2. Comprobar si el usuario ya existe (función de json_connect.php)
        $existing_error = checkUserExists($username, $email);
        
        if ($existing_error) {
            $error_message = $existing_error;
        } else {
            // 3. Crear el usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $newUser = [
                "nom_usuari" => $username,
                "contrasenya" => $hashed_password,
                "email" => $email,
                "nom" => "", 
                "cognoms" => "", 
                "data_registre" => date('c')
            ];

            // 4. Guardar (función de json_connect.php)
            // Nota: Asumo que tu json_connect tiene createUser. 
            // Si usaste el ejemplo anterior que llamamos 'saveUser', cámbialo aquí.
            $createdUser = createUser($newUser); 

            if ($createdUser) {
                // ÉXITO: Redirigir al login para que entre
                header('Location: /login.html');
                exit;
            } else {
                $error_message = "Error del servidor: No se pudo registrar el usuario.";
            }
        }
    }

    // SI HUBO ERROR
    if ($error_message) {
        echo "<h3 style='color:red'>Error en el registro</h3>";
        echo "<p>" . htmlspecialchars($error_message) . "</p>";
        echo "<a href='/register.html'>Volver a intentar</a>";
    }

} else {
    // Si entran sin POST, mandar al formulario
    header('Location: /register.html');
    exit;
}
?>