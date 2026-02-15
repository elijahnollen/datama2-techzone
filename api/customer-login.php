<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';

// Handle preflight CORS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!empty($data['email']) && !empty($data['password'])) {
    $email = strtolower(trim($data['email']));
    $passwordInput = $data['password'];

    // FORENSIC SEARCH: Using ilike for case-insensitive email matching
    $queryUrl = SUPABASE_URL . "/rest/v1/customer?email_address=ilike." . urlencode($email) . "&select=id,first_name,last_name,password";

    $ch = curl_init($queryUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY
    ]);

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    if (!empty($result) && isset($result[0])) {
        $user = $result[0];

        // VERIFICATION: Check the BCRYPT hash 
        if (password_verify($passwordInput, $user['password'])) {
            $fullName = trim(($user['first_name'] ?? '') . " " . ($user['last_name'] ?? ''));
            
            echo json_encode([
                "success" => true,
                "name" => $fullName ?: "Valued Customer",
                "message" => "Login successful"
            ]);
        } else {
            // Security Tip: Generic error messages prevent email harvesting
            echo json_encode(["success" => false, "message" => "Invalid email or password."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email or password."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Please provide both email and password."]);
}