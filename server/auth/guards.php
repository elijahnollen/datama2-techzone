<?php
// server/auth/guards.php
require_once __DIR__ . '/../lib/session.php';

function require_customer_login(): void {
    if (!isset($_SESSION['customer'])) {
        header('Location: ' . BASE_URL . '/client/login.php');
        exit;
    }
}

function require_admin_login(): void {
    if (!isset($_SESSION['admin'])) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}
