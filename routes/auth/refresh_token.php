<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once __DIR__ . "/../../../vendor/autoload.php";
require_once __DIR__ . "/../../middlewares/rate_limit.php";
rateLimit(10, 60); // Maksimal 10 request per 60 detik


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if ($data === null || !isset($data['refresh_token'])) {
    http_response_code(400);
    echo json_encode(["error" => "Refresh token is required"]);
    exit;
}

$refresh_token = $data['refresh_token'];
$secret_key = "iyasgac0r";

try {
    // Decode refresh token
    $decoded = JWT::decode($refresh_token, new Key($secret_key, 'HS256'));
    $user_id = $decoded->user_id;

    // Cek apakah refresh token valid di database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND refresh_token = ?");
    $stmt->execute([$user_id, $refresh_token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid refresh token"]);
        exit;
    }

    // Generate Access Token baru (1 jam)
    $access_payload = [
        "user_id" => $user['id'],
        "username" => $user['username'],
        "exp" => time() + (60 * 60)
    ];
    $access_token = JWT::encode($access_payload, $secret_key, 'HS256');

    // Generate Refresh Token baru (agar tidak bisa reuse refresh token lama)
    $refresh_payload = [
        "user_id" => $user['id'],
        "exp" => time() + (7 * 24 * 60 * 60)
    ];
    $new_refresh_token = JWT::encode($refresh_payload, $secret_key, 'HS256');

    // Update refresh token di database
    $updateStmt = $pdo->prepare("UPDATE users SET refresh_token = ? WHERE id = ?");
    $updateStmt->execute([$new_refresh_token, $user['id']]);

    echo json_encode([
        "access_token" => $access_token,
        "refresh_token" => $new_refresh_token
    ]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token", "message" => $e->getMessage()]);
}
?>
