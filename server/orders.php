<?php
require __DIR__ . "/lib/session.php";
require __DIR__ . "/config/database.php";
require __DIR__ . "/config/app.php";

$customerID = PROTOTYPE_CUSTOMER_ID;

$pdo = db();
$stmt = $pdo->prepare("
    SELECT saleID, sale_date, total_amount
    FROM sale
    WHERE customerID = ?
    ORDER BY saleID DESC
");
$stmt->execute([$customerID]);
$sales = $stmt->fetchAll();

function money($n) { return number_format((float)$n, 2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Orders</title>
  <link rel="stylesheet" href="../client/client_cart.css">
</head>
<body>
  <main style="max-width: 900px; margin: 40px auto; padding: 0 16px;">
    <h1>My Orders (Prototype)</h1>
    <p style="opacity:.85;">Orders are filtered by Customer ID: <?= (int)$customerID ?> (hardcoded for prototype).</p>

    <?php if (empty($sales)): ?>
      <p>No orders yet.</p>
    <?php else: ?>
      <table style="width:100%; border-collapse: collapse; overflow:hidden; border-radius:12px;">
        <thead>
          <tr style="text-align:left; border-bottom: 1px solid #2a2a2a;">
            <th style="padding:12px;">Sale ID</th>
            <th style="padding:12px;">Date</th>
            <th style="padding:12px;">Total</th>
            <th style="padding:12px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sales as $s): ?>
            <tr style="border-bottom: 1px solid #222;">
              <td style="padding:12px;"><?= (int)$s['saleID'] ?></td>
              <td style="padding:12px;"><?= htmlspecialchars($s['sale_date']) ?></td>
              <td style="padding:12px;"><?= CURRENCY_SYMBOL . money($s['total_amount']) ?></td>
              <td style="padding:12px;">
                <form method="POST" action="order_view.php" style="margin:0;">
                  <input type="hidden" name="saleID" value="<?= (int)$s['saleID'] ?>">
                  <button type="submit" style="padding:8px 10px; border-radius:10px; cursor:pointer;">View</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <div style="margin-top:18px;">
      <a href="cart.php">Back to Cart</a>
    </div>
  </main>
</body>
</html>
