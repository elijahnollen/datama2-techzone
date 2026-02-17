<?php
require_once __DIR__ . '/../lib/session.php';

function require_customer(): void {
  if (empty($_SESSION['customer_id'])) {
    header("Location: /client/login.php");
    exit;
  }
}

function require_admin(): void {
  if (empty($_SESSION['admin_id'])) {
    header("Location: /admin/login.php");
    exit;
  }
}
