<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['email'])) {
    $email = trim($data['email']);

    // 1. Check if user exists in your customer table
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

    if (!empty($result)) {
        // In a real scenario, this is where you'd trigger the PHPMailer logic
        logAction("FORGOT PASSWORD: Valid request for [$email]. User found.");
        echo json_encode([
            "success" => true,
            "message" => "Account found. Reset instructions would be sent to your Gmail now."
        ]);
    } else {
        logAction("FORGOT PASSWORD: Fail for [$email]. User not in database.");
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "This email is not registered with us."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Please enter your email address."]);
}