<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['email']) && !empty($data['password'])) {
    $email = trim($data['email']);
    $password = $data['password'];

    // 1. Updated query to fetch specific columns since customer_name is gone
    $queryUrl = SUPABASE_URL . "/rest/v1/customer?email_address=eq." . urlencode($email) . "&select=id,first_name,last_name,password,role";

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
        
        // 2. Plain text password comparison
        if ($password === $user['password']) {
            
            // 3. Combine First and Last name for a clean forensic identity
            $firstName = $user['first_name'] ?? '';
            $lastName = $user['last_name'] ?? '';
            $fullName = trim($firstName . " " . $lastName);
            
            if (empty($fullName)) {
                $fullName = "Valued Customer";
            }

            logAction("SUCCESS: Customer Login [$email] - Name: $fullName");
            
            echo json_encode([
                "success" => true, 
                "message" => "Login successful", 
                "name" => $fullName,
                "user_id" => $user['id']
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
