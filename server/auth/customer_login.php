<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method not allowed');
}

$email = trim($_POST['email'] ?? '');
$pass  = (string)($_POST['password'] ?? '');

$pdo = db();

$stmt = $pdo->prepare("
  SELECT customerID, password_hash, is_active
  FROM customer
  WHERE email_address = ?
  LIMIT 1
");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || (int)$user['is_active'] !== 1) {
  exit("<p style='color:red;'>Invalid credentials.</p>");
}

if (empty($user['password_hash']) || !password_verify($pass, $user['password_hash'])) {
  exit("<p style='color:red;'>Invalid credentials.</p>");
}

session_regenerate_id(true); // recommended after auth :contentReference[oaicite:5]{index=5}
$_SESSION['customer_id'] = (int)$user['customerID'];

echo "<h2>Login successful</h2>";
echo "<p><a href='/client/'>Go to Shop</a></p>";
