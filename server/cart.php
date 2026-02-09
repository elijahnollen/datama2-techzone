<?php
require __DIR__ . "/lib/session.php";
require __DIR__ . "/config/database.php";
require __DIR__ . "/cart/cart_service.php";

function money($n) { return number_format((float)$n, 2); }

function getProductById(int $productID) {
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT productID, product_name, quantity, selling_price, is_active
        FROM product
        WHERE productID = ? LIMIT 1
    ");
    $stmt->execute([$productID]);
    return $stmt->fetch();
}

$action = $_POST['action'] ?? null;

if ($action === "add") {
    $productID = (int)($_POST['productID'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 0);

    if ($productID > 0 && $qty > 0) {
        $p = getProductById($productID);

        if ($p && (int)$p['is_active'] === 1) {
            // Basic stock check (checkout will re-check again)
            if ((int)$p['quantity'] >= $qty) {
                cartAdd($_SESSION['cart'], $p, $qty);
            }
        }
    }

    header("Location: cart.php");
    exit;
}

if ($action === "update") {
    $productID = (int)($_POST['productID'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 0);

    if ($productID > 0 && isset($_SESSION['cart']['items'][$productID])) {
        if ($qty <= 0) {
            cartRemove($_SESSION['cart'], $productID);
        } else {
            $p = getProductById($productID);
            if ($p) {
                $max = (int)$p['quantity'];
                $qty = min($qty, $max);
                cartUpdate($_SESSION['cart'], $productID, $qty);
            } else {
                cartRemove($_SESSION['cart'], $productID);
            }
        }
    }

    header("Location: cart.php");
    exit;
}

if ($action === "remove") {
    $productID = (int)($_POST['productID'] ?? 0);
    if ($productID > 0) {
        cartRemove($_SESSION['cart'], $productID);
    }
    header("Location: cart.php");
    exit;
}

if ($action === "clear") {
    cartClear($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// Render
$items = $_SESSION['cart']['items'] ?? [];
$total = cartTotal($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TechZone - Cart</title>
  <link rel="stylesheet" href="../client/client_cart.css">
</head>
<body>
  <main style="max-width: 900px; margin: 40px auto; padding: 0 16px;">
    <h1 style="margin-bottom: 12px;">Shopping Cart</h1>

    <!-- Quick Add Form (Prototype Testing) -->
    <section style="margin: 18px 0; padding: 16px; border: 1px solid #2a2a2a; border-radius: 12px;">
      <h3 style="margin-top: 0;">Add Item (Prototype)</h3>
      <form method="POST" action="cart.php" style="display:flex; gap:10px; flex-wrap:wrap;">
        <input type="hidden" name="action" value="add">
        <input name="productID" type="number" placeholder="Product ID" required style="padding:10px; border-radius:10px;">
        <input name="qty" type="number" placeholder="Qty" min="1" required style="padding:10px; border-radius:10px;">
        <button type="submit" style="padding:10px 14px; border-radius:10px; cursor:pointer;">Add to Cart</button>
      </form>
      <p style="opacity:.75; margin:10px 0 0;">This is for testing, product page can submit the same fields.</p>
    </section>

    <?php if (empty($items)): ?>
      <p>Your cart is empty.</p>
    <?php else: ?>
      <table style="width:100%; border-collapse: collapse; overflow:hidden; border-radius:12px;">
        <thead>
          <tr style="text-align:left; border-bottom: 1px solid #2a2a2a;">
            <th style="padding:12px;">Product</th>
            <th style="padding:12px;">Unit Price</th>
            <th style="padding:12px;">Qty</th>
            <th style="padding:12px;">Subtotal</th>
            <th style="padding:12px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): 
            $subtotal = (float)$it['unit_price'] * (int)$it['qty'];
          ?>
            <tr style="border-bottom: 1px solid #222;">
              <td style="padding:12px;">
                <?= htmlspecialchars($it['product_name']) ?>
                <div style="opacity:.7; font-size: 12px;">ID: <?= (int)$it['productID'] ?></div>
              </td>
              <td style="padding:12px;">₱<?= money($it['unit_price']) ?></td>
              <td style="padding:12px;">
                <form method="POST" action="cart.php" style="display:flex; gap:8px;">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="productID" value="<?= (int)$it['productID'] ?>">
                  <input name="qty" type="number" min="0" value="<?= (int)$it['qty'] ?>" style="width:80px; padding:8px; border-radius:10px;">
                  <button type="submit" style="padding:8px 10px; border-radius:10px; cursor:pointer;">Update</button>
                </form>
              </td>
              <td style="padding:12px;">₱<?= money($subtotal) ?></td>
              <td style="padding:12px;">
                <form method="POST" action="cart.php">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="productID" value="<?= (int)$it['productID'] ?>">
                  <button type="submit" style="padding:8px 10px; border-radius:10px; cursor:pointer;">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <section style="margin-top: 18px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
        <form method="POST" action="cart.php">
          <input type="hidden" name="action" value="clear">
          <button type="submit" style="padding:10px 14px; border-radius:10px; cursor:pointer;">Clear Cart</button>
        </form>

        <div style="text-align:right;">
          <div style="opacity:.75;">Total</div>
          <div style="font-size: 22px; font-weight: 700;">₱<?= money($total) ?></div>

          <form method="POST" action="checkout.php" style="margin-top:10px;">
            <button type="submit" style="padding:12px 16px; border-radius:12px; cursor:pointer;">
              Checkout (Prototype)
            </button>
          </form>
        </div>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
