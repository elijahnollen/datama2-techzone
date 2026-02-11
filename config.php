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

