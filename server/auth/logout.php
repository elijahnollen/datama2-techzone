<?php
require_once __DIR__ . '/../lib/session.php';

$_SESSION = [];
session_destroy();

echo "<p>Logged out.</p>";
echo "<p><a href='/client/login.php'>Customer Login</a> | <a href='/admin/login.php'>Admin Login</a></p>";
