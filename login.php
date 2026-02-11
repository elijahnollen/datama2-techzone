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

    $tables = ['employee', 'customer'];
    $user = null;
    $foundTable = '';

    foreach ($tables as $table) {
        $url = SUPABASE_URL . "/rest/v1/$table?email_address=eq." . urlencode($email) . "&select=*";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: ".SUPABASE_KEY, "Authorization: Bearer ".SUPABASE_KEY]);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        if (is_array($response) && count($response) > 0) {
            $user = $response[0];
            $foundTable = $table;
            break; 
        }
    }

    if ($user) {
        // Changed from password_verify() to a simple string comparison
        if ($password === $user->password) {
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "role" => ($foundTable === 'employee') ? $user->employee_role : 'customer'
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
}