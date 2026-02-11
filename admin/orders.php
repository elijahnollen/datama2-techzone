<?php
require __DIR__ . "/../server/lib/session.php";
require __DIR__ . "/../server/config/database.php";
require __DIR__ . "/../server/config/app.php";

$pdo = db();
$stmt = $pdo->query("
    SELECT s.saleID, s.sale_date, s.total_amount, s.customerID, s.employeeID
    FROM sale s
    ORDER BY s.saleID DESC
    LIMIT 50
");
$sales = $stmt->fetchAll();

function money($n) { return number_format((float)$n, 2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Orders</title>
  <link rel="stylesheet" href="../client/client_cart.css">
</head>
<body>
  <main style="max-width: 1100px; margin: 40px auto; padding: 0 16px;">
    <h1>Admin Orders (Prototype)</h1>
    <p style="opacity:.85;">Showing latest 50 sales records.</p>

    <?php if (empty($sales)): ?>
      <p>No sales records found.</p>
    <?php else: ?>
      <table style="width:100%; border-collapse: collapse; overflow:hidden; border-radius:12px;">
        <thead>
          <tr style="text-align:left; border-bottom: 1px solid #2a2a2a;">
            <th style="padding:12px;">Sale ID</th>
            <th style="padding:12px;">Date</th>
            <th style="padding:12px;">Customer</th>
            <th style="padding:12px;">Employee</th>
            <th style="padding:12px;">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sales as $s): ?>
            <tr style="border-bottom: 1px solid #222;">
              <td style="padding:12px;"><?= (int)$s['saleID'] ?></td>
              <td style="padding:12px;"><?= htmlspecialchars($s['sale_date']) ?></td>
              <td style="padding:12px;"><?= (int)$s['customerID'] ?></td>
              <td style="padding:12px;"><?= (int)$s['employeeID'] ?></td>
              <td style="padding:12px;"><?= CURRENCY_SYMBOL . money($s['total_amount']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <div style="margin-top:18px;">
      <a href="../server/cart.php">Back to Cart</a>
    </div>
  </main>
</body>
</html>
