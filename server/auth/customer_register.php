<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../auth/captcha.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method not allowed');
}

// --- Sanitize inputs (leader style) ---
$firstName = trim(strip_tags($_POST['first_name'] ?? ''));
$lastName  = trim(strip_tags($_POST['last_name'] ?? ''));
$email     = trim($_POST['email'] ?? '');
$phone     = trim(strip_tags($_POST['phone'] ?? ''));
$street    = trim(strip_tags($_POST['street_address'] ?? ''));
$barangay  = trim(strip_tags($_POST['barangay'] ?? ''));
$city      = trim(strip_tags($_POST['city'] ?? ''));
$province  = trim(strip_tags($_POST['province'] ?? ''));
$zipCode   = trim(strip_tags($_POST['zip_code'] ?? ''));

$password  = (string)($_POST['password'] ?? '');
$password2 = (string)($_POST['password2'] ?? '');
$captchaAns= (string)($_POST['captcha'] ?? '');

// --- Layer 0: captcha ---
if (!captcha_verify($captchaAns)) {
  exit("<p style='color:red;'>• Invalid captcha answer.</p>");
}

// --- Layer 1: validation ---
$errors = [];

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors[] = "Invalid email format.";
}
if (!empty($zipCode) && !preg_match('/^[0-9]{4}$/', $zipCode)) {
  $errors[] = "Invalid Zip Code format. Please use 4 digits.";
}
if (empty($street)) {
  $errors[] = "Street address is required for e-commerce delivery.";
}
if (strlen($password) < 8) {
  $errors[] = "Password must be at least 8 characters.";
}
if ($password !== $password2) {
  $errors[] = "Passwords do not match.";
}

if (!empty($errors)) {
  foreach ($errors as $e) {
    echo "<p style='color:red;'>• " . htmlspecialchars($e) . "</p>";
  }
  exit;
}

// --- Layer 2: DB interaction with public ID + retry ---
$pdo = db();
$inserted = false;
$attempts = 0;
$maxRetries = 5;

while (!$inserted && $attempts < $maxRetries) {
  try {
    $publicId = 'CS-' . strtoupper(bin2hex(random_bytes(4)));

    // 1) Insert customer using your stored procedure (no password in proc)
    $stmt = $pdo->prepare("CALL record_new_customer(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
      $publicId,
      $firstName,
      $lastName,
      ($email === '' ? null : $email),
      ($phone === '' ? null : $phone),
      $street,
      $barangay,
      $city,
      $province,
      ($zipCode === '' ? null : $zipCode)
    ]);

    // 2) Set password hash + customer_type Online
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("UPDATE customer SET password_hash = ?, customer_type='Online' WHERE public_id = ? LIMIT 1");
    $upd->execute([$hash, $publicId]);

    // 3) Auto-login (store session)
    $row = $pdo->prepare("SELECT customerID FROM customer WHERE public_id = ? LIMIT 1");
    $row->execute([$publicId]);
    $cust = $row->fetch();

    session_regenerate_id(true); // recommended after login :contentReference[oaicite:3]{index=3}
    $_SESSION['customer_id'] = (int)$cust['customerID'];

    $inserted = true;

    echo "<h2>Registration Successful!</h2>";
    echo "Welcome to TechZone. Your Customer ID is: <strong>" . htmlspecialchars($publicId) . "</strong>";
    echo "<p><a href='/client/login.php'>Go to Login</a></p>";

  } catch (PDOException $e) {
    // Duplicate entry collision
    if (!empty($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
      $attempts++;
      continue;
    }
    echo "<h2>Registration Failed</h2>";
    echo "<p style='color:red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    break;
  }
}
