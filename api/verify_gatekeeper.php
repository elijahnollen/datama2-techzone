<?php
// api/verify_gatekeeper.php
header("Content-Type: application/json");
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$inputCode = $data['code'] ?? '';

// Retrieve the code from .env via config.php or getenv
$secretCode = getenv('MASTER_AUTH_CODE') ?: "TZ-2026"; 

if ($inputCode === $secretCode) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Invalid Code"]);
}
?>