<?php
require __DIR__ . "/lib/session.php";
require __DIR__ . "/config/database.php";
require __DIR__ . "/config/app.php";

$pdo = db();
$customerID = PROTOTYPE_CUSTOMER_ID;

$returnID = (int)($_GET['returnID'] ?? 0);
if ($returnID <= 0) { header("Location: returns_list.php"); exit; }

$stmtHeader = $pdo->prepare("
    SELECT returnID, return_date, refund_amount, customerID
    FROM return_transaction
    WHERE returnID = ? AND customerID = ?
    LIMIT 1
");
$stmtHeader->execute([$returnID, $customerID]);
$hdr = $stmtHeader->fetch();
if (!$hdr) { header("Location: returns_list.php"); exit; }

$stmtItems = $pdo->prepare("
    SELECT ri.return_itemID, ri.return_quantity, ri.reason, ri.return_status, ri.notes,
           ri.sale_itemID, p.product_name
    FROM return_item ri
    JOIN sale_item si ON si.sale_itemID = ri.sale_itemID
    JOIN product p ON p.productID = si.productID
    WHERE ri.returnID = ?
");
$stmtItems->execute([$returnID]);
$items = $stmtItems->fetchAll();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Return Details</title>
  <link rel="stylesheet" href="../client/client_cart.css">
</head>
<body>
<main style="max-width: 900px; margin: 40px auto; padding: 0 16px;">
  <h1>Return Details</h1>

  <section style="margin-top:16px; padding:16px; border:1px solid #2a2a2a; border-radius:12px;">
    <div style="opacity:.75;">Return ID</div>
    <div style="font-size:22px; font-weight:700;"><?= (int)$hdr['returnID'] ?></div>

    <div style="margin-top:12px; opacity:.75;">Return Date</div>
    <div><?= h($hdr['return_date']) ?></div>

    <div style="margin-top:12px; opacity:.75;">Refund Amount</div>
    <div><?= $hdr['refund_amount'] === null ? 'NULL (pending)' : h($hdr['refund_amount']) ?></div>
  </section>

  <h2 style="margin-top:22px;">Returned Items</h2>

  <?php if (empty($items)): ?>
    <p>No items found.</p>
  <?php else: ?>
    <table style="width:100%; border-collapse: collapse; overflow:hidden; border-radius:12px;">
      <thead>
        <tr style="text-align:left; border-bottom: 1px solid #2a2a2a;">
          <th style="padding:12px;">Product</th>
          <th style="padding:12px;">Qty</th>
          <th style="padding:12px;">Reason</th>
          <th style="padding:12px;">Status</th>
          <th style="padding:12px;">Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr style="border-bottom: 1px solid #222;">
            <td style="padding:12px;"><?= h($it['product_name']) ?><div style="opacity:.7;font-size:12px;">sale_itemID: <?= (int)$it['sale_itemID'] ?></div></td>
            <td style="padding:12px;"><?= (int)$it['return_quantity'] ?></td>
            <td style="padding:12px;"><?= h($it['reason']) ?></td>
            <td style="padding:12px;"><?= h($it['return_status']) ?></td>
            <td style="padding:12px;"><?= h($it['notes'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div style="margin-top:18px; display:flex; gap:12px;">
    <a href="returns_list.php">Back to Returns</a>
    <a href="returns_request.php">Create Another Return</a>
  </div>
</main>
</body>
</html>
