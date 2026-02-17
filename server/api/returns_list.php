<?php
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/app.php";

header("Content-Type: application/json");

$pdo = db();
$customerID = PROTOTYPE_CUSTOMER_ID;

$stmt = $pdo->prepare("
    SELECT
        rt.returnID,
        rt.public_id,
        rt.date_created,
        rt.return_progress,
        rt.refund_amount,
        rt.saleID
    FROM return_transaction rt
    JOIN sale s ON s.saleID = rt.saleID
    WHERE s.customerID = ?
    ORDER BY rt.returnID DESC
");
$stmt->execute([$customerID]);

echo json_encode(["success"=>true, "data"=>$stmt->fetchAll()]);
