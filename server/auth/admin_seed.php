<?php
require_once __DIR__ . '/../config/database.php';

$pdo = db();
$username = 'admin';
$publicId = 'AD-' . strtoupper(bin2hex(random_bytes(4)));
$hash = password_hash('Admin@12345', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO admin_user (public_id, username, password_hash) VALUES (?, ?, ?)");
$stmt->execute([$publicId, $username, $hash]);

echo "Seeded admin user.\n";
echo "Username: admin\n";
echo "Password: Admin@12345\n";
