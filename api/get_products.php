<?php
// api/products.php
require_once '../config.php';

// --- BROWSER PERMISSIONS (CORS) ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

header("Content-Type: application/json");

// --- GET PARAMETERS ---
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// --- BASE URL ---
// Default ordering by ID ensures a consistent list for forensic audits
$url = SUPABASE_URL . "/rest/v1/product?select=*&order=id";

// --- FILTER LOGIC ---

// 1. Search Filter (Case-insensitive fuzzy match)
if (!empty($search)) {
    $url .= "&product_name=ilike.*" . urlencode($search) . "*";
}

// 2. FIXED: Category Filter
// We skip the filter if it is empty OR if the user selected "All"
if (!empty($category) && strcasecmp($category, 'All') !== 0) {
    $url .= "&category=ilike." . urlencode($category);
}

// --- API REQUEST ---
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: " . SUPABASE_KEY,
    "Authorization: Bearer " . SUPABASE_KEY
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- FORENSIC LOGGING ---
// Records intent and activity for audit logs
error_log("[CATALOG ACCESS]: Category: '$category', Search: '$search' at " . date('Y-m-d H:i:s'));

// Return the response from Supabase
if ($httpCode === 200) {
    echo $response;
} else {
    http_response_code($httpCode);
    echo json_encode([
        "error" => "Failed to fetch data",
        "details" => json_decode($response)
    ]);
}