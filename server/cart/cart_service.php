<?php

/**
 * Add item to cart session
 */
function cartAdd(array &$cart, array $product, int $qty): void {
    $productID = (int)$product['productID'];

    if (!isset($cart['items'][$productID])) {
        $cart['items'][$productID] = [
            'productID' => $productID,
            'product_name' => $product['product_name'],
            'unit_price' => (float)$product['selling_price'],
            'qty' => 0
        ];
    }

    $cart['items'][$productID]['qty'] += $qty;
}

/**
 * Update item quantity
 */
function cartUpdate(array &$cart, int $productID, int $qty): void {
    if (!isset($cart['items'][$productID])) return;

    if ($qty <= 0) {
        unset($cart['items'][$productID]);
    } else {
        $cart['items'][$productID]['qty'] = $qty;
    }
}

/**
 * Remove item
 */
function cartRemove(array &$cart, int $productID): void {
    if (isset($cart['items'][$productID])) {
        unset($cart['items'][$productID]);
    }
}

/**
 * Clear cart
 */
function cartClear(array &$cart): void {
    $cart['items'] = [];
}

/**
 * Compute total
 */
function cartTotal(array $cart): float {
    $total = 0.0;
    foreach ($cart['items'] as $item) {
        $total += ((float)$item['unit_price'] * (int)$item['qty']);
    }
    return $total;
}
