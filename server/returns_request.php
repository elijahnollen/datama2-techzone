<?php
require __DIR__ . "/lib/session.php";
require __DIR__ . "/config/database.php";
require __DIR__ . "/config/app.php";

$pdo = db();
$customerID = PROTOTYPE_CUSTOMER_ID;
$employeeID = PROTOTYPE_EMPLOYEE_ID;

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Step selection
$saleID = (int)($_GET['saleID'] ?? 0);

// Handle submit (create return)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saleID = (int)($_POST['saleID'] ?? 0);
    $selected = $_POST['selected'] ?? []; // sale_itemID => "on"
    $qtyMap   = $_POST['return_quantity'] ?? []; // sale_itemID => qty
    $reasonMap= $_POST['reason'] ?? []; // sale_itemID => reason
    $notesMap = $_POST['notes'] ?? []; // sale_itemID => notes

    if ($saleID <= 0 || empty($selected)) {
        header("Location: returns_request.php?saleID=" . $saleID);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Ensure sale belongs to customer (prototype safety)
        $stmtSale = $pdo->prepare("SELECT saleID FROM sale WHERE saleID = ? AND customerID = ? LIMIT 1");
        $stmtSale->execute([$saleID, $customerID]);
        $sale = $stmtSale->fetch();
        if (!$sale) throw new Exception("Invalid sale for this customer.");

        // Create return_transaction
        $stmtRT = $pdo->prepare("
            INSERT INTO return_transaction (refund_amount, customerID, employeeID)
            VALUES (NULL, ?, ?)
        ");
        $stmtRT->execute([$customerID, $employeeID]);
        $returnID = (int)$pdo->lastInsertId();

        // Prepare statements
        $stmtItemFetch = $pdo->prepare("
            SELECT si.sale_itemID, si.quantity_sold, si.price_at_sale, si.productID, p.product_name
            FROM sale_item si
            JOIN product p ON p.productID = si.productID
            WHERE si.sale_itemID = ? AND si.saleID = ?
            LIMIT 1
        ");

        $stmtInsertRI = $pdo->prepare("
            INSERT INTO return_item (return_quantity, reason, return_status, notes, sale_itemID, returnID)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($selected as $sale_itemID_str => $_on) {
            $sale_itemID = (int)$sale_itemID_str;
            $reqQty = (int)($qtyMap[$sale_itemID_str] ?? 0);
            $reason = (string)($reasonMap[$sale_itemID_str] ?? '');
            $notes  = (string)($notesMap[$sale_itemID_str] ?? null);

            if ($reqQty <= 0) continue;
            if (!in_array($reason, ['Defective','Change of Mind'], true)) {
                throw new Exception("Invalid reason.");
            }

            // Validate sale_item belongs to sale and qty not exceeded
            $stmtItemFetch->execute([$sale_itemID, $saleID]);
            $si = $stmtItemFetch->fetch();
            if (!$si) throw new Exception("Invalid sale item selected.");

            $maxQty = (int)$si['quantity_sold'];
            if ($reqQty > $maxQty) throw new Exception("Return qty exceeds bought qty for item {$si['product_name']}.");

            // Customer should NOT decide final status; set default for prototype
            $defaultStatus = 'Store Credit';

            $stmtInsertRI->execute([
                $reqQty,
                $reason,
                $defaultStatus,
                $notes ?: null,
                $sale_itemID,
                $returnID
            ]);
        }

        $pdo->commit();

        $_SESSION['last_return_id'] = $returnID;
        header("Location: returns_view.php?returnID=" . $returnID);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $err = $e->getMessage();
    }
}

// Load sales list for this customer (for choosing)
$stmtSales = $pdo->prepare("
    SELECT saleID, sale_date, total_amount
    FROM sale
    WHERE customerID = ?
    ORDER BY saleID DESC
    LIMIT 20
");
$stmtSales->execute([$customerID]);
$sales = $stmtSales->fetchAll();

// If a sale is selected, load items for that sale
$items = [];
if ($saleID > 0) {
    $stmtItems = $pdo->prepare("
        SELECT si.sale_itemID, si.quantity_sold, si.price_at_sale, p.product_name, p.productID
        FROM sale_item si
        JOIN product p ON p.productID = si.productID
        WHERE si.saleID = ?
        ORDER BY si.sale_itemID ASC
    ");
    $stmtItems->execute([$saleID]);
    $items = $stmtItems->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Return Items</title>
  <link rel="stylesheet" href="../client/client_cart.css">
</head>
<body>
<main style="max-width: 1000px; margin: 40px auto; padding: 0 16px;">
  <h1>Return Items (Customer)</h1>
  <p style="opacity:.85;">
    Prototype customer ID: <?= (int)$customerID ?>. Choose an order, then select which items to return.
  </p>

  <?php if (!empty($err ?? null)): ?>
    <div style="padding:12px; border:1px solid #aa3333; border-radius:12px; margin:12px 0;">
      <strong>Error:</strong> <?= h($err) ?>
    </div>
  <?php endif; ?>

  <section style="margin: 18px 0; padding: 16px; border: 1px solid #2a2a2a; border-radius: 12px;">
    <h3 style="margin:0 0 10px;">Step 1: Choose an Order (Sale)</h3>

    <form method="GET" action="returns_request.php" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
      <label>Sale ID:</label>
      <select name="saleID" style="padding:10px; border-radius:10px;" required>
        <option value="">-- Select --</option>
        <?php foreach ($sales as $s): ?>
          <option value="<?= (int)$s['saleID'] ?>" <?= $saleID === (int)$s['saleID'] ? 'selected' : '' ?>>
            #<?= (int)$s['saleID'] ?> — <?= h($s['sale_date']) ?> — ₱<?= number_format((float)$s['total_amount'],2) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" style="padding:10px 14px; border-radius:10px; cursor:pointer;">Load Items</button>
      <a href="returns_list.php" style="margin-left:auto;">View My Returns</a>
    </form>
  </section>

  <?php if ($saleID > 0): ?>
    <section style="margin: 18px 0; padding: 16px; border: 1px solid #2a2a2a; border-radius: 12px;">
      <h3 style="margin:0 0 10px;">Step 2: Select Items to Return (Sale #<?= (int)$saleID ?>)</h3>

      <?php if (empty($items)): ?>
        <p>No items found for this sale.</p>
      <?php else: ?>
        <form method="POST" action="returns_request.php">
          <input type="hidden" name="saleID" value="<?= (int)$saleID ?>"/>

          <table style="width:100%; border-collapse: collapse; overflow:hidden; border-radius:12px;">
            <thead>
              <tr style="text-align:left; border-bottom: 1px solid #2a2a2a;">
                <th style="padding:12px;">Select</th>
                <th style="padding:12px;">Product</th>
                <th style="padding:12px;">Bought Qty</th>
                <th style="padding:12px;">Return Qty</th>
                <th style="padding:12px;">Reason</th>
                <th style="padding:12px;">Notes</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $it): ?>
                <?php $sid = (int)$it['sale_itemID']; $max=(int)$it['quantity_sold']; ?>
                <tr style="border-bottom: 1px solid #222;">
                  <td style="padding:12px;">
                    <input type="checkbox" name="selected[<?= $sid ?>]" />
                  </td>
                  <td style="padding:12px;">
                    <?= h($it['product_name']) ?>
                    <div style="opacity:.7; font-size:12px;">sale_itemID: <?= $sid ?> | productID: <?= (int)$it['productID'] ?></div>
                  </td>
                  <td style="padding:12px;"><?= $max ?></td>
                  <td style="padding:12px;">
                    <input type="number" name="return_quantity[<?= $sid ?>]" min="1" max="<?= $max ?>" value="1"
                           style="width:90px; padding:8px; border-radius:10px;"/>
                  </td>
                  <td style="padding:12px;">
                    <select name="reason[<?= $sid ?>]" style="padding:8px; border-radius:10px;">
                      <option value="Defective">Defective</option>
                      <option value="Change of Mind">Change of Mind</option>
                    </select>
                  </td>
                  <td style="padding:12px;">
                    <input type="text" name="notes[<?= $sid ?>]" placeholder="Optional notes"
                           style="width: 220px; padding:8px; border-radius:10px;"/>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <div style="margin-top:14px; display:flex; justify-content:flex-end;">
            <button type="submit" style="padding:12px 16px; border-radius:12px; cursor:pointer;">
              Submit Return Request
            </button>
          </div>

          <p style="opacity:.75; margin-top:10px;">
            Note: Return status is set to <strong>Store Credit</strong> by default for prototype. Admin will update later.
          </p>
        </form>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <div style="margin-top:18px;">
    <a href="cart.php">Back to Cart</a>
  </div>
</main>
</body>
</html>
