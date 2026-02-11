<?php
// config.php
$env = parse_ini_file(__DIR__ . '/.env'); // Using __DIR__ helps PHP find the file

define('SUPABASE_URL', $env['SUPABASE_URL'] ?? 'https://puqivchxbvszeyigjvmj.supabase.co');
define('SUPABASE_KEY', $env['SUPABASE_KEY'] ?? 'sb_publishable_HqwyeJjwnsbbeiSYWCafsQ_vLOBpR2u');

if (!SUPABASE_URL) {
    error_log("[FORENSIC ALERT] Supabase URL is still missing!");
}
?>