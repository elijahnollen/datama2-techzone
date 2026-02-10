<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $email = trim($data->email);
    $password = $data->password;

    $queryUrl = SUPABASE_URL . "/rest/v1/employee?email_address=ilike." . urlencode($email) . "&password=eq." . urlencode($password) . "&select=employee_role,first_name";

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
        // --- THIS PRINTS TO YOUR BASH TERMINAL ---
        error_log("\n[!] SECURITY ALERT: Admin Login Detected!");
        error_log("[+] User: " . $email);
        error_log("[+] STATUS: ACCESS GRANTED\n");

        echo json_encode(["isAdmin" => true, "name" => $result[0]->first_name]);
    } else {
        // --- THIS PRINTS TO YOUR BASH TERMINAL ---
        error_log("\n[!] SECURITY ALERT: Unauthorized Attempt!");
        error_log("[-] Email: " . $email);
        error_log("[-] STATUS: ACCESS DENIED\n");

        http_response_code(401);
        echo json_encode(["isAdmin" => false, "message" => "Denied"]);
    }
}