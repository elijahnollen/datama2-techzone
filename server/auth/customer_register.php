<?php
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success'=>false,'error'=>'POST only']);
  exit;
}

// ---- Captcha check ----
$captcha = trim((string)($_POST['captcha_answer'] ?? ''));
if (!isset($_SESSION['captcha_answer']) || $captcha === '' || $captcha !== (string)$_SESSION['captcha_answer']) {
  http_response_code(400);
  echo json_encode(['success'=>false,'error'=>'Captcha incorrect']);
  exit;
}
unset($_SESSION['captcha_answer']); // one-time use

$firstName = trim(strip_tags($_POST['first_name'] ?? ''));
$lastName  = trim(strip_tags($_POST['last_name'] ?? ''));
$email     = trim($_POST['email_address'] ?? ($_POST['email'] ?? '')); // supports either name
$phone     = trim(strip_tags($_POST['contact_number'] ?? ($_POST['phone'] ?? '')));
$street    = trim(strip_tags($_POST['street_address'] ?? ''));
$barangay  = trim(strip_tags($_POST['barangay'] ?? ''));
$city      = trim(strip_tags($_POST['city_municipality'] ?? ($_POST['city'] ?? '')));
$province  = trim(strip_tags($_POST['province'] ?? ''));
$zipCode   = trim(strip_tags($_POST['zip_code'] ?? ''));
$password  = (string)($_POST['password'] ?? '');
$confirm   = (string)($_POST['confirm_password'] ?? '');

$errors = [];

// Email format
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
if ($password !== $confirm) {
  $errors[] = "Password confirmation does not match.";
}

if (!empty($errors)) {
  http_response_code(400);
  echo json_encode(['success'=>false,'errors'=>$errors]);
  exit;
}

$pdo = db();

$inserted = false;
$attempts = 0;
$maxRetries = 5;

while (!$inserted && $attempts < $maxRetries) {
  try {
    $publicId = 'CS-' . strtoupper(bin2hex(random_bytes(4)));

    $stmt = $pdo->prepare("CALL record_new_customer(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
      $publicId,
      $firstName,
      $lastName,
      $email,
      $phone,
      $street,
      $barangay,
      $city,
      $province,
      $zipCode
    ]);

    // 2) Set password_hash + customer_type after insert
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $upd = $pdo->prepare("
      UPDATE customer
      SET password_hash = ?, customer_type = 'Online'
      WHERE public_id = ?
      LIMIT 1
    ");
    $upd->execute([$hash, $publicId]);

    $inserted = true;

    echo json_encode([
      'success' => true,
      'message' => 'Registration Successful!',
      'customer_public_id' => $publicId
    ]);
    exit;

  } catch (PDOException $e) {
    // Duplicate entry collision
    if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
      $attempts++;
      continue;
    }

    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    exit;
  }
}

http_response_code(500);
echo json_encode(['success'=>false,'error'=>'Could not generate unique customer ID. Try again.']);
