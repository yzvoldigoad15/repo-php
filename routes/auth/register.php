<?php
require_once __DIR__ . '/../../../backend/config/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'];
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    // Cek apakah username sudah ada
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->execute([$username]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo json_encode(["error" => "Username already exists"]);
    http_response_code(400);
    exit;
}

// Jika belum ada, baru insert
$stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->execute([$username, $email, password_hash($password, PASSWORD_BCRYPT)]);

echo json_encode(["success" => "User registered successfully"]);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);

        echo json_encode(["message" => "User registered successfully"]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
?>
