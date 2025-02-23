<?php
require_once __DIR__ . "/config/database.php"; // Path sudah benar
require_once __DIR__ . "/../vendor/autoload.php"; // Perbaiki path autoload.php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "iyasgac0r"; // Sesuai secret key login

// Cek apakah Authorization Header ada
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: No token provided"]);
    exit;
}

// Ambil token dari header
$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

echo json_encode([
    "debug" => [
        "secret_key_used" => $secret_key,
        "token_received" => $token
    ]
]);
exit;


try {
    // Decode token
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    
    // Cek apakah `user_id` ada dalam token
    if (!isset($decoded->user_id)) {
        throw new Exception("Token does not contain user_id");
    }

    // Simpan user ID ke variabel global
    $user_id = $decoded->user_id;

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: Invalid token", "message" => $e->getMessage()]);
    exit;
}
?>
