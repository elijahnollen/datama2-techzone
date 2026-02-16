<?php
// api/config.php

// Absolute path resolution to avoid directory depth issues
$envPath = realpath(__DIR__ . '/../.env');

if ($envPath && file_exists($envPath)) {
    // Using parse_ini_file to grab the variables
    $env = parse_ini_file($envPath);
    
    $url = $env['SUPABASE_URL'] ?? null;
    $key = $env['SUPABASE_KEY'] ?? null;

    define('SUPABASE_URL', $url);
    define('SUPABASE_KEY', $key);
} else {
    // Fallback for Vercel or GitHub Secrets
    define('SUPABASE_URL', getenv('SUPABASE_URL'));
    define('SUPABASE_KEY', getenv('SUPABASE_KEY'));
}

// CRITICAL: If these are null, the API will return 404 "Not Found"
if (!SUPABASE_URL || !SUPABASE_KEY) {
    error_log("[FORENSIC ALERT] Credentials missing. Path searched: " . (__DIR__ . '/../.env'));
    // logAction is defined below
    logAction("FAILURE: Could not load .env at " . __DIR__);
}

function logAction($message) {
    $logFile = __DIR__ . '/audit_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    // Check if the file is writable in the new Codespace
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>