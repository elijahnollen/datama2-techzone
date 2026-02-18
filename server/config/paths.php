<?php
// server/config/paths.php
// Detect base URL path (e.g. /auth) even if project folder changes.

function base_path(): string {
    // /auth/server/auth/forgot_password_request.php
    // dirname(..., 3) => /auth
    $bp = rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/');
    return $bp === '' ? '' : $bp;
}

function url(string $path): string {
    return base_path() . $path;
}
