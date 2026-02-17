<?php
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/app.php";

header("Content-Type: application/json");

$pdo = db();
$customerID = PROTOTYPE_CUSTOMER_ID;

$stmt = $pdo->prepare("
    SELECT rt.returnID,
           rt.date_created AS return_date,
           rt.refund_amount,
           rt.saleID,
           rt.return_progress,
           rt.return_method,
           rt.tracking_no
    FROM return_transaction rt
    JOIN sale s ON s.saleID = rt.saleID
    WHERE s.customerID = ?
    ORDER BY rt.returnID DESC
");
$stmt->execute([$customerID]);
$rows = $stmt->fetchAll();

echo json_encode(["success"=>true, "data"=>$rows]);
