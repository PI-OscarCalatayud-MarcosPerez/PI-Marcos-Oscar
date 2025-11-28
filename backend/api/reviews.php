<?php
ini_set('display_errors', 0);
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 3600);

session_start();
header('Content-Type: application/json');

// Archivo donde se guardan las reseñas
$jsonFile = __DIR__ . '/../data/reviews.json';

// Asegurar que el archivo existe
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, '[]');
    chmod($jsonFile, 0777); // Permisos para escribir
}

// --- GUARDAR (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'No logueado']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    $reviews = json_decode(file_get_contents($jsonFile), true) ?? [];
    
    $newReview = [
        'productId' => $input['productId'],
        'user' => $_SESSION['username'], // Nombre real del usuario
        'rating' => (int)$input['rating'],
        'comment' => htmlspecialchars($input['comment']),
        'date' => date('Y-m-d')
    ];

    $reviews[] = $newReview;
    
    if(file_put_contents($jsonFile, json_encode($reviews, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error escritura']);
    }
    exit;
}

// --- LEER (GET) ---
$id = $_GET['product_id'] ?? '';
$data = json_decode(file_get_contents($jsonFile), true) ?? [];
$productReviews = array_filter($data, fn($r) => $r['productId'] == $id);

echo json_encode(array_values($productReviews));
?>