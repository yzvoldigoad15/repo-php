<?php
require_once __DIR__ . "/../../config/database.php"; // ✅ Path benar
require_once __DIR__ . "/../../middlewares/rate_limit.php";
rateLimit(10, 60); // Maksimal 10 request per 60 detik

$pdo = getDatabaseConnection(); // ✅ Ambil koneksi database
header('Content-Type: application/json');

session_start();

// Ambil refresh token dari request
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!isset($data['refresh_token'])) {
    http_response_code(400);
    echo json_encode(["error" => "Refresh token is required"]);
    exit;
}

$refresh_token = $data['refresh_token'];

try {
    // Hapus refresh token dari database
    $stmt = $pdo->prepare("UPDATE users SET refresh_token = NULL WHERE refresh_token = ?");
    $stmt->execute([$refresh_token]);

    // Hapus session (jika ada)
    session_destroy();

    echo json_encode(["message" => "Logout successful"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Internal server error", "message" => $e->getMessage()]);
}
?>
