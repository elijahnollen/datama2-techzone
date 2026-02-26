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
$collection = mongoCollectionName($env, 'return_request');

function returnStatusPath(string $returnProgress, string $returnStatus): array
{
    $progress = strtolower(trim($returnProgress));
    $itemStatus = trim($returnStatus);
    $finalStatus = $itemStatus !== '' ? $itemStatus : 'Finalized';

    if ($progress === 'requested') {
        return ['Pending Review'];
    }
    if ($progress === 'approved') {
        return ['Pending Review', 'Approved'];
    }
    if ($progress === 'in process') {
        return ['Pending Review', 'Approved', 'In Process'];
    }
    if ($progress === 'rejected') {
        return ['Pending Review', 'Rejected'];
    }
    if ($progress === 'finalized') {
        return ['Pending Review', 'Approved', 'In Process', $finalStatus];
    }

    $normalized = normalizeMongoReturnRequestStatus($returnProgress, $returnStatus);
    return [$normalized !== '' ? $normalized : 'Pending Review'];
}

$rows = $pdo->query(
    'SELECT rt.returnID,
            rt.public_id AS return_public_id,
            rt.date_created,
            rt.updated_at,
            rt.return_method,
            rt.return_progress,
            ri.return_itemID,
            ri.return_status,
            ri.reason,
            ri.notes,
            s.public_id AS sale_public_id,
            c.public_id AS customer_public_id,
            p.public_id AS product_public_id,
            p.product_name,
            si.price_at_sale
     FROM return_transaction rt
     JOIN return_item ri ON ri.returnID = rt.returnID
     JOIN sale_item si ON si.sale_itemID = ri.sale_itemID
     JOIN sale s ON s.saleID = rt.saleID
     JOIN customer c ON c.customerID = s.customerID
     JOIN product p ON p.productID = si.productID
     WHERE rt.public_id IS NOT NULL AND rt.public_id <> ""
     ORDER BY rt.returnID DESC, ri.return_itemID DESC'
)->fetchAll();

$processed = 0;
$seen = [];
foreach ($rows as $row) {
    $returnPublicId = asString($row['return_public_id'] ?? '');
    if ($returnPublicId === '' || isset($seen[$returnPublicId])) {
        continue;
    }
    $seen[$returnPublicId] = true;

    $createdAt = asString($row['date_created'] ?? '');
    $updatedAt = asString($row['updated_at'] ?? '');
    $timestamp = $updatedAt !== '' ? $updatedAt : ($createdAt !== '' ? $createdAt : nowUtc());

    $returnMethod = asString($row['return_method'] ?? '');
    $shipmentMethodLabel = $returnMethod === 'Courier' ? 'Courier Pick-up' : 'Drop-off';

    $returnStatus = asString($row['return_status'] ?? '');
    $refundMethodLabel = $returnStatus === 'Store Credit'
        ? 'Store Credit'
        : ($returnStatus === 'Replaced' ? 'Replacement' : 'Original Payment Method');

    $normalizedStatus = normalizeMongoReturnRequestStatus(
        asString($row['return_progress'] ?? ''),
        $returnStatus
    );

    $path = returnStatusPath(asString($row['return_progress'] ?? ''), $returnStatus);
    if ($path === []) {
        $path = [$normalizedStatus !== '' ? $normalizedStatus : 'Pending Review'];
    }

    $historyEntry = [
        'status' => $normalizedStatus,
        'timestamp' => $timestamp,
        'updated_by' => 'System',
        'remarks' => 'Return request synchronized from sale record.',
    ];
    $returnHistory = [];
    $baseTimestamp = $createdAt !== '' ? $createdAt : $timestamp;
    foreach ($path as $idx => $step) {
        $returnHistory[] = [
            'status' => $step,
            'timestamp' => $idx === count($path) - 1 ? $timestamp : $baseTimestamp,
            'updated_by' => 'System',
            'remarks' => 'Return request synchronized from sale record.',
        ];
    }

    $doc = [
        'return_public_id' => $returnPublicId,
        'customer_public_id' => asString($row['customer_public_id'] ?? ''),
        'order_public_id' => asString($row['sale_public_id'] ?? ''),
        'returned_item' => [
            'product_public_id' => asString($row['product_public_id'] ?? ''),
            'product_name' => asString($row['product_name'] ?? ''),
            'unit_price' => (float) ($row['price_at_sale'] ?? 0),
        ],
        'reason' => asString($row['reason'] ?? ''),
        'description' => asString($row['notes'] ?? ''),
        'evidence_photos' => [],
        'preferences' => [
            'shipment_method' => $shipmentMethodLabel,
            'refund_method' => $refundMethodLabel,
        ],
        'status' => $normalizedStatus,
        'refund_history_status' => [$historyEntry],
        'return_status_history' => $returnHistory,
        'created_at' => $createdAt !== '' ? $createdAt : $timestamp,
        'updated_at' => $timestamp,
    ];

    mongoUpdateOne($manager, $db, $collection, ['return_public_id' => $returnPublicId], ['$set' => $doc], true);
    $processed++;
}

echo "Rebuilt return requests: {$processed}\n";
