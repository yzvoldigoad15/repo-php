<?php
// Load Database Configuration
require_once __DIR__ . "/../../config/database.php";
header('Content-Type: application/json');

// Load JWT Library
require_once __DIR__ . "/../../../vendor/autoload.php";

require_once __DIR__ . "/../../middlewares/rate_limit.php";
rateLimit(10, 60); // Maksimal 10 request per 60 detik

require_once __DIR__ . "/../../helpers/security.php";
$email = sanitizeInput($data['email']);
$password = sanitizeInput($data['password']);


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debugging: Pastikan koneksi database ada
if (!isset($pdo) || $pdo === null) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection not found"]);
    exit;
}

// Ambil data dari request
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if ($data === null || !isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Email and password are required"]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

try {
    // Cek apakah koneksi database valid
    if (!isset($pdo) || $pdo === null) {
        throw new Exception("Database connection not found.");
    }

    // Ambil user berdasarkan email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid email or password"]);
        exit;
    }

    // Verifikasi password
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid email or password"]);
        exit;
    }

    // Secret key buat JWT
    $secret_key = "iyasgac0r";  // Ganti dengan secret key yang kuat
    
    // Generate Access Token (1 jam)
    $access_payload = [
        "user_id" => $user['id'],
        "username" => $user['username'],
        "role" => $user['role'],
        "exp" => time() + (60 * 60)
    ];
    $access_token = JWT::encode($access_payload, $secret_key, 'HS256');

    // Generate Refresh Token (7 hari)
    $refresh_payload = [
        "user_id" => $user['id'],
        "exp" => time() + (7 * 24 * 60 * 60)
    ];
    $refresh_token = JWT::encode($refresh_payload, $secret_key, 'HS256');

    // Simpan refresh token ke database
    $updateStmt = $pdo->prepare("UPDATE users SET refresh_token = ? WHERE id = ?");
    $updateStmt->execute([$refresh_token, $user['id']]);

    // Kirim response sukses
    echo json_encode([
        "access_token" => $access_token,
        "refresh_token" => $refresh_token
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Internal server error", "message" => $e->getMessage()]);
}
?>
