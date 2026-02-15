<?php
require __DIR__ . "/../lib/session.php";
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/app.php";

header("Content-Type: application/json");

$pdo = db();
$customerID = PROTOTYPE_CUSTOMER_ID;
$employeeID = PROTOTYPE_EMPLOYEE_ID;

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(["success"=>false, "error"=>"Invalid JSON"]);
    exit;
}

$saleID = (int)($input['saleID'] ?? 0);
$items  = $input['items'] ?? [];

if ($saleID <= 0 || !is_array($items) || empty($items)) {
    http_response_code(400);
    echo json_encode(["success"=>false, "error"=>"saleID and items[] required"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmtSale = $pdo->prepare("SELECT saleID FROM sale WHERE saleID = ? AND customerID = ? LIMIT 1");
    $stmtSale->execute([$saleID, $customerID]);
    if (!$stmtSale->fetch()) throw new Exception("Invalid sale for this customer.");

    $stmtRT = $pdo->prepare("INSERT INTO return_transaction (refund_amount, customerID, employeeID) VALUES (NULL, ?, ?)");
    $stmtRT->execute([$customerID, $employeeID]);
    $returnID = (int)$pdo->lastInsertId();

    $stmtItemFetch = $pdo->prepare("
        SELECT sale_itemID, quantity_sold
        FROM sale_item
        WHERE sale_itemID = ? AND saleID = ?
        LIMIT 1
    ");

    $stmtInsertRI = $pdo->prepare("
        INSERT INTO return_item (return_quantity, reason, return_status, notes, sale_itemID, returnID)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($items as $it) {
        $sale_itemID = (int)($it['sale_itemID'] ?? 0);
        $qty = (int)($it['return_quantity'] ?? 0);
        $reason = (string)($it['reason'] ?? '');
        $notes = (string)($it['notes'] ?? '');

        if ($sale_itemID <= 0 || $qty <= 0) continue;
        if (!in_array($reason, ['Defective','Change of Mind'], true)) throw new Exception("Invalid reason.");

        $stmtItemFetch->execute([$sale_itemID, $saleID]);
        $row = $stmtItemFetch->fetch();
        if (!$row) throw new Exception("Invalid sale_itemID in items[]");

        if ($qty > (int)$row['quantity_sold']) throw new Exception("Return qty exceeds bought qty.");

        $defaultStatus = 'Store Credit';
        $stmtInsertRI->execute([$qty, $reason, $defaultStatus, ($notes ?: null), $sale_itemID, $returnID]);
    }

    $pdo->commit();

    echo json_encode(["success"=>true, "data"=>["returnID"=>$returnID]]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(["success"=>false, "error"=>$e->getMessage()]);
}
