<?php
// api/refunds.php
require_once '../config.php';

// --- BROWSER PERMISSIONS (CORS) ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle Preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$log_file = 'audit_log.txt'; // Forensic flat-file log

/**
 * Helper function for forensic text logging
 * Captures Timestamp, IP, and the specific action.
 */
function writeToLog($file, $message) {
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $entry = "[$timestamp] IP: $ip | $message" . PHP_EOL;
    file_put_contents($file, $entry, FILE_APPEND);
}

// --- GET: List all refund requests (Admin view) ---
if ($method === 'GET') {
    // Fetches all records from the table
    $url = SUPABASE_URL . "/rest/v1/refund_request?select=*";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    echo $response;
}

// --- POST: Customer submits a new refund request ---
if ($method === 'POST') {
    // Read JSON input from Postman Body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Force status to 'Pending' for new requests
    $data['status'] = 'Pending'; 

    $ch = curl_init(SUPABASE_URL . "/rest/v1/refund_request");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Header fix: return=representation ensures Postman shows the saved data
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json",
        "Prefer: return=representation" 
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // FORENSIC LOG: Record the submission in the .txt file
    if ($httpCode >= 200 && $httpCode < 300) {
        $customer = $data['customer_name'] ?? 'Unknown';
        $reason = $data['reason'] ?? 'No reason provided';
        writeToLog($log_file, "NEW REQUEST: Customer: $customer | Reason: $reason");
    }

    echo $response;
}

// --- PATCH: Admin accepts or rejects a refund request ---
if ($method === 'PATCH') {
    $data = json_decode(file_get_contents('php://input'), true);
    $refund_id = $data['id'] ?? null;
    $new_status = $data['status'] ?? null;

    if (!$refund_id || !$new_status) {
        echo json_encode(["error" => "Missing Refund ID or Status"]);
        exit;
    }

    // Filter by ID to update the specific record
    $url = SUPABASE_URL . "/rest/v1/refund_request?id=eq." . $refund_id;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['status' => $new_status]));
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

    // FORENSIC LOG: Record the admin decision in the .txt file
    if ($httpCode == 200) {
        writeToLog($log_file, "ADMIN ACTION: ID: $refund_id | Result: $new_status");
    }
    
    echo $response;
}
