<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Absolute pathing to prevent crashes
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Config missing."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// Input Processing
$input = file_get_contents("php://input");
$data = json_decode($input, true);

$email = !empty($data['email']) ? trim($data['email']) : null;
$password = !empty($data['password']) ? $data['password'] : null;
$table = !empty($data['table']) ? strtolower(trim($data['table'])) : null;

// Forensic Validation
if ($email && $password && $table && $table !== 'undefined') {
    
    // Hash for login compliance
    $hashedPass = password_hash($password, PASSWORD_BCRYPT); 

    // ilike search to prevent case-sensitive mismatches
    $url = SUPABASE_URL . "/rest/v1/$table?email_address=ilike." . urlencode($email);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['password' => $hashedPass]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json",
        "Prefer: return=representation" 
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300 && !empty($result)) {
        echo json_encode(["success" => true, "message" => "Password updated!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Database rejected update. Email not found in table '$table'."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Incomplete data. Restart from Step 1."]);
}