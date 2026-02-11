<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$json = file_get_contents("php://input");
$data = json_decode($json);

if ($data && !empty($data->email) && !empty($data->password)) {
    $email = trim($data->email);
    $password = $data->password; 
    $role = $data->role ?? 'customer';
    
    $tableName = ($role === 'employee' || $role === 'admin') ? 'employee' : 'customer';

    // 1. Shared columns for BOTH tables
    $payload = [
        "email_address" => $email,
        "password"      => $password,
        "first_name"    => $data->first_name ?? null,
        "last_name"     => $data->last_name ?? null
    ];

    // 2. Table-specific columns
    if ($tableName === 'customer') {
        $payload["customer_name"] = $data->customer_name ?? ($data->first_name . ' ' . $data->last_name);
        $payload["phone_number"]  = $data->phone_number ?? null;
        $payload["address"]       = $data->address ?? null;
    } else {
        // Only for employee table
        $payload["employee_role"] = $role;
    }

    $url = SUPABASE_URL . "/rest/v1/" . $tableName;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json",
        "Prefer: return=representation" 
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    echo $response;
}