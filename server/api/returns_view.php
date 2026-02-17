<?php
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/app.php";

header("Content-Type: application/json");

$pdo = db();
$customerID = PROTOTYPE_CUSTOMER_ID;

$returnID = (int)($_GET['returnID'] ?? 0);
if ($returnID <= 0) {
    http_response_code(400);
    echo json_encode(["success"=>false, "error"=>"returnID is required"]);
    exit;
}

$stmtHdr = $pdo->prepare("
    SELECT
        rt.returnID, rt.public_id, rt.date_created, rt.refund_amount,
        rt.return_progress, rt.tracking_no, rt.return_method, rt.saleID
    FROM return_transaction rt
    JOIN sale s ON s.saleID = rt.saleID
    WHERE rt.returnID = ? AND s.customerID = ?
    LIMIT 1
");
$stmtHdr->execute([$returnID, $customerID]);
$hdr = $stmtHdr->fetch();

if (!$hdr) {
    http_response_code(404);
    echo json_encode(["success"=>false, "error"=>"Return not found"]);
    exit;
}

$stmtItems = $pdo->prepare("
    SELECT
        ri.return_itemID,
        ri.return_quantity,
        ri.reason,
        ri.return_status,
        ri.notes,
        ri.sale_itemID,
        si.productID,
        p.product_name
    FROM return_item ri
    JOIN sale_item si ON si.sale_itemID = ri.sale_itemID
    JOIN product p ON p.productID = si.productID
    WHERE ri.returnID = ?
    ORDER BY ri.return_itemID ASC
");
$stmtItems->execute([$returnID]);

echo json_encode([
    "success"=>true,
    "data"=>[
        "header"=>$hdr,
        "items"=>$stmtItems->fetchAll()
    ]
]);
