<?php
$host = 'localhost';
$dbname = 'techzone_new_inventory'; // Matches your phpMyAdmin sidebar
$username = 'root';
$password = ''; // Default XAMPP password is blank

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]));
}
?>