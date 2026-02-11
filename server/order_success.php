<?php
require __DIR__ . "/lib/session.php";
require __DIR__ . "/config/database.php";
require __DIR__ . "/config/app.php";

$saleID = (int)($_SESSION['last_sale_id'] ?? 0);
if ($saleID <= 0) {
    header("Location: cart.php");
    exit;
}

$pdo = db();

// Get sale header
$stmtSale = $pdo->prepare("
    SELECT saleID, sale_date, total_amount, customerID
    FROM sale
    WHERE saleID = ?
    LIMIT 1
");
$stmtSale->execute([$saleID]);
$sale = $stmtSale->fetch();

if (!$sale) {
    header("Location: cart.php");
    exit;
}

// Get sale items with product name
$stmtItems = $pdo->prepare("
    SELECT si.sale_itemID, si.quantity_sold, si.price_at_sale, si.productID,
           p.product_name
    FROM sale_item si
    JOIN product p ON p.productID = si.productID
    WHERE si.saleID = ?
");
$stmtItems->execute([$saleID]);
$items = $stmtItems->fetchAll();

function money($n) { return number_format((float)$n, 2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Receipt</title>
  <link rel="stylesheet" href="../client/client_cart.css">
</head>
<body>
  <main style="max-width: 900px; margin: 40px auto; padding: 0 16px;">
    <h1>Order Confirmed</h1>
    <p style="opacity:.85;">This is a prototype receipt page generated from the database.</p>

    <section style="margin-top:16px; padding:16px; border:1px solid #2a2a2a; border-radius:12px;">
      <div style="opacity:.75;">Sale ID</div>
      <div style="font-size:22px; font-weight:700;"><?= (int)$sale['saleID'] ?></div>

      <div style="margin-top:12px; opacity:.75;">Customer ID (Prototype)</div>
      <div><?= (int)$sale['customerID'] ?></div>

      <div style="margin-top:12px; opacity:.75;">Sale Date</div>
      <div><?= htmlspecialchars($sale['sale_date']) ?></div>
    </section>

    <h2 style="margin-top:22px;">Items</h2>
    <table style="width:100%; border-collapse: collapse; overflow:hidden; border-radius:12px;">
      <thead>
        <tr style="text-align:left; border-bottom: 1px solid #2a2a2a;">
          <th style="padding:12px;">Product</th>
          <th style="padding:12px;">Unit Price</th>
          <th style="padding:12px;">Qty</th>
          <th style="padding:12px;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it):
          $subtotal = (float)$it['price_at_sale'] * (int)$it['quantity_sold'];
        ?>
          <tr style="border-bottom: 1px solid #222;">
            <td style="padding:12px;"><?= htmlspecialchars($it['product_name']) ?> <div style="opacity:.7; font-size:12px;">ID: <?= (int)$it['productID'] ?></div></td>
            <td style="padding:12px;"><?= CURRENCY_SYMBOL . money($it['price_at_sale']) ?></td>
            <td style="padding:12px;"><?= (int)$it['quantity_sold'] ?></td>
            <td style="padding:12px;"><?= CURRENCY_SYMBOL . money($subtotal) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <section style="margin-top:18px; text-align:right;">
      <div style="opacity:.75;">Total Paid (Simulated)</div>
      <div style="font-size: 22px; font-weight: 700;"><?= CURRENCY_SYMBOL . money($sale['total_amount']) ?></div>
      <div style="margin-top:10px; opacity:.7;">Payment Ref (Simulated): <?= "SIM-" . time() ?></div>
    </section>

    <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap;">
      <a href="orders.php">View My Orders</a>
      <a href="cart.php">Back to Cart</a>
    </div>
  </main>
</body>
</html>
