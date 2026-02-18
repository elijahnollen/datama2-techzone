<?php
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../config/app.php';

function require_customer(): void {
  if (empty($_SESSION['customer_id'])) {
    header("Location: " . BASE_URL . "/client/login.php");
    exit;
  }
}

function require_admin(): void {
  if (empty($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "/admin/login.php");
    exit;
  }
}
