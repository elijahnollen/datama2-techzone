<?php
require '../config/db.php';

$stmt=$pdo->query("SELECT * FROM vw_inventory_transaction");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
