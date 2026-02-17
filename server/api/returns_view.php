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

// Header must belong to this customer
$stmtHdr = $pdo->prepare("
    SELECT returnID, return_date, refund_amount
    FROM return_transaction
    WHERE returnID = ? AND customerID = ?
    LIMIT 1
");
$stmtHdr->execute([$returnID, $customerID]);
$hdr = $stmtHdr->fetch();

if (!$hdr) {
    http_response_code(404);
    echo json_encode(["success"=>false, "error"=>"Return not found"]);
    exit;
}

// Items
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
