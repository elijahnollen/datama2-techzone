<?php
require_once '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

// --- GET: List all refund requests (Admin view) ---
if ($method === 'GET') {
    $url = SUPABASE_URL . "/rest/v1/refund_request?select=*"; // Removed filter to see everything
    
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
    $data = json_decode(file_get_contents('php://input'), true);
    
    // We force status to 'pending' because the customer can't 'accept' their own refund
    $data['status'] = 'pending'; 

    $ch = curl_init(SUPABASE_URL . "/rest/v1/refund_request");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
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
    echo $response;
}