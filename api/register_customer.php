<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Ensure config exists
$configPath = __DIR__ . '/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal Server Error: Config Missing"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// Get input
$input = file_get_contents("php://input");
$data = json_decode($input);

if ($data && !empty($data->email) && !empty($data->password)) {
    
    // Hash password for security
    $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT); 
    
    // Prepare Supabase Payload for the CUSTOMER table
    $payload = [
        "email_address" => trim($data->email),
        "password"      => $hashedPassword,
        "first_name"    => $data->firstName,
        "last_name"     => $data->lastName,
        "address"       => $data->address,
        "phone_number"  => $data->phone,
        "role"          => "customer"
    ];

    // Target the customer table
    $url = SUPABASE_URL . "/rest/v1/customer";
    
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

    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(["success" => true, "message" => "Registration successful!"]);
    } else {
        $error = json_decode($response, true);
        // Check if error is duplicate email (Code 23505)
        $msg = (isset($error['code']) && $error['code'] === '23505') ? "Email already exists." : "Database error.";
        echo json_encode(["success" => false, "message" => $msg]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Required fields missing."]);
}