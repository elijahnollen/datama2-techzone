<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    echo "<p style='color:red;'>Username and password are required.</p>";
    echo "<p><a href='" . BASE_URL . "/admin/login.php'>Back</a></p>";
    exit;
}

$pdo = db();

$stmt = $pdo->prepare("
  SELECT adminID, public_id, username, password_hash, is_active
  FROM admin_user
  WHERE username = ?
  LIMIT 1
");
$stmt->execute([$username]);
$row = $stmt->fetch();

if (!$row || (int)$row['is_active'] !== 1) {
    echo "<p style='color:red;'>Invalid login.</p>";
    echo "<p><a href='" . BASE_URL . "/admin/login.php'>Back</a></p>";
    exit;
}

if (!password_verify($password, $row['password_hash'])) {
    echo "<p style='color:red;'>Invalid login.</p>";
    echo "<p><a href='" . BASE_URL . "/admin/login.php'>Back</a></p>";
    exit;
}

session_regenerate_id(true);

$_SESSION['admin'] = [
    'adminID'  => (int)$row['adminID'],
    'public_id'=> (string)$row['public_id'],
    'username' => (string)$row['username'],
];

header('Location: ' . BASE_URL . '/admin/');
exit;
