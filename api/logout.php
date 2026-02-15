<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once 'config.php'; // This already has logAction() inside it!

$data = json_decode(file_get_contents("php://input"), true);
$email = !empty($data['email']) ? trim($data['email']) : "Unknown User";

// 1. Log the logout event for your forensic audit
logAction("LOGOUT: User [$email] has logged out.");

// 2. Clear any session data (if you are using sessions)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();

// 3. Send success response
echo json_encode([
    "success" => true,
    "message" => "Logged out successfully"
]);