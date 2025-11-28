<?php
ini_set('display_errors', 0);
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 3600);

session_start();
header('Content-Type: application/json');

echo json_encode([
    'isLoggedIn' => isset($_SESSION['user_id']),
    'user' => $_SESSION['username'] ?? null
]);
?>