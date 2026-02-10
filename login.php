<?php
// login.php (Root Folder)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// --- BRING IN THE SECURE CONFIG ---
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// --- AUDIT LOGGER ---
function logAction($action, $details) {
    $timestamp = date("Y-m-d H:i:s");
    $logEntry = "[AUDIT LOG][$timestamp] ACTION: $action | DETAILS: " . json_encode($details) . PHP_EOL;
    file_put_contents("audit_log.txt", $logEntry, FILE_APPEND);
    error_log($logEntry);
}

// --- HANDLE POST REQUEST ---
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $email = trim($data->email);
    $password = $data->password;

    $ch = curl_init();
    
    // Using constants from config.php
    $url = SUPABASE_URL . "/rest/v1/employee?email_address=ilike." . urlencode($email) . "&password=eq." . urlencode($password) . "&select=employee_role,first_name";

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $result = json_decode($response);
    curl_close($ch);

    if (!empty($result)) {
        logAction("ADMIN_AUTH_SUCCESS", ["email" => $email]);
        echo json_encode([
            "isAdmin" => true,
            "name" => $result[0]->first_name,
            "role" => $result[0]->employee_role
        ]);
    } else {
        logAction("ADMIN_LOGIN_FAILED", ["email" => $email]);
        http_response_code(401);
        echo json_encode(["isAdmin" => false, "message" => "Invalid Credentials"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data"]);
}
