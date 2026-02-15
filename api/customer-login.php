<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['email']) && !empty($data['password'])) {
    $email = strtolower(trim($data['email'])); // Forensic cleaning
    $passwordFromInput = $data['password'];

    // 1. Fetch user data
    $queryUrl = SUPABASE_URL . "/rest/v1/customer?email_address=eq." . urlencode($email) . "&select=id,first_name,last_name,password";

    $ch = curl_init($queryUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY
    ]);

    $response = curl_exec($ch);
    $result = json_decode($response, true); 
    curl_close($ch);

    // --- FORENSIC DEBUG BLOCK ---
    if (empty($result)) {
        echo json_encode([
            "success" => false, 
            "message" => "No account found with that email.",
            "debug_info" => [
                "email_sent" => $email,
                "supabase_raw" => $response, // This shows the raw error from Supabase
                "url_attempted" => $queryUrl
            ]
        ]);
        exit;
    }
    // ----------------------------

    $user = $result[0]; 
    if (password_verify($passwordFromInput, $user['password'])) {
        echo json_encode([
            "success" => true, 
            "message" => "Login successful", 
            "name" => trim(($user['first_name'] ?? '') . " " . ($user['last_name'] ?? '')),
            "user_id" => $user['id']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid password."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Incomplete data."]);
}