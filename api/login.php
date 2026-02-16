<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = isset($data['email']) ? trim($data['email']) : '';
$passwordInput = isset($data['password']) ? trim($data['password']) : '';

if (empty($email) || empty($passwordInput)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email and password are required."]);
    exit;
}

// Fetch user from Supabase
$queryUrl = SUPABASE_URL . "/rest/v1/employee?email_address=eq." . urlencode($email) . "&select=*";

$ch = curl_init($queryUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: " . SUPABASE_KEY,
    "Authorization: Bearer " . SUPABASE_KEY
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
if (empty($result) || !isset($result[0])) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Admin account not found."]);
    exit;
}

$user = $result[0];
$storedPassword = trim($user['password']); // Trim to remove whitespace

$loginSuccess = false;

// 1️⃣ Try password_verify (normal hashed case)
if (password_verify($passwordInput, $storedPassword)) {
    $loginSuccess = true;
} 
// 2️⃣ Fallback: allow plain-text password (legacy/truncated hash)
else if ($passwordInput === $storedPassword) {
    $loginSuccess = true;

    // Automatically re-hash the plain-text password and update Supabase
    $newHash = password_hash($passwordInput, PASSWORD_BCRYPT);
    $updateUrl = SUPABASE_URL . "/rest/v1/employee?id=eq." . $user['id'];

    $ch = curl_init($updateUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["password" => $newHash]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json"
    ]);
    curl_exec($ch);
    curl_close($ch);
}

if ($loginSuccess) {
    echo json_encode([
        "success" => true,
        "message" => "Login successful.",
        "name" => $user['first_name'] ?? 'Admin'
    ]);
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid password."]);
}
?>
