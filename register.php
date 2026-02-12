<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';

// Handle preflight CORS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$json = file_get_contents("php://input");
$data = json_decode($json);

if ($data && !empty($data->email) && !empty($data->password)) {
    $email = trim($data->email);
    $password = $data->password; 
    $role = $data->role ?? 'customer';
    
    // Determine which table to use
    $tableName = ($role === 'employee' || $role === 'admin') ? 'employee' : 'customer';

    // 1. Shared columns for BOTH tables
    // We now rely on first_name and last_name instead of a single name column
    $payload = [
        "email_address" => $email,
        "password"      => $password,
        "first_name"    => $data->first_name ?? null,
        "last_name"     => $data->last_name ?? null
    ];

    // 2. Table-specific logic
    if ($tableName === 'customer') {
        // REMOVED: customer_name is no longer in the payload
        $payload["phone_number"]  = $data->phone_number ?? null;
        $payload["address"]       = $data->address ?? null;
        $payload["role"]          = 'customer'; // Explicitly set role for forensic tracking
    } else {
        // Employee/Admin specific columns
        $payload["employee_role"] = $role;
    }

    // 3. Send to Supabase
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
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 4. Forensic Logging
    if ($httpCode >= 200 && $httpCode < 300) {
        logAction("REGISTRATION SUCCESS: New $role account created for [$email]");
        echo json_encode([
            "success" => true,
            "message" => "Registration successful as $role."
        ]);
    } else {
        logAction("REGISTRATION FAILURE: Could not create account for [$email]. Error code: $httpCode");
        http_response_code($httpCode);
        echo $response; // Return Supabase error for debugging
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Required fields (email, password) are missing."]);
}
?>