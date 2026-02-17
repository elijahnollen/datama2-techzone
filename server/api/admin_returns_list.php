<?php require_once __DIR__ . '/../server/auth/guards.php'; require_admin(); ?>
<?php
require __DIR__ . "/../config/database.php";

header("Content-Type: application/json");

$pdo = db();
$stmt = $pdo->query("
  SELECT returnID, return_date, refund_amount, customerID, employeeID
  FROM return_transaction
  ORDER BY returnID DESC
  LIMIT 50
");
echo json_encode(["success"=>true, "data"=>$stmt->fetchAll()]);
