<?php
require __DIR__ . "/../server/lib/session.php";
require __DIR__ . "/../server/config/database.php";

$pdo = db();

$stmt = $pdo->query("
  SELECT rt.returnID, rt.return_date, rt.refund_amount, rt.customerID, rt.employeeID
  FROM return_transaction rt
  ORDER BY rt.returnID DESC
  LIMIT 50
");
$returns = $stmt->fetchAll();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n){ return number_format((float)$n, 2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin - Returns</title>
  <link rel="stylesheet" href="../client/client_cart.css">
</head>
<body>
<main style="max-width:1100px;margin:40px auto;padding:0 16px;">
  <h1>Admin Return/Refund Management</h1>
  <p style="opacity:.85;">Prototype admin view of latest 50 return requests.</p>

  <?php if (empty($returns)): ?>
    <p>No return requests yet.</p>
  <?php else: ?>
    <table style="width:100%; border-collapse: collapse; overflow:hidden; border-radius:12px;">
      <thead>
        <tr style="text-align:left; border-bottom: 1px solid #2a2a2a;">
          <th style="padding:12px;">Return ID</th>
          <th style="padding:12px;">Return Date</th>
          <th style="padding:12px;">CustomerID</th>
          <th style="padding:12px;">EmployeeID</th>
          <th style="padding:12px;">Refund Amount</th>
          <th style="padding:12px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($returns as $r): ?>
          <tr style="border-bottom: 1px solid #222;">
            <td style="padding:12px;"><?= (int)$r['returnID'] ?></td>
            <td style="padding:12px;"><?= h($r['return_date']) ?></td>
            <td style="padding:12px;"><?= (int)$r['customerID'] ?></td>
            <td style="padding:12px;"><?= (int)$r['employeeID'] ?></td>
            <td style="padding:12px;">
              <?= $r['refund_amount'] === null ? 'NULL (pending)' : ('₱'.money($r['refund_amount'])) ?>
            </td>
            <td style="padding:12px;">
              <a href="returns_view.php?returnID=<?= (int)$r['returnID'] ?>">Open</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div style="margin-top:18px;">
    <a href="orders.php">Admin Orders</a>
  </div>
</main>
</body>
</html>
