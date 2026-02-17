<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method not allowed');
}

$username = trim($_POST['username'] ?? '');
$pass     = (string)($_POST['password'] ?? '');

$pdo = db();

$stmt = $pdo->prepare("
  SELECT adminID, password_hash, is_active
  FROM admin_user
  WHERE username = ?
  LIMIT 1
");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if (!$admin || (int)$admin['is_active'] !== 1) {
  exit("<p style='color:red;'>Invalid credentials.</p>");
}

if (!password_verify($pass, $admin['password_hash'])) {
  exit("<p style='color:red;'>Invalid credentials.</p>");
}

session_regenerate_id(true); // recommended :contentReference[oaicite:6]{index=6}
$_SESSION['admin_id'] = (int)$admin['adminID'];

echo "<h2>Admin login successful</h2>";
echo "<p><a href='/admin/'>Go to Admin Dashboard</a></p>";
