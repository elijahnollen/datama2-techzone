<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/common.php';
require_once __DIR__ . '/../app/Models/services.php';
require_once __DIR__ . '/../app/Core/helpers.php';

$env = loadEnv(__DIR__ . '/../.env');
$pdo = mysqlPdo($env);
$manager = mongoManager($env);
if (!$manager) {
    fwrite(STDERR, "MongoDB connection is unavailable.\n");
    exit(1);
}

$db = mongoDbName($env);
$collection = mongoCollectionName($env, 'orders');

$catalog = loadProductCatalog($env);
$catalogByPublicId = catalogMapByPublicId($catalog);

$itemsBySaleId = [];
$itemsStmt = $pdo->query(
    'SELECT si.saleID,
            si.quantity_sold,
            si.price_at_sale,
            p.public_id AS product_public_id,
            p.product_name
     FROM sale_item si
     JOIN product p ON p.productID = si.productID'
);
foreach ($itemsStmt->fetchAll() as $row) {
    $saleId = (int) ($row['saleID'] ?? 0);
    if ($saleId <= 0) {
        continue;
    }
    $productPublicId = asString($row['product_public_id'] ?? '');
    $catalogItem = $catalogByPublicId[$productPublicId] ?? [];
    $itemsBySaleId[$saleId][] = [
        'product_public_id' => $productPublicId,
        'product_name' => asString($row['product_name'] ?? ''),
        'quantity' => (int) ($row['quantity_sold'] ?? 0),
        'price_at_sale' => (float) ($row['price_at_sale'] ?? 0),
        'image_url' => asString($catalogItem['image_url'] ?? ''),
    ];
}

$salesStmt = $pdo->query(
    'SELECT s.saleID,
            s.public_id,
            s.sale_date,
            s.updated_at,
            s.sale_status,
            s.fulfillment_method,
            s.tracking_number,
            s.courier_name,
            s.shipping_name,
            s.shipping_street,
            s.shipping_barangay,
            s.shipping_city_municipality,
            s.shipping_province,
            s.shipping_zip_code,
            c.public_id AS customer_public_id,
            c.email_address,
            c.contact_number,
            c.first_name,
            c.last_name,
            c.street_address,
            c.barangay,
            c.city_municipality,
            c.province,
            c.zip_code,
            pay.payment_method,
            pay.payment_status,
            pay.amount,
            pay.public_id AS payment_public_id,
            pay.payment_date,
            pay.updated_at AS payment_updated_at
     FROM sale s
     JOIN customer c ON c.customerID = s.customerID
     LEFT JOIN (
        SELECT p.saleID, p.payment_method, p.payment_status, p.amount, p.public_id, p.payment_date, p.updated_at
        FROM payment p
        JOIN (
            SELECT saleID, MAX(paymentID) AS latest_payment_id
            FROM payment
            WHERE saleID IS NOT NULL
            GROUP BY saleID
        ) latest ON latest.latest_payment_id = p.paymentID
     ) pay ON pay.saleID = s.saleID
     WHERE s.public_id IS NOT NULL AND s.public_id <> ""'
);

$processed = 0;
foreach ($salesStmt->fetchAll() as $row) {
    $saleId = (int) ($row['saleID'] ?? 0);
    $orderPublicId = asString($row['public_id'] ?? '');
    if ($saleId <= 0 || $orderPublicId === '') {
        continue;
    }

    $saleDate = asString($row['sale_date'] ?? '');
    $saleUpdatedAt = asString($row['updated_at'] ?? '');
    $paymentUpdatedAt = asString($row['payment_updated_at'] ?? '');
    $paymentDate = asString($row['payment_date'] ?? '');
    $updatedAt = $saleUpdatedAt !== '' ? $saleUpdatedAt : ($paymentUpdatedAt !== '' ? $paymentUpdatedAt : ($paymentDate !== '' ? $paymentDate : $saleDate));
    if ($updatedAt === '') {
        $updatedAt = nowUtc();
    }
    $createdAt = $saleDate !== '' ? $saleDate : $updatedAt;

    $fulfillmentMethod = asString($row['fulfillment_method'] ?? '');
    $normalizedStatus = normalizeSaleStatusForOutput(asString($row['sale_status'] ?? ''), $fulfillmentMethod);

    $statusPath = orderTimelinePath($normalizedStatus, $fulfillmentMethod);
    if ($statusPath === []) {
        $statusPath = [$normalizedStatus !== '' ? $normalizedStatus : 'Pending'];
    } else {
        $currentIndex = count($statusPath) - 1;
        foreach ($statusPath as $idx => $step) {
            if (strcasecmp($step, $normalizedStatus) === 0) {
                $currentIndex = $idx;
                break;
            }
        }
        if (strcasecmp($statusPath[$currentIndex] ?? '', $normalizedStatus) !== 0) {
            $statusPath[] = $normalizedStatus !== '' ? $normalizedStatus : 'Pending';
            $currentIndex = count($statusPath) - 1;
        }
        $statusPath = array_slice($statusPath, 0, $currentIndex + 1);
    }
    $history = [];
    foreach ($statusPath as $idx => $step) {
        $history[] = [
            'status' => $step,
            'timestamp' => $idx === count($statusPath) - 1 ? $updatedAt : $createdAt,
            'updated_by' => 'System',
            'remarks' => 'Order synchronized from sale record.',
        ];
    }

    $items = $itemsBySaleId[$saleId] ?? [];
    $subtotal = 0.0;
    foreach ($items as $item) {
        $subtotal += ((float) ($item['price_at_sale'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
    }
    $paymentAmount = (float) ($row['amount'] ?? 0);
    $grandTotal = $paymentAmount > 0 ? $paymentAmount : $subtotal;

    $paymentMethod = asString($row['payment_method'] ?? 'Cash');
    $rawPaymentStatus = asString($row['payment_status'] ?? '');
    $normalizedPaymentStatus = normalizeOrderPaymentStatus($rawPaymentStatus !== '' ? $rawPaymentStatus : 'Pending');
    $paymentStatusTimestamp = $paymentUpdatedAt !== '' ? $paymentUpdatedAt : ($paymentDate !== '' ? $paymentDate : $createdAt);

    $paymentHistory = [[
        'status' => $normalizedPaymentStatus,
        'timestamp' => $paymentStatusTimestamp,
        'updated_by' => 'System',
        'remarks' => 'Payment status synchronized from sale record.',
    ]];

    $customerName = trim(asString($row['first_name'] ?? '') . ' ' . asString($row['last_name'] ?? ''));
    $shippingName = asString($row['shipping_name'] ?? '');
    if ($shippingName === '') {
        $shippingName = $customerName;
    }
    $shippingAddress = [
        'full_name' => $shippingName,
        'phone' => asString($row['contact_number'] ?? ''),
        'email' => asString($row['email_address'] ?? ''),
        'street_address' => asString($row['shipping_street'] ?? $row['street_address'] ?? ''),
        'barangay' => asString($row['shipping_barangay'] ?? $row['barangay'] ?? ''),
        'city_municipality' => asString($row['shipping_city_municipality'] ?? $row['city_municipality'] ?? ''),
        'province' => asString($row['shipping_province'] ?? $row['province'] ?? ''),
        'zip_code' => asString($row['shipping_zip_code'] ?? $row['zip_code'] ?? ''),
    ];

    $trackingNumber = asString($row['tracking_number'] ?? '');
    $courierName = asString($row['courier_name'] ?? '');
    $doc = [
        'order_public_id' => $orderPublicId,
        'customer_public_id' => asString($row['customer_public_id'] ?? ''),
        'items' => array_values($items),
        'payment' => [
            'subtotal' => round($subtotal, 2),
            'grand_total' => round($grandTotal, 2),
            'payment_method' => $paymentMethod,
            'payment_status' => $normalizedPaymentStatus,
            'transaction_reference' => asString($row['payment_public_id'] ?? ''),
        ],
        'shipping_address' => $shippingAddress,
        'logistics' => [
            'carrier' => $courierName !== '' ? $courierName : null,
            'tracking_number' => $trackingNumber !== '' ? $trackingNumber : null,
        ],
        'order_status' => $normalizedStatus,
        'status_history' => $history,
        'payment_history_status' => $paymentHistory,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];

    mongoUpdateOne($manager, $db, $collection, ['order_public_id' => $orderPublicId], ['$set' => $doc], true);
    $processed++;
}

echo "Rebuilt orders: {$processed}\n";
