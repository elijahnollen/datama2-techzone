<?php
require_once 'config.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$data = json_decode(file_get_contents('php://input'), true);

// Verify we have all 3 pieces of forensic evidence
if (!empty($data['email']) && !empty($data['password']) && !empty($data['table'])) {
    $email = trim($data['email']);
    $table = trim($data['table']);
    $newPass = password_hash($data['password'], PASSWORD_BCRYPT); 

    $url = SUPABASE_URL . "/rest/v1/$table?email_address=eq." . urlencode($email);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['password' => $newPass]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json",
        "Prefer: return=representation" // This is key to seeing if it actually worked
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // If Supabase returns an empty array [], it means no user matched that email
    $result = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300 && !empty($result)) {
        echo json_encode(["success" => true, "message" => "Password updated successfully!"]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Database rejected update. Ensure the email exists in the $table table.",
            "debug_code" => $httpCode,
            "supabase_msg" => $response
        ]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing data: email, table, or password."]);
}