<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php'; 

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['email']) && !empty($data['password'])) {
    $email = trim($data['email']); 
    $passwordFromInput = $data['password'];

    // Searching using your actual column name: email_address
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

    if (!empty($result) && isset($result[0])) {
        $user = $result[0]; 
        
        // Verification logic
        $isMatch = ($passwordFromInput === $user['password']) || password_verify($passwordFromInput, $user['password']);
        
        if ($isMatch) {
            echo json_encode([
                "success" => true, 
                "message" => "Admin login successful", 
                "name" => $user['first_name'] . " " . $user['last_name']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid password."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Admin account not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing credentials."]);
}