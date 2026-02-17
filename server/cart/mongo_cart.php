<?php
// server/cart/mongo_cart.php
require_once __DIR__ . '/../config/mongo.php';
require_once __DIR__ . '/../config/app.php';

function cart_key_customer_id(): int {
    // Later this will come from login session
    return PROTOTYPE_CUSTOMER_ID;
}

function cart_get(): array {
    $mgr = mongo_manager();

    $filter = ['customer_id' => cart_key_customer_id()];
    $query  = new MongoDB\Driver\Query($filter, ['limit' => 1]);

    $ns = MONGO_DB . '.' . MONGO_CART_COLLECTION;
    $rows = $mgr->executeQuery($ns, $query)->toArray();

    if (!$rows) {
        return ['customer_id' => cart_key_customer_id(), 'items' => []];
    }

    // Convert BSON document to PHP array safely
    $doc = json_decode(json_encode($rows[0]), true);

    return [
        'customer_id' => $doc['customer_id'],
        'items' => $doc['items'] ?? []
    ];
}

function cart_save(array $items): void {
    $mgr = mongo_manager();
    $ns  = MONGO_DB . '.' . MONGO_CART_COLLECTION;

    $bulk = new MongoDB\Driver\BulkWrite;

    // Upsert cart by customer_id
    $filter = ['customer_id' => cart_key_customer_id()];
    $update = [
        '$set' => [
            'customer_id' => cart_key_customer_id(),
            'items' => array_values($items),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];

    $bulk->update($filter, $update, ['upsert' => true]);
    $mgr->executeBulkWrite($ns, $bulk);
}

function cart_clear(): void {
    cart_save([]);
}

function cart_add_item(int $productID, string $product_name, float $unit_price, int $qty): array {
    if ($qty < 1) $qty = 1;

    $cart = cart_get();
    $items = $cart['items'];

    $found = false;
    foreach ($items as &$it) {
        if ((int)$it['productID'] === $productID) {
            $it['qty'] = (int)$it['qty'] + $qty;
            $found = true;
            break;
        }
    }
    unset($it);

    if (!$found) {
        $items[] = [
            'productID' => $productID,
            'product_name' => $product_name,
            'unit_price' => $unit_price,
            'qty' => $qty
        ];
    }

    cart_save($items);
    return cart_get();
}

function cart_update_qty(int $productID, int $qty): array {
    $cart = cart_get();
    $items = $cart['items'];

    $newItems = [];
    foreach ($items as $it) {
        if ((int)$it['productID'] === $productID) {
            if ($qty > 0) {
                $it['qty'] = $qty;
                $newItems[] = $it;
            }
            // if qty <= 0 => remove item
        } else {
            $newItems[] = $it;
        }
    }

    cart_save($newItems);
    return cart_get();
}

function cart_remove_item(int $productID): array {
    return cart_update_qty($productID, 0);
}
