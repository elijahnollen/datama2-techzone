<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Pulls in your Supabase URL and Key from the .env
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $email = trim($data->email);
    $password = $data->password;

    // Use .ilike for case-insensitive email matching
    $queryUrl = SUPABASE_URL . "/rest/v1/customer?email_address=ilike." . urlencode($email) . "&password=eq." . urlencode($password) . "&select=customer_name";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $queryUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $result = json_decode($response);
    curl_close($ch);

    if (!empty($result) && count($result) > 0) {
        // --- REAL-TIME BASH FEEDBACK ---
        error_log("\n[!] CUSTOMER LOGIN: Success");
        error_log("[+] User: " . $email);
        error_log("[+] STATUS: ACCESS GRANTED\n");

        echo json_encode(["success" => true, "name" => $result[0]->customer_name]);
    } else {
        // --- REAL-TIME BASH FEEDBACK ---
        error_log("\n[!] CUSTOMER LOGIN: Attempt");
        error_log("[-] Email: " . $email);
        error_log("[-] STATUS: ACCESS DENIED\n");

        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid Credentials"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete login data"]);
}