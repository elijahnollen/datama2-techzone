<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method not allowed');
}

$token = (string)($_POST['token'] ?? '');
$p1 = (string)($_POST['new_password'] ?? '');
$p2 = (string)($_POST['new_password2'] ?? '');

if ($token === '') exit("<p style='color:red;'>Missing token.</p>");
if (strlen($p1) < 8) exit("<p style='color:red;'>Password must be at least 8 characters.</p>");
if ($p1 !== $p2) exit("<p style='color:red;'>Passwords do not match.</p>");

$pdo = db();

// Find latest unused, unexpired reset request
$stmt = $pdo->prepare("
  SELECT resetID, customerID, token_hash, expires_at, used_at
  FROM password_reset
  WHERE used_at IS NULL AND expires_at > NOW()
  ORDER BY resetID DESC
");
$stmt->execute();
$resets = $stmt->fetchAll();

$match = null;
foreach ($resets as $r) {
  if (password_verify($token, $r['token_hash'])) {
    $match = $r;
    break;
  }
}

if (!$match) {
  exit("<p style='color:red;'>Invalid or expired token.</p>");
}

$resetID = (int)$match['resetID'];
$customerID = (int)$match['customerID'];

try {
  $pdo->beginTransaction();

  // Update password
  $newHash = password_hash($p1, PASSWORD_DEFAULT);
  $upd = $pdo->prepare("UPDATE customer SET password_hash = ? WHERE customerID = ? LIMIT 1");
  $upd->execute([$newHash, $customerID]);

  // Mark token as used
  $mark = $pdo->prepare("UPDATE password_reset SET used_at = NOW() WHERE resetID = ? LIMIT 1");
  $mark->execute([$resetID]);

  $pdo->commit();

  echo "<h2>Password updated successfully.</h2>";
  echo "<p><a href='/client/login.php'>Go to login</a></p>";

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo "<p style='color:red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
