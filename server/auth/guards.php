<?php
require_once __DIR__ . '/../lib/session.php';

function require_login(): void {
  if (!isset($_SESSION['user'])) {
    http_response_code(401);
    die("Unauthorized");
  }
}

function require_admin(): void {
  if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die("Forbidden");
  }
}

function require_customer(): void {
  if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'customer') {
    http_response_code(403);
    die("Forbidden");
  }
}
