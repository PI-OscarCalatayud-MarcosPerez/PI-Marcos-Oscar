<?php
// backend/api/products.php

// Permitir acceso desde el frontend
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Ruta al archivo JSON usando rutas del sistema (backend/data/products.json)
// __DIR__ es 'backend/api', así que subimos un nivel (..) y entramos en data
$jsonFile = __DIR__ . '/../data/products.json';

if (file_exists($jsonFile)) {
    // Leer el contenido del archivo y enviarlo tal cual
    echo file_get_contents($jsonFile);
} else {
    // Si falla, devolver un error JSON válido
    http_response_code(404);
    echo json_encode(["error" => "Archivo de productos no encontrado"]);
}
?>