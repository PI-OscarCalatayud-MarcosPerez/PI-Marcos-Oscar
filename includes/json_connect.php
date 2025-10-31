<?php
define('JSON_SERVER_URL', 'http://localhost:3001');


function checkUserExists($username, $email) {
    $url_user = JSON_SERVER_URL . "/usuaris?nom_usuari=" . urlencode($username);
    $result_user = @file_get_contents($url_user); 
    
    if ($result_user !== FALSE) {
        $data_user = json_decode($result_user, true);
        if (!empty($data_user)) {
            return "El nombre de usuario '{$username}' ya está en uso.";
        }
    }

    $url_email = JSON_SERVER_URL . "/usuaris?email=" . urlencode($email);
    $result_email = @file_get_contents($url_email);

    if ($result_email !== FALSE) {
        $data_email = json_decode($result_email, true);
        if (!empty($data_email)) {
            return "El email '{$email}' ya está registrado.";
        }
    }

    return null; 
}


function createUser($userData) {
    $url = JSON_SERVER_URL . "/usuaris";
    $jsonData = json_encode($userData);

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/json',
            'content' => $jsonData,
            'ignore_errors' => true 
        ]
    ];

    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return false;
    }


    if (strpos($http_response_header[0], '201') !== false) {
        return json_decode($result, true); 
    } else {
        return false;
    }
}

function getUserByUsername($username) {
    $url = JSON_SERVER_URL . "/usuaris?nom_usuari=" . urlencode($username);

    $result = @file_get_contents($url);

    if ($result === FALSE) {
        return null;
    }

    $data = json_decode($result, true);

    if (empty($data)) {
        return null;
    }

    return $data[0];
}

function getUserById($id) {
    $url = JSON_SERVER_URL . "/usuaris/" . $id;

    $result = @file_get_contents($url);

    if ($result === FALSE) {
        return null;
    }

    return json_decode($result, true);
}


function updateUser($id, $data) {
    $url = JSON_SERVER_URL . "/usuaris/" . $id;
    $jsonData = json_encode($data);

    $options = [
        'http' => [
            'method'  => 'PATCH', 
            'header'  => 'Content-Type: application/json',
            'content' => $jsonData,
            'ignore_errors' => true
        ]
    ];

    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return false;
    }

    if (strpos($http_response_header[0], '200') !== false) {
        return json_decode($result, true); 
    } else {
        return false;
    }
}