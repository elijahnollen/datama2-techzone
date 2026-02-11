<?php
// config.php

// This parses your .env file into an array
$env = parse_ini_file('.env');

// Define constants for use in your login scripts
define('SUPABASE_URL', $env['SUPABASE_URL']);
define('SUPABASE_KEY', $env['SUPABASE_KEY']);

// Verification (Internal only, won't show on the website)
if (!SUPABASE_URL || !SUPABASE_KEY) {
    error_log("[FORENSIC ALERT] .env file is missing or keys are not set.");
}

/**
 * Forensic Logging Function
 * Writes user activity to audit_log.txt in the root folder
 */
function logAction($message) {
    $logFile = __DIR__ . '/audit_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    // PHP_EOL adds a new line automatically
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    
    // FILE_APPEND ensures we don't delete old logs
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>