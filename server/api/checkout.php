<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../cart/mongo_cart.php';

header('Content-Type: application/json');

function gen_public_id(string $prefix = 'S-'): string {
    return $prefix . strtoupper(bin2hex(random_bytes(4)));
}

try {
    $cart = cart_get();
    $items = $cart['items'];

    if (count($items) === 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Cart is empty']);
        exit;
    }

    // Minimal shipping payload (you can expand later)
    $fulfillment_method = $_POST['fulfillment_method'] ?? 'Delivery'; // Pickup | Delivery | Walk-in

    $pdo = db();
    $pdo->beginTransaction(); // transaction start :contentReference[oaicite:3]{index=3}

    // 1) Lock product rows and verify stock
    $total = 0.0;
    $lockedProducts = []; // productID => row

    foreach ($items as $it) {
        $pid = (int)$it['productID'];
        $qty = (int)$it['qty'];

        // Lock the row so two checkouts don't oversell :contentReference[oaicite:4]{index=4}
        $stmt = $pdo->prepare("SELECT productID, product_name, quantity, selling_price, is_active
                               FROM product
                               WHERE productID = ?
                               FOR UPDATE");
        $stmt->execute([$pid]);
        $p = $stmt->fetch();

        if (!$p || (int)$p['is_active'] !== 1) {
            throw new Exception("Product not found/inactive: $pid");
        }

        if ((int)$p['quantity'] < $qty) {
            throw new Exception("Insufficient stock for {$p['product_name']} (requested $qty, available {$p['quantity']})");
        }

        $lockedProducts[$pid] = $p;
        $total += ((float)$p['selling_price'] * $qty);
    }

    // 2) Create sale row
    $sale_public = gen_public_id('S-');

    $stmt = $pdo->prepare("
        INSERT INTO sale (public_id, total_amount, customerID, employeeID, fulfillment_method)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $sale_public,
        $total,
        PROTOTYPE_CUSTOMER_ID,
        PROTOTYPE_EMPLOYEE_ID,
        $fulfillment_method
    ]);

    $saleID = (int)$pdo->lastInsertId();

    // 3) Insert sale items + deduct inventory + create inventory_transaction
    foreach ($items as $it) {
        $pid = (int)$it['productID'];
        $qty = (int)$it['qty'];
        $p = $lockedProducts[$pid];

        $price = (float)$p['selling_price'];

        $stmt = $pdo->prepare("
            INSERT INTO sale_item (quantity_sold, price_at_sale, saleID, productID)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$qty, $price, $saleID, $pid]);

        $stmt = $pdo->prepare("UPDATE product SET quantity = quantity - ? WHERE productID = ?");
        $stmt->execute([$qty, $pid]);

        $stmt = $pdo->prepare("
            INSERT INTO inventory_transaction (quantity_change, transaction_type, referenceID, productID, employeeID)
            VALUES (?, 'Sale', ?, ?, ?)
        ");
        $stmt->execute([-1 * $qty, $saleID, $pid, PROTOTYPE_EMPLOYEE_ID]);
    }

    $pdo->commit();

    // 4) Clear cart in MongoDB only after commit
    cart_clear();

    echo json_encode([
        'ok' => true,
        'saleID' => $saleID,
        'sale_public_id' => $sale_public,
        'total' => $total
    ]);

} catch (Throwable $e) {
    // Rollback if transaction active
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
