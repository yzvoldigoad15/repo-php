<?php
require_once __DIR__ . "/../../../vendor/autoload.php"; // Pastikan sudah install Firebase JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "iyasgac0r"; // Pakai secret key yang sama dengan saat buat token

function generateToken($payload, $expiry = 3600) {
    global $secret_key;
    $payload['exp'] = time() + $expiry;
    return JWT::encode($payload, $secret_key, 'HS256');
}

function verifyToken($token) {
    global $secret_key;
    try {
        return JWT::decode($token, new Key($secret_key, 'HS256'));
    } catch (Exception $e) {
        return false;
    }
}
?>
