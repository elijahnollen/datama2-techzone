<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';
require_once 'login.php'; // Reuse your logAction function

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$data = json_decode(file_get_contents("php://input"));

if ($data && !empty($data->email) && !empty($data->new_password)) {
    $email = trim($data->email);
    $newPass = $data->new_password;

    $tables = ['employee', 'customer'];
    $foundTable = null;

    // Identify which table holds this email
    foreach ($tables as $table) {
        $checkUrl = SUPABASE_URL . "/rest/v1/$table?email_address=eq." . urlencode($email);
        $ch = curl_init($checkUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: ".SUPABASE_KEY, "Authorization: Bearer ".SUPABASE_KEY]);
        $res = json_decode(curl_exec($ch));
        curl_close($ch);

        if (is_array($res) && count($res) > 0) {
            $foundTable = $table;
            break;
        }
    }

    if ($foundTable) {
        // Update the password in the database
        $updateUrl = SUPABASE_URL . "/rest/v1/$foundTable?email_address=eq." . urlencode($email);
        $ch = curl_init($updateUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["password" => $newPass]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: ".SUPABASE_KEY,
            "Authorization: Bearer ".SUPABASE_KEY,
            "Content-Type: application/json"
        ]);
        curl_exec($ch);
        curl_close($ch);

        logAction("PASSWORD RECOVERY: User [$email] in [$foundTable] reset their password.");
        echo json_encode(["success" => true, "message" => "Password updated."]);
    } else {
        logAction("FAILED RECOVERY: Identity check failed for [$email].");
        echo json_encode(["success" => false, "message" => "Email not found."]);
    }
}