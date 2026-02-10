<?php
// api/refunds.php
require_once '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

// --- GET: List all 'pending' refund requests ---
if ($method === 'GET') {
    // FIXED: Changed 'refund_requests' to 'refund_request'
    $url = SUPABASE_URL . "/rest/v1/refund_request?select=*&status=eq.pending";
    
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

// --- PATCH: Accept or Reject a refund request ---
if ($method === 'PATCH') {
    $data = json_decode(file_get_contents('php://input'), true);
    $refund_id = $data['id'] ?? null;
    $new_status = $data['status'] ?? null;

    if (!$refund_id || !$new_status) {
        echo json_encode(["error" => "Missing Refund ID or Status"]);
        exit;
    }

    // FIXED: Changed 'refund_requests' to 'refund_request'
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
    curl_close($ch);

    error_log("[REFUND UPDATE]: ID #$refund_id set to $new_status at " . date('Y-m-d H:i:s'));
    
    echo $response;
}