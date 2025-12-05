<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permitir peticiones desde el frontend si es necesario

// Rutas de archivos
$commentsFile = __DIR__ . '/../data/comentarios.json';

// Función para leer JSON
function readJson($file) {
    if (!file_exists($file)) return [];
    $json = file_get_contents($file);
    return json_decode($json, true) ?? [];
}

// Función para guardar JSON
function saveJson($file, $data) {
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// --- GET: Obtener comentarios y estado de sesión ---
if ($method === 'GET' && $action === 'get') {
    $productId = $_GET['product_id'] ?? null;
    
    // Verificar si el usuario está logueado
    $isLoggedIn = isset($_SESSION['user_id']);
    $currentUserId = $_SESSION['user_id'] ?? null;
    $currentUserRole = $_SESSION['role'] ?? 'user';

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Falta el ID del producto']);
        exit;
    }

    $allComments = readJson($commentsFile);
    
    // 1. FILTRAR: Solo los comentarios de ESTE juego
    $productComments = array_filter($allComments, function($c) use ($productId) {
        // Convertimos a string ambos para asegurar que coinciden "1" con 1
        return strval($c['product_id']) === strval($productId);
    });

    // 2. ORDENAR: Más recientes primero
    usort($productComments, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    // 3. PROCESAR: Añadir datos útiles para el frontend
    $responseComments = array_map(function($c) use ($currentUserId, $currentUserRole) {
        // Verificar si yo le di like
        $c['liked_by_me'] = $currentUserId && in_array($currentUserId, $c['likes'] ?? []);
        // Contar likes
        $c['likes_count'] = count($c['likes'] ?? []);
        // Permiso de borrado (dueño o admin)
        $c['can_delete'] = ($currentUserId && $c['user_id'] == $currentUserId) || $currentUserRole === 'admin';
        return $c;
    }, $productComments);

    // Devolvemos flag de login y la lista
    echo json_encode([
        'is_logged_in' => $isLoggedIn,
        'comments' => array_values($responseComments)
    ]);
    exit;
}

// --- POST: Guardar Comentario ---
if ($method === 'POST' && $action === 'add') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    if(empty($input['comment']) || empty($input['rating'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos (comentario o puntuación)']);
        exit;
    }

    $newComment = [
        'id' => uniqid('cmt_'),
        'product_id' => $input['product_id'], // IMPORTANTE: Guardamos a qué juego pertenece
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? 'Usuario',
        'comment' => htmlspecialchars($input['comment']),
        'rating' => (int)$input['rating'],
        'timestamp' => date('c'),
        'likes' => []
    ];

    $comments = readJson($commentsFile);
    $comments[] = $newComment;
    saveJson($commentsFile, $comments);

    echo json_encode(['success' => true, 'comment' => $newComment]);
    exit;
}

// --- POST: Me Gusta (Like) ---
if ($method === 'POST' && $action === 'like') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Inicia sesión para valorar']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $commentId = $input['comment_id'];
    $userId = $_SESSION['user_id'];

    $comments = readJson($commentsFile);
    $updated = false;
    $likesCount = 0;
    $likedByMe = false;

    foreach ($comments as &$c) {
        if ($c['id'] === $commentId) {
            if (!isset($c['likes'])) $c['likes'] = [];

            if (in_array($userId, $c['likes'])) {
                // Quitar like
                $c['likes'] = array_diff($c['likes'], [$userId]);
                $likedByMe = false;
            } else {
                // Poner like
                $c['likes'][] = $userId;
                $likedByMe = true;
            }
            $c['likes'] = array_values($c['likes']);
            $likesCount = count($c['likes']);
            $updated = true;
            break;
        }
    }

    if ($updated) {
        saveJson($commentsFile, $comments);
        echo json_encode(['success' => true, 'likes' => $likesCount, 'liked' => $likedByMe]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Comentario no encontrado']);
    }
    exit;
}

// --- DELETE: Borrar Comentario ---
if ($method === 'DELETE') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $commentId = $input['comment_id'];
    $currentUserId = $_SESSION['user_id'];
    $currentUserRole = $_SESSION['role'] ?? 'user';

    $comments = readJson($commentsFile);
    $originalCount = count($comments);
    
    $comments = array_filter($comments, function($c) use ($commentId, $currentUserId, $currentUserRole) {
        if ($c['id'] === $commentId) {
            // Permitir borrado si es el autor O es administrador
            if ($c['user_id'] == $currentUserId || $currentUserRole === 'admin') {
                return false; // Eliminar del array
            }
        }
        return true; // Mantener en el array
    });

    if (count($comments) < $originalCount) {
        saveJson($commentsFile, array_values($comments));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso o no existe']);
    }
    exit;
}
?>