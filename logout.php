<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';
// Include your existing logAction function (or paste it here)
function logAction($message) {
    file_put_contents('audit_log.txt', "[" . date("Y-m-d H:i:s") . "] $message" . PHP_EOL, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$data = json_decode(file_get_contents("php://input"));

if ($data && !empty($data->email)) {
    $email = trim($data->email);
    
    // Record the exit in the audit log
    logAction("USER LOGOUT: [$email] has successfully disconnected.");

    echo json_encode([
        "success" => true,
        "message" => "Logout logged successfully"
    ]);
} else {
    echo json_encode(["success" => false, "message" => "No user data provided"]);
}