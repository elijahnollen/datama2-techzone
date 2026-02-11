<?php
require '../config/db.php';

$keyword = $_GET['search'] ?? '';
$min = $_GET['min'] ?? 0;
$max = $_GET['max'] ?? 999999;

$sql = "
SELECT productID, product_name, selling_price, quantity
FROM product
WHERE is_active = 1
AND product_name LIKE ?
AND selling_price BETWEEN ? AND ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute(["%$keyword%", $min, $max]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));