<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Points to your config.php in the same /api folder
require_once 'config.php'; 

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['email']) && !empty($data['password'])) {
    // Normalizing input for the search
    $email = trim($data['email']); 
    $passwordFromInput = $data['password'];

    // FORENSIC FIX: Using 'ilike' instead of 'eq' to ignore capitalization
    $queryUrl = SUPABASE_URL . "/rest/v1/employee?email_address=ilike." . urlencode($email) . "&select=*";

    $ch = curl_init($queryUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY
    ]);

    $response = curl_exec($ch);
    $result = json_decode($response, true); 
    curl_close($ch);

    // If result is empty, even with ilike, the email literally doesn't exist in the table
    if (!empty($result) && isset($result[0])) {
        $user = $result[0]; 
        
        // FORENSIC PASSWORD CHECK: Supports both plain-text and BCRYPT hashes
        $isMatch = ($passwordFromInput === $user['password']) || password_verify($passwordFromInput, $user['password']);
        
        if ($isMatch) {
            logAction("SUCCESS: Admin Login [$email]");
            echo json_encode([
                "success" => true, 
                "message" => "Admin login successful", 
                "name" => $user['employee_name'] ?? "Admin"
            ]);
        } else {
            logAction("FAILURE: Admin [$email] - Incorrect Password");
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid password."]);
        }
    } else {
        logAction("FAILURE: Admin [$email] - User Not Found");
        http_response_code(404);
        echo json_encode([
            "success" => false, 
            "message" => "Admin account not found.",
            "debug_trace" => [
                "sent_email" => $email,
                "supabase_raw" => $response
            ]
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing credentials."]);
}