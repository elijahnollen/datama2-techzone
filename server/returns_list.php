<?php
require __DIR__ . "/lib/session.php";
require __DIR__ . "/config/database.php";
require __DIR__ . "/config/app.php";

$pdo = db();
$customerID = PROTOTYPE_CUSTOMER_ID;

$stmt = $pdo->prepare("
    SELECT returnID, return_date, refund_amount
    FROM return_transaction
    WHERE customerID = ?
    ORDER BY returnID DESC
");
$stmt->execute([$customerID]);
$rows = $stmt->fetchAll();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }
function money($n){ return number_format((float)$n,2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Returns</title>
  <link rel="stylesheet" href="../client/client_cart.css">
</head>
<body>
<main style="max-width: 900px; margin: 40px auto; padding: 0 16px;">
  <h1>My Return Requests (Prototype)</h1>
  <p style="opacity:.85;">Customer ID: <?= (int)$customerID ?></p>

  <?php if (empty($rows)): ?>
    <p>No returns yet.</p>
  <?php else: ?>
    <table style="width:100%; border-collapse: collapse; overflow:hidden; border-radius:12px;">
      <thead>
        <tr style="text-align:left; border-bottom: 1px solid #2a2a2a;">
          <th style="padding:12px;">Return ID</th>
          <th style="padding:12px;">Date</th>
          <th style="padding:12px;">Refund Amount</th>
          <th style="padding:12px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr style="border-bottom: 1px solid #222;">
            <td style="padding:12px;"><?= (int)$r['returnID'] ?></td>
            <td style="padding:12px;"><?= h($r['return_date']) ?></td>
            <td style="padding:12px;"><?= $r['refund_amount'] === null ? 'NULL' : ('₱'.money($r['refund_amount'])) ?></td>
            <td style="padding:12px;">
              <a href="returns_view.php?returnID=<?= (int)$r['returnID'] ?>">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div style="margin-top:18px; display:flex; gap:12px;">
    <a href="returns_request.php">Create Return</a>
    <a href="cart.php">Back to Cart</a>
  </div>
</main>
</body>
</html>
