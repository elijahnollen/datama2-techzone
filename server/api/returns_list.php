<?php
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/app.php";

header("Content-Type: application/json");

$pdo = db();
$customerID = PROTOTYPE_CUSTOMER_ID;

$stmt = $pdo->prepare("
    SELECT returnID, return_date, refund_amount
    FROM return_transaction
    WHERE customerID = ?
    ORDER BY returnID DESC
");
$stmt->execute([$customerID]);
$rows = $stmt->fetchAll();

echo json_encode(["success"=>true, "data"=>$rows]);
