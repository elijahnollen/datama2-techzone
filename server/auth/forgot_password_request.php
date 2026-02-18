<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paths.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method not allowed');
}

$email = trim($_POST['email'] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  exit("<p style='color:red;'>Invalid email.</p>");
}

$pdo = db();

// IMPORTANT: do not reveal if account exists (prevents user enumeration)
$stmt = $pdo->prepare("SELECT customerID, is_active FROM customer WHERE email_address = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

echo "<h2>If that email exists, a reset link will be provided.</h2>";

if (!$user || (int)$user['is_active'] !== 1) {
  // Show same message even if not found
  echo "<p><a href='/client/login.php'>Back to login</a></p>";
  exit;
}

$customerID = (int)$user['customerID'];

// Generate raw token
$rawToken = bin2hex(random_bytes(32)); // long random token
$tokenHash = password_hash($rawToken, PASSWORD_DEFAULT);

$expires = (new DateTimeImmutable('+15 minutes'))->format('Y-m-d H:i:s');

// Store token hash
$ins = $pdo->prepare("
  INSERT INTO password_reset (customerID, token_hash, expires_at)
  VALUES (?, ?, ?)
");
$ins->execute([$customerID, $tokenHash, $expires]);

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'], 2), '/'); 
// Example: /auth/server/auth -> /auth

$resetLink = url("/client/reset_password.php?token=" . urlencode($rawToken));

echo "<p><strong>Prototype reset link:</strong></p>";
echo "<p><a href='{$resetLink}'>" . htmlspecialchars($resetLink) . "</a></p>";

echo "<p><a href='" . url("/client/login.php") . "'>Back to login</a></p>";

