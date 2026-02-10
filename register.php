<?php
// register.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
    $name = trim($data->name);
    $email = trim($data->email);
    $password = $data->password;

    // STEP 1: Check if the user already exists (Forensic Integrity Check)
    $checkUrl = SUPABASE_URL . "/rest/v1/customer?email_address=eq." . urlencode($email) . "&select=id";
    $ch = curl_init($checkUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: ".SUPABASE_KEY, "Authorization: Bearer ".SUPABASE_KEY]);
    $checkResponse = json_decode(curl_exec($ch));
    curl_close($ch);

    if (!empty($checkResponse)) {
        error_log("[!] REGISTRATION REFUSED: Email $email already exists.");
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Email already registered"]);
        exit;
    }

    // STEP 2: Insert the new customer
    $url = SUPABASE_URL . "/rest/v1/customer";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "customer_name" => $name,
        "email_address" => $email,
        "password" => $password
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 201) {
        // --- LIVE BASH NOTIFICATION ---
        error_log("\n[+] NEW USER REGISTERED");
        error_log("[Name]: " . $name);
        error_log("[Email]: " . $email . "\n");

        echo json_encode(["success" => true, "message" => "Account Created"]);
    } else {
        echo json_encode(["success" => false, "message" => "Registration Failed"]);
    }
}