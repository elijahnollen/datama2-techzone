<?php
require '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET → View returns
if ($method === 'GET') {

    $stmt = $pdo->query("SELECT * FROM vw_return_details");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// POST → Approve / Deny
if ($method === 'POST') {

    $returnItemID = $_POST['return_itemID'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("
        UPDATE return_item
        SET return_status = ?
        WHERE return_itemID = ?
    ");

    $stmt->execute([$status, $returnItemID]);

    echo json_encode(["message"=>"Updated"]);
}
