<?php
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success'=>false,'error'=>'POST only']);
  exit;
}

// captcha
$captcha = trim((string)($_POST['captcha_answer'] ?? ''));
if (!isset($_SESSION['captcha_answer']) || $captcha === '' || $captcha !== (string)$_SESSION['captcha_answer']) {
  http_response_code(400);
  echo json_encode(['success'=>false,'error'=>'Captcha incorrect']);
  exit;
}
unset($_SESSION['captcha_answer']);

$email = trim((string)($_POST['email_address'] ?? ($_POST['email'] ?? '')));
$pass  = (string)($_POST['password'] ?? '');

if ($email === '' || $pass === '') {
  http_response_code(400);
  echo json_encode(['success'=>false,'error'=>'Email and password are required']);
  exit;
}

$pdo = db();

$stmt = $pdo->prepare("
  SELECT customerID, public_id, password_hash, is_active, first_name, last_name
  FROM customer
  WHERE email_address = ?
  LIMIT 1
");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
  http_response_code(401);
  echo json_encode(['success'=>false,'error'=>'Invalid credentials']);
  exit;
}

if ((int)$user['is_active'] !== 1) {
  http_response_code(403);
  echo json_encode(['success'=>false,'error'=>'Account is inactive']);
  exit;
}

if (!password_verify($pass, (string)$user['password_hash'])) {
  http_response_code(401);
  echo json_encode(['success'=>false,'error'=>'Invalid credentials']);
  exit;
}

session_regenerate_id(true);

$_SESSION['user'] = [
  'role' => 'customer',
  'customerID' => (int)$user['customerID'],
  'public_id' => (string)$user['public_id'],
  'name' => trim($user['first_name'] . ' ' . $user['last_name'])
];

echo json_encode(['success'=>true,'message'=>'Login ok','user'=>$_SESSION['user']]);
