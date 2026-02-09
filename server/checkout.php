<?php
require __DIR__ . "/lib/session.php";
require __DIR__ . "/config/database.php";

function money($n) { return number_format((float)$n, 2); }

$items = $_SESSION['cart']['items'] ?? [];
if (empty($items)) {
    header("Location: cart.php");
    exit;
}

// Prototype IDs (replace later with real login/session)
$customerID = 1;      // TODO: from logged-in customer session
$employeeID = 1;      // TODO: "Online System" employee row

$pdo = db();

try {
    $pdo->beginTransaction();

    $grandTotal = 0.0;

    foreach ($items as $it) {
        $productID = (int)$it['productID'];
        $qtyWanted = (int)$it['qty'];

        $stmt = $pdo->prepare("SELECT productID, product_name, quantity, selling_price, is_active
                               FROM product
                               WHERE productID = ?
                               FOR UPDATE");
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

    $stmtSale = $pdo->prepare("INSERT INTO sale (total_amount, customerID, employeeID)
                               VALUES (?, ?, ?)");
    $stmtSale->execute([$grandTotal, $customerID, $employeeID]);
    $saleID = (int)$pdo->lastInsertId();

    $stmtItem = $pdo->prepare("INSERT INTO sale_item (quantity_sold, price_at_sale, serial_number, saleID, productID)
                               VALUES (?, ?, NULL, ?, ?)");

    $stmtDeduct = $pdo->prepare("UPDATE product SET quantity = quantity - ? WHERE productID = ?");

    foreach ($items as $it) {
        $productID = (int)$it['productID'];
        $qtyWanted = (int)$it['qty'];

        // Get price again
        $stmtP = $pdo->prepare("SELECT selling_price FROM product WHERE productID = ? FOR UPDATE");
        $stmtP->execute([$productID]);
        $p2 = $stmtP->fetch();
        $unitPrice = (float)$p2['selling_price'];

        $stmtItem->execute([$qtyWanted, $unitPrice, $saleID, $productID]);
        $stmtDeduct->execute([$qtyWanted, $productID]);
    }

    $pdo->commit();

    // Clear cart after successful commit
    $_SESSION['cart']['items'] = [];

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    // Render an error page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout Success</title>
  <link rel="stylesheet" href="../client/client_cart.css">
</head>
<body>
  <main style="max-width: 900px; margin: 40px auto; padding: 0 16px;">
    <h1>Checkout Successful</h1>
    <p style="opacity:.85;">Your order was recorded successfully.</p>

    <section style="margin-top:16px; padding:16px; border:1px solid #2a2a2a; border-radius:12px;">
      <div style="opacity:.75;">Sale ID</div>
      <div style="font-size:22px; font-weight:700;"><?= (int)$saleID ?></div>

      <div style="margin-top:12px; opacity:.75;">Total Paid (Simulated)</div>
      <div style="font-size:22px; font-weight:700;">₱<?= money($grandTotal) ?></div>

      <div style="margin-top:12px; opacity:.7;">Payment Ref (Simulated)</div>
      <div><?= "SIM-" . time() ?></div>
    </section>

    <div style="margin-top:18px;">
      <a href="cart.php">Return to Cart</a>
    </div>
  </main>
</body>
</html>
