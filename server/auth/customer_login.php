<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/client/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo "<p style='color:red;'>Email and password are required.</p>";
    echo "<p><a href='" . BASE_URL . "/client/login.php'>Back</a></p>";
    exit;
}

$pdo = db();

// Find active customer by email
$stmt = $pdo->prepare("
  SELECT customerID, public_id, first_name, last_name, password_hash, is_active
  FROM customer
  WHERE email_address = ?
  LIMIT 1
");
$stmt->execute([$email]);
$row = $stmt->fetch();

if (!$row || (int)$row['is_active'] !== 1) {
    echo "<p style='color:red;'>Invalid login.</p>";
    echo "<p><a href='" . BASE_URL . "/client/login.php'>Back</a></p>";
    exit;
}

if (empty($row['password_hash']) || !password_verify($password, $row['password_hash'])) {
    echo "<p style='color:red;'>Invalid login.</p>";
    echo "<p><a href='" . BASE_URL . "/client/login.php'>Back</a></p>";
    exit;
}

session_regenerate_id(true);

$_SESSION['customer'] = [
    'customerID' => (int)$row['customerID'],
    'public_id'  => (string)$row['public_id'],
    'name'       => trim($row['first_name'] . ' ' . $row['last_name']),
];

header('Location: ' . BASE_URL . '/client/');
exit;
