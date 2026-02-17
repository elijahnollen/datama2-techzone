<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../cart/mongo_cart.php';

header('Content-Type: application/json');

try {
    $productID = (int)($_POST['productID'] ?? 0);
    $qty       = (int)($_POST['qty'] ?? 1);

    if ($productID <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'productID is required']);
        exit;
    }

    // Pull product name + price from MySQL so cart can't be spoofed
    $pdo = db();
    $stmt = $pdo->prepare("SELECT productID, product_name, selling_price, is_active FROM product WHERE productID = ?");
    $stmt->execute([$productID]);
    $p = $stmt->fetch();

    if (!$p || (int)$p['is_active'] !== 1) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Product not found or inactive']);
        exit;
    }

    $cart = cart_add_item(
        (int)$p['productID'],
        (string)$p['product_name'],
        (float)$p['selling_price'],
        $qty
    );

    echo json_encode(['ok' => true, 'cart' => $cart]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
