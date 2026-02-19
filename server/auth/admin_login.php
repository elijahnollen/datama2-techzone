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
  SELECT employeeID, public_id, password_hash, employee_status, employee_role, first_name, last_name
  FROM employee
  WHERE email_address = ?
  LIMIT 1
");
$stmt->execute([$email]);
$emp = $stmt->fetch();

if (!$emp) {
  http_response_code(401);
  echo json_encode(['success'=>false,'error'=>'Invalid credentials']);
  exit;
}

if (!empty($emp['employee_status']) && strtolower((string)$emp['employee_status']) !== 'active') {
  http_response_code(403);
  echo json_encode(['success'=>false,'error'=>'Employee not active']);
  exit;
}

if (!password_verify($pass, (string)$emp['password_hash'])) {
  http_response_code(401);
  echo json_encode(['success'=>false,'error'=>'Invalid credentials']);
  exit;
}

session_regenerate_id(true);

$_SESSION['user'] = [
  'role' => 'admin',
  'employeeID' => (int)$emp['employeeID'],
  'public_id' => (string)$emp['public_id'],
  'employee_role' => (string)($emp['employee_role'] ?? ''),
  'name' => trim($emp['first_name'] . ' ' . $emp['last_name'])
];

echo json_encode(['success'=>true,'message'=>'Admin login ok','user'=>$_SESSION['user']]);
