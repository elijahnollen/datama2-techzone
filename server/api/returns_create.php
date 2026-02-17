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

    // 1) Verify sale belongs to customer
    $stmtSale = $pdo->prepare("SELECT saleID FROM sale WHERE saleID = ? AND customerID = ? LIMIT 1");
    $stmtSale->execute([$saleID, $customerID]);
    if (!$stmtSale->fetch()) {
        throw new Exception("Invalid sale for this customer.");
    }

    // 2) Prepare statements
    $stmtItemFetch = $pdo->prepare("
        SELECT sale_itemID, quantity_sold
        FROM sale_item
        WHERE sale_itemID = ? AND saleID = ?
        LIMIT 1
    ");

    // Total previously returned for this sale_itemID (regardless of status)
    $stmtPrevReturned = $pdo->prepare("
        SELECT COALESCE(SUM(ri.return_quantity), 0) AS returned_qty
        FROM return_item ri
        JOIN return_transaction rt ON rt.returnID = ri.returnID
        WHERE ri.sale_itemID = ?
    ");

    // Create return header only AFTER we confirm at least one valid item
    $createdReturnHeader = false;
    $returnID = null;

    $stmtRT = $pdo->prepare("
        INSERT INTO return_transaction (refund_amount, customerID, employeeID)
        VALUES (NULL, ?, ?)
    ");

    $stmtInsertRI = $pdo->prepare("
        INSERT INTO return_item (return_quantity, reason, return_status, notes, sale_itemID, returnID)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $validCount = 0;

    foreach ($items as $it) {
        $sale_itemID = (int)($it['sale_itemID'] ?? 0);
        $qty = (int)($it['return_quantity'] ?? 0);
        $reason = (string)($it['reason'] ?? '');
        $notes = (string)($it['notes'] ?? '');

        if ($sale_itemID <= 0 || $qty <= 0) continue;
        if (!in_array($reason, ['Defective','Change of Mind'], true)) {
            throw new Exception("Invalid reason. Use Defective or Change of Mind.");
        }

        // Validate sale_item belongs to sale
        $stmtItemFetch->execute([$sale_itemID, $saleID]);
        $row = $stmtItemFetch->fetch();
        if (!$row) {
            throw new Exception("Invalid sale_itemID in items[].");
        }

        $boughtQty = (int)$row['quantity_sold'];

        // Subtract previous returns
        $stmtPrevReturned->execute([$sale_itemID]);
        $prev = (int)($stmtPrevReturned->fetch()['returned_qty'] ?? 0);

        $remaining = $boughtQty - $prev;
        if ($remaining <= 0) {
            throw new Exception("This item has already been fully returned.");
        }
        if ($qty > $remaining) {
            throw new Exception("Return qty exceeds remaining qty. Remaining: {$remaining}");
        }

        // Create return_transaction header once (lazy-create)
        if (!$createdReturnHeader) {
            $stmtRT->execute([$customerID, $employeeID]);
            $returnID = (int)$pdo->lastInsertId();
            $createdReturnHeader = true;
        }

        $defaultStatus = 'Store Credit';
        $stmtInsertRI->execute([
            $qty,
            $reason,
            $defaultStatus,
            ($notes === '' ? null : $notes),
            $sale_itemID,
            $returnID
        ]);

        $validCount++;
    }

    if ($validCount === 0) {
        throw new Exception("No valid return items submitted.");
    }

    $pdo->commit();

    echo json_encode(["success"=>true, "data"=>["returnID"=>$returnID, "items_added"=>$validCount]]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(["success"=>false, "error"=>$e->getMessage()]);
}
