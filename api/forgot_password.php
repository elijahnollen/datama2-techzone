<?php
ob_start(); 

require_once 'config.php'; 

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

function writeToLog($message) {
    // Forensic Path: Ensure log is written in the api folder
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

    // Search across customer and employee tables
    $tables = ['customer', 'employee']; 
    
    foreach ($tables as $table) {
        // FORENSIC FIX: Changed 'eq' to 'ilike' for case-insensitivity
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
        // DYNAMIC URL: Automatically detects if you're on Codespaces or Vercel
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $resetLink = $protocol . $host . "/reset-password.html?email=" . urlencode($email) . "&table=" . $targetTable;

        writeToLog("FORGOT PASSWORD: Found [$email] in [$targetTable]. Reset Link: $resetLink");

        echo json_encode([
            "success" => true,
            "message" => "Account found. Your reset link is ready in the audit log.",
            "table" => $targetTable // Frontend uses this to redirect automatically if needed
        ]);
    } else {
        writeToLog("FORGOT PASSWORD FAIL: [$email] not found.");
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "This email is not registered."]);
    }
} else {
    ob_clean();
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email address is required."]);
}
exit;