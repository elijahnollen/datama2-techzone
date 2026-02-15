<?php
// Prevent accidental whitespace from breaking JSON
ob_start(); 

require_once 'config.php'; 

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

/**
 * Forensic Logging Function
 */
function writeToLog($message) {
    $log_file = 'audit_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $entry = "[$timestamp] IP: $ip | $message" . PHP_EOL;
    file_put_contents($log_file, $entry, FILE_APPEND);
}

// Capture JSON input from fetch
$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['email'])) {
    $email = trim($data['email']);
    $userFound = false;
    $targetTable = "";

    // Search across customer and employee tables
    $tables = ['customer', 'employee']; 
    
    foreach ($tables as $table) {
        $queryUrl = SUPABASE_URL . "/rest/v1/$table?email_address=eq." . urlencode($email) . "&select=*";
        
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

    ob_clean(); // Wipe buffer before JSON output

    if ($userFound) {
        /**
         * LOG-BASED RESET INTEGRATION
         * We generate the link and save it to the log instead of sending an email.
         */
        // Replace with your actual Frontend URL for the reset page
        $frontendUrl = "https://your-codespace-url-5173.app.github.dev/reset-password.html";
        $resetLink = $frontendUrl . "?email=" . urlencode($email) . "&table=" . $targetTable;

        writeToLog("FORGOT PASSWORD: Found [$email] in [$targetTable]. Reset Link: $resetLink");

        echo json_encode([
            "success" => true,
            "message" => "Account found. For development, your reset link has been sent to audit_log.txt."
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