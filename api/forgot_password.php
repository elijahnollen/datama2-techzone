<?php
ob_start(); 
require_once __DIR__ . '/config.php'; 

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

function writeToLog($message) {
    $log_file = __DIR__ . '/audit_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $entry = "[$timestamp] IP: $ip | $message" . PHP_EOL;
    file_put_contents($log_file, $entry, FILE_APPEND);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['email'])) {
    $email = trim($data['email']);
    $userFound = false;
    $targetTable = "";

    $tables = ['customer', 'employee']; 
    
    foreach ($tables as $table) {
        $queryUrl = SUPABASE_URL . "/rest/v1/$table?email_address=ilike." . urlencode($email) . "&select=*";
        
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
            $userFound = true;
            $targetTable = $table;
            break; 
        }
    }

    ob_clean(); 

    if ($userFound) {
        // SUCCESS: We now send the table name BACK to the browser
        writeToLog("SUCCESS: Account verified for [$email] in [$targetTable]");

        echo json_encode([
            "success" => true,
            "table" => $targetTable, // CRITICAL: This was missing!
            "message" => "Account verified. Proceed to Step 2."
        ]);
    } else {
        writeToLog("FAIL: Reset attempt for non-existent email [$email]");
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Email not found."]);
    }
} else {
    ob_clean();
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email is required."]);
}
exit;