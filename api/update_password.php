<?php
require_once 'config.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['email']) && !empty($data['password']) && !empty($data['table'])) {
    // Normalizing email and table for the request
    $email = trim($data['email']);
    $table = strtolower(trim($data['table'])); // Ensure table name is lowercase
    
    // FORENSIC UPGRADE: Securely hash the password using BCRYPT
    $newPass = password_hash($data['password'], PASSWORD_BCRYPT); 

    // FIX: Using 'ilike' instead of 'eq' to handle case-sensitive emails
    $url = SUPABASE_URL . "/rest/v1/$table?email_address=ilike." . urlencode($email);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['password' => $newPass]));
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

    // If result is empty, the PATCH didn't find a matching row
    if ($httpCode >= 200 && $httpCode < 300 && !empty($result)) {
        logAction("SUCCESS: Password reset for [$email] in table [$table]");
        echo json_encode(["success" => true, "message" => "Password updated successfully!"]);
    } else {
        logAction("FAILURE: Password reset failed for [$email] - HTTP: $httpCode");
        echo json_encode([
            "success" => false, 
            "message" => "Update rejected. Email not found or database error.",
            "forensic_debug" => [
                "http_code" => $httpCode,
                "tried_email" => $email,
                "table" => $table,
                "supabase_raw" => $result
            ]
        ]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Incomplete forensic data: email, table, or password required."]);
}