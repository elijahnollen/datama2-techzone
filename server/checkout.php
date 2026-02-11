<?php
require __DIR__ . "/lib/session.php";
require __DIR__ . "/config/database.php";
require __DIR__ . "/config/app.php";

$items = $_SESSION['cart']['items'] ?? [];
if (empty($items)) {
    header("Location: cart.php");
    exit;
}

$customerID = PROTOTYPE_CUSTOMER_ID;
$employeeID = PROTOTYPE_EMPLOYEE_ID;

$pdo = db();

try {
    $pdo->beginTransaction();

    // 1) Lock products and validate stock
    $grandTotal = 0.0;

    foreach ($items as $it) {
        $productID = (int)$it['productID'];
        $qtyWanted = (int)$it['qty'];

        $stmt = $pdo->prepare("
            SELECT productID, product_name, quantity, selling_price, is_active
            FROM product
            WHERE productID = ?
            FOR UPDATE
        ");
        $stmt->execute([$productID]);
        $p = $stmt->fetch();

        if (!$p || (int)$p['is_active'] !== 1) {
            throw new Exception("Product ID $productID is not available.");
        }

        $stock = (int)$p['quantity'];
        if ($qtyWanted <= 0 || $stock < $qtyWanted) {
            throw new Exception("Insufficient stock for {$p['product_name']}. Available: $stock, Requested: $qtyWanted");
        }

        $unitPrice = (float)$p['selling_price'];
        $grandTotal += ($unitPrice * $qtyWanted);
    }

    // 2) Create sale
    $stmtSale = $pdo->prepare("
        INSERT INTO sale (total_amount, customerID, employeeID)
        VALUES (?, ?, ?)
    ");
    $stmtSale->execute([$grandTotal, $customerID, $employeeID]);
    $saleID = (int)$pdo->lastInsertId();

    // 3) Create sale items + deduct inventory
    $stmtItem = $pdo->prepare("
        INSERT INTO sale_item (quantity_sold, price_at_sale, serial_number, saleID, productID)
        VALUES (?, ?, NULL, ?, ?)
    ");
    $stmtDeduct = $pdo->prepare("
        UPDATE product SET quantity = quantity - ? WHERE productID = ?
    ");

    foreach ($items as $it) {
        $productID = (int)$it['productID'];
        $qtyWanted = (int)$it['qty'];

        // Locked read for price consistency
        $stmtP = $pdo->prepare("SELECT selling_price FROM product WHERE productID = ? FOR UPDATE");
        $stmtP->execute([$productID]);
        $p2 = $stmtP->fetch();
        $unitPrice = (float)$p2['selling_price'];

        $stmtItem->execute([$qtyWanted, $unitPrice, $saleID, $productID]);
        $stmtDeduct->execute([$qtyWanted, $productID]);
    }

    $pdo->commit();

    // 4) Post-checkout session updates
    $_SESSION['cart']['items'] = [];
    $_SESSION['last_sale_id'] = $saleID;

    // Redirect to receipt page
    header("Location: order_success.php");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    // Prototype failure render
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
      <title>Checkout Failed</title>
      <link rel="stylesheet" href="../client/client_cart.css">
    </head>
    <body>
      <main style="max-width:900px;margin:40px auto;padding:0 16px;">
        <h1>Checkout Failed</h1>
        <p style="opacity:.85;"><?= htmlspecialchars($e->getMessage()) ?></p>
        <a href="cart.php">Back to Cart</a>
      </main>
    </body>
    </html>
    <?php
    exit;
}