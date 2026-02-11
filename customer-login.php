<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['email']) && !empty($data['password'])) {
    $email = trim($data['email']);
    $password = $data['password'];

    // Search query specifically using email_address as confirmed
    $queryUrl = SUPABASE_URL . "/rest/v1/customer?email_address=eq." . urlencode($email) . "&select=*";

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
        
        // Plain text password comparison
        if ($password === $user['password']) {
            logAction("SUCCESS: Customer Login [$email]");
            echo json_encode([
                "success" => true, 
                "message" => "Login successful", 
                "name" => $user['customer_name'] ?? "Valued Customer"
            ]);
        } else {
            logAction("FAILURE: Customer [$email] - Incorrect Password");
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid password."]);
        }
    } else {
        logAction("FAILURE: Customer [$email] - User Not Found");
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "No account found with that email."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Incomplete data provided."]);
}