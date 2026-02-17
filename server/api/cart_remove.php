<?php
require_once __DIR__ . '/../cart/mongo_cart.php';

header('Content-Type: application/json');

try {
    $productID = (int)($_POST['productID'] ?? 0);

    if ($productID <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'productID is required']);
        exit;
    }

    $cart = cart_remove_item($productID);
    echo json_encode(['ok' => true, 'cart' => $cart]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
