<?php

define('JSON_FILE', __DIR__ . '/../data/users.json');
/**
 * Helper: Obtener la ESTRUCTURA COMPLETA del JSON
 * Devuelve siempre un array con la clave 'usuaris'
 */
function getJsonData()
{
    // Si no existe el archivo, devolvemos la estructura base vacía
    if (!file_exists(JSON_FILE)) {
        return ['usuaris' => []];
    }

    $content = file_get_contents(JSON_FILE);
    $data = json_decode($content, true);

    // Si el archivo está vacío o corrupto, o no tiene la clave 'usuaris', lo arreglamos
    if (!is_array($data) || !isset($data['usuaris'])) {
        return ['usuaris' => []];
    }

    return $data;
}

/**
 * Helper: Guardar la estructura completa
 */
function saveJsonData($data)
{
    return file_put_contents(JSON_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Buscar un usuario por nombre (Para Login)
 */
function getUserByUsername($username)
{
    $data = getJsonData();

    // Iteramos SOLO sobre la lista 'usuaris'
    foreach ($data['usuaris'] as $user) {
        if (isset($user['nom_usuari']) && $user['nom_usuari'] === $username) {
            return $user;
        }
    }
    return null;
}

/**
 * Comprobar si existe usuario o email (Para Registro)
 */
function checkUserExists($username, $email)
{
    $data = getJsonData();

    foreach ($data['usuaris'] as $user) {
        // Saltamos registros corruptos si los hubiera
        if (!isset($user['nom_usuari']) || !isset($user['email'])) {
            continue;
        }

        if ($user['nom_usuari'] === $username) {
            return "El nombre de usuario '$username' ya está en uso.";
        }
        if ($user['email'] === $email) {
            return "El correo electrónico '$email' ya está registrado.";
        }
    }
    return null;
}

/**
 * Crear un nuevo usuario (Para Registro)
 * Lo guarda DENTRO de 'usuaris'
 */
function createUser($newUser)
{
    // 1. Cargar todos los datos
    $data = getJsonData();

    // 2. Calcular ID mirando solo la lista de 'usuaris'
    $lastId = 0;
    $ids = array_column($data['usuaris'], 'id');

    if (!empty($ids)) {
        $lastId = max($ids);
    }

    // Asignamos el nuevo ID
    $newUser['id'] = $lastId + 1;

    // 3. AÑADIR A LA LISTA 'usuaris'
    $data['usuaris'][] = $newUser;

    // 4. Guardar el objeto completo
    if (saveJsonData($data)) {
        return true;
    }
    return false;
}
/**
 * Obtener usuario por ID (Para el Perfil)
 */
function getUserById($id)
{
    $data = getJsonData(); // Usamos la función que lee 'usuaris'
    foreach ($data['usuaris'] as $user) {
        if ($user['id'] == $id) {
            return $user;
        }
    }
    return null;
}

/**
 * Actualizar usuario (Para guardar cambios del Perfil)
 */
function updateUser($id, $newData)
{
    $data = getJsonData();
    $updatedUser = null;
    $index = -1;

    // Buscamos el usuario y su posición
    foreach ($data['usuaris'] as $key => $user) {
        if ($user['id'] == $id) {
            $index = $key;
            break;
        }
    }

    if ($index !== -1) {
        // Mantenemos los datos viejos (id, password, fecha) y sobrescribimos los nuevos
        $user = $data['usuaris'][$index];
        $user['email'] = $newData['email'];
        $user['nom'] = $newData['nom'];
        $user['cognoms'] = $newData['cognoms'];

        // Guardamos en el array temporal
        $data['usuaris'][$index] = $user;
        $updatedUser = $user;

        // Guardamos en el archivo
        if (saveJsonData($data)) {
            return $updatedUser;
        }
    }
    return false;
}
?>