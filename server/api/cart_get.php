<?php
require_once __DIR__ . '/../cart/mongo_cart.php';

header('Content-Type: application/json');

try {
    echo json_encode([
        'ok' => true,
        'cart' => cart_get()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
