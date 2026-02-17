<?php
// server/lib/session.php
// Start session safely (only once). Cart is stored in MongoDB (not in $_SESSION).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
