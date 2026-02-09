<?php
// PDO connection. Replace credentials with our local MySQL info.

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host = "localhost";
    $db   = "techzone_new_inventory";  // <-- match our DB name
    $user = "root";
    $pass = "";                        // <-- password if any
    $charset = "utf8mb4";

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
}
