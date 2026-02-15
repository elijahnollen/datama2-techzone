<?php
// api/config.php

// FORENSIC PATH FIX: Look in the root directory for .env
$envFile = __DIR__ . '/../.env';

if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    define('SUPABASE_URL', $env['SUPABASE_URL'] ?? null);
    define('SUPABASE_KEY', $env['SUPABASE_KEY'] ?? null);
} else {
    // If .env is missing, check Vercel environment variables
    define('SUPABASE_URL', getenv('SUPABASE_URL'));
    define('SUPABASE_KEY', getenv('SUPABASE_KEY'));
}

// Verification
if (!SUPABASE_URL || !SUPABASE_KEY) {
    error_log("[FORENSIC ALERT] Database credentials could not be loaded.");
}

function logAction($message) {
    // Keep logs inside the api folder or use a relative path
    $logFile = __DIR__ . '/audit_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>