<?php
require_once __DIR__ . "/../auth_middleware.php";

echo json_encode([
    "message" => "Welcome! You have access to this protected endpoint.",
    "user_id" => $user_id
]);
?>
<?php
require_once __DIR__ . '/../middlewares/auth.php';

$user = authenticate(); // Ambil data user dari token

echo json_encode([
    "message" => "Welcome, {$user['username']}!",
    "user_role" => $user['role']
]);
?>
