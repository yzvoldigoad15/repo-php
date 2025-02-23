<?php
require_once __DIR__ . '/../middlewares/auth.php';

$admin = authenticate('admin'); // Hanya admin yang bisa akses

echo json_encode([
    "message" => "Welcome, Admin {$admin['username']}!"
]);
?>
