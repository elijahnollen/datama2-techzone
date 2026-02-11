<?php
// api/products.php
require_once '../config.php';

// --- BROWSER PERMISSIONS (CORS) ---
// Essential for letting the Vue.js frontend talk to this PHP backend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle pre-flight requests from the browser
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

header("Content-Type: application/json");

// --- LOGIC ---
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// FIXED: Removed '&order=product_id' to avoid the "column does not exist" error.
// Most Supabase tables use 'id' as the primary key.
$url = SUPABASE_URL . "/rest/v1/product?select=*&order=id";

// Add search filter if keyword exists (ilike = case-insensitive)
if (!empty($search)) {
    $url .= "&product_name=ilike.*" . urlencode($search) . "*";
}

// Add category filter if selected
if (!empty($category)) {
    $url .= "&category=eq." . urlencode($category);
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: " . SUPABASE_KEY,
    "Authorization: Bearer " . SUPABASE_KEY
]);

$response = curl_exec($ch);
curl_close($ch);

// --- FORENSIC LOGGING ---
// Records search activity for your final Computer Forensics report
error_log("[CATALOG ACCESS]: Category: '$category', Search: '$search' at " . date('Y-m-d H:i:s'));

echo $response;