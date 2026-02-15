<?php
require __DIR__ . "/../server/lib/session.php";
require __DIR__ . "/../server/config/database.php";

$pdo = db();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n){ return number_format((float)$n, 2); }

$returnID = (int)($_GET['returnID'] ?? 0);
if ($returnID <= 0) { header("Location: returns.php"); exit; }

$err = null;
$ok = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $refund_amount = $_POST['refund_amount'] ?? null;
    $refund_amount = ($refund_amount === '' || $refund_amount === null) ? null : (float)$refund_amount;

    $statusMap = $_POST['return_status'] ?? []; // return_itemID => status
    $notesMap  = $_POST['notes'] ?? []; // return_itemID => notes

    $allowed = ['Refunded','Replaced','Store Credit'];

    try {
        $pdo->beginTransaction();

        // Update header refund_amount (can be NULL)
        $stmtHdr = $pdo->prepare("UPDATE return_transaction SET refund_amount = ? WHERE returnID = ?");
        $stmtHdr->execute([$refund_amount, $returnID]);

        // Update each return_item
        $stmtUpd = $pdo->prepare("
          UPDATE return_item
          SET return_status = ?, notes = ?
          WHERE return_itemID = ? AND returnID = ?
        ");

        foreach ($statusMap as $return_itemID_str => $status) {
            $return_itemID = (int)$return_itemID_str;
            $status = (string)$status;
            $notes = $notesMap[$return_itemID_str] ?? null;

            if (!in_array($status, $allowed, true)) {
                throw new Exception("Invalid status selected.");
            }

            $stmtUpd->execute([$status, ($notes === '' ? null : $notes), $return_itemID, $returnID]);
        }

        $pdo->commit();
        $ok = "Return updated successfully.";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $err = $e->getMessage();
    }
}

// Load header
$stmtHdr = $pdo->prepare("
  SELECT returnID, return_date, refund_amount, customerID, employeeID
  FROM return_transaction
  WHERE returnID = ?
  LIMIT 1
");
$stmtHdr->execute([$returnID]);
$hdr = $stmtHdr->fetch();
if (!$hdr) { header("Location: returns.php"); exit; }

// Load items
$stmtItems = $pdo->prepare("
  SELECT ri.return_itemID, ri.return_quantity, ri.reason, ri.return_status, ri.notes,
         ri.sale_itemID, p.product_name, si.price_at_sale
  FROM return_item ri
  JOIN sale_item si ON si.sale_itemID = ri.sale_itemID
  JOIN product p ON p.productID = si.productID
  WHERE ri.returnID = ?
  ORDER BY ri.return_itemID ASC
");
$stmtItems->execute([$returnID]);
$items = $stmtItems->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin - Return #<?= (int)$returnID ?></title>
  <link rel="stylesheet" href="../client/client_cart.css">
</head>
<body>
<main style="max-width:1100px;margin:40px auto;padding:0 16px;">
  <h1>Return Request #<?= (int)$returnID ?></h1>

  <?php if ($err): ?>
    <div style="padding:12px; border:1px solid #aa3333; border-radius:12px; margin:12px 0;">
      <strong>Error:</strong> <?= h($err) ?>
    </div>
  <?php endif; ?>

  <?php if ($ok): ?>
    <div style="padding:12px; border:1px solid #2f8f2f; border-radius:12px; margin:12px 0;">
      <?= h($ok) ?>
    </div>
  <?php endif; ?>

  <section style="margin-top:16px; padding:16px; border:1px solid #2a2a2a; border-radius:12px;">
    <div style="opacity:.75;">Return Date</div>
    <div><?= h($hdr['return_date']) ?></div>

    <div style="margin-top:10px; opacity:.75;">CustomerID</div>
    <div><?= (int)$hdr['customerID'] ?></div>

    <div style="margin-top:10px; opacity:.75;">EmployeeID</div>
    <div><?= (int)$hdr['employeeID'] ?></div>
  </section>

  <form method="POST" action="returns_view.php?returnID=<?= (int)$returnID ?>" style="margin-top:18px;">
    <section style="padding:16px; border:1px solid #2a2a2a; border-radius:12px;">
      <h3 style="margin:0 0 10px;">Refund Amount (Header)</h3>
      <p style="opacity:.75; margin:0 0 10px;">
        Set this only if the final decision is refund. Otherwise you can leave it NULL.
      </p>
      <input type="number" step="0.01" name="refund_amount"
             value="<?= $hdr['refund_amount'] === null ? '' : h($hdr['refund_amount']) ?>"
             placeholder="e.g., 1500.00"
             style="padding:10px; border-radius:10px; width:220px;">
    </section>

    <h2 style="margin-top:22px;">Returned Items</h2>

    <?php if (empty($items)): ?>
      <p>No return items found.</p>
    <?php else: ?>
      <table style="width:100%; border-collapse: collapse; overflow:hidden; border-radius:12px;">
        <thead>
          <tr style="text-align:left; border-bottom: 1px solid #2a2a2a;">
            <th style="padding:12px;">Product</th>
            <th style="padding:12px;">Qty</th>
            <th style="padding:12px;">Reason</th>
            <th style="padding:12px;">Current Status</th>
            <th style="padding:12px;">Set Final Status</th>
            <th style="padding:12px;">Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr style="border-bottom: 1px solid #222;">
              <td style="padding:12px;">
                <?= h($it['product_name']) ?>
                <div style="opacity:.7;font-size:12px;">
                  return_itemID: <?= (int)$it['return_itemID'] ?> | sale_itemID: <?= (int)$it['sale_itemID'] ?> | ₱<?= money($it['price_at_sale']) ?>
                </div>
              </td>
              <td style="padding:12px;"><?= (int)$it['return_quantity'] ?></td>
              <td style="padding:12px;"><?= h($it['reason']) ?></td>
              <td style="padding:12px;"><?= h($it['return_status']) ?></td>
              <td style="padding:12px;">
                <select name="return_status[<?= (int)$it['return_itemID'] ?>]" style="padding:8px; border-radius:10px;">
                  <?php foreach (['Refunded','Replaced','Store Credit'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $opt === $it['return_status'] ? 'selected' : '' ?>>
                      <?= $opt ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td style="padding:12px;">
                <input type="text"
                       name="notes[<?= (int)$it['return_itemID'] ?>]"
                       value="<?= h($it['notes'] ?? '') ?>"
                       placeholder="Optional notes"
                       style="padding:8px; border-radius:10px; width:240px;">
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="margin-top:14px; display:flex; justify-content:flex-end;">
        <button type="submit" style="padding:12px 16px; border-radius:12px; cursor:pointer;">
          Save Updates
        </button>
      </div>
    <?php endif; ?>
  </form>

  <div style="margin-top:18px;">
    <a href="returns.php">Back to Returns</a>
  </div>
</main>
</body>
</html>
