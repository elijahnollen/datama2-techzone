<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/common.php';
require_once __DIR__ . '/../app/Models/services.php';
require_once __DIR__ . '/../app/Core/helpers.php';

$env = loadEnv(__DIR__ . '/../.env');
$manager = mongoManager($env);

if (!$manager) {
    fwrite(STDERR, "[backfill] MongoDB manager unavailable.\n");
    exit(1);
}

$db = mongoDbName($env);
$orderCollection = mongoCollectionName($env, 'orders');
$returnCollection = mongoCollectionName($env, 'return_request');
$dryRun = in_array('--dry-run', $argv, true);

/**
 * @param array<int,array<string,mixed>> $history
 */
function latestUpdatedBy(array $history): string
{
    if ($history === []) {
        return 'System';
    }
    $last = $history[count($history) - 1] ?? null;
    if (!is_array($last)) {
        return 'System';
    }
    $updatedBy = asString($last['updated_by'] ?? '');
    return $updatedBy !== '' ? $updatedBy : 'System';
}

function bestTimestamp(array $doc): string
{
    $updatedAt = asString($doc['updated_at'] ?? '');
    if ($updatedAt !== '') {
        return $updatedAt;
    }
    $createdAt = asString($doc['created_at'] ?? '');
    if ($createdAt !== '') {
        return $createdAt;
    }
    return nowUtc();
}

/**
 * @return array<int,mixed>
 */
function normalizeHistoryValue(mixed $value): array
{
    if (is_array($value)) {
        return array_values($value);
    }
    if ($value instanceof Traversable) {
        return array_values(iterator_to_array($value));
    }
    if (is_object($value)) {
        $decoded = json_decode(json_encode($value, JSON_UNESCAPED_UNICODE), true);
        if (is_array($decoded)) {
            return array_values($decoded);
        }
    }
    return [];
}

$ordersScanned = 0;
$ordersUpdated = 0;
$returnsScanned = 0;
$returnsUpdated = 0;

try {
        $orderDocs = mongoFindMany(
            $manager,
            $db,
            $orderCollection,
            [],
        ['projection' => ['_id' => 1, 'order_public_id' => 1, 'payment' => 1, 'status_history' => 1, 'payment_history_status' => 1, 'created_at' => 1, 'updated_at' => 1]]
        );

    foreach ($orderDocs as $orderDoc) {
        if (!is_array($orderDoc)) {
            continue;
        }
        $ordersScanned++;

        $paymentHistory = normalizeHistoryValue($orderDoc['payment_history_status'] ?? null);
        if ($paymentHistory !== []) {
            continue;
        }
        if (!array_key_exists('_id', $orderDoc)) {
            continue;
        }
        $orderId = mongoObjectIdFromValue($orderDoc['_id']);
        if ($orderId === null) {
            continue;
        }

        $payment = is_array($orderDoc['payment'] ?? null) ? (array) $orderDoc['payment'] : [];
        $paymentStatus = normalizeOrderPaymentStatus(asString($payment['payment_status'] ?? ''));
        $history = normalizeHistoryValue($orderDoc['status_history'] ?? null);
        $entry = [
            'status' => $paymentStatus,
            'timestamp' => bestTimestamp($orderDoc),
            'updated_by' => latestUpdatedBy($history),
            'remarks' => 'Backfilled from current payment status.',
        ];

        if ($dryRun) {
            $ordersUpdated++;
            continue;
        }

        mongoUpdateOne(
            $manager,
            $db,
            $orderCollection,
            ['_id' => $orderId],
            ['$set' => ['payment_history_status' => [$entry]]]
        );
        $ordersUpdated++;
    }
} catch (Throwable $error) {
    fwrite(STDERR, '[backfill] Order backfill failed: ' . $error->getMessage() . "\n");
    exit(1);
}

try {
    $returnDocs = mongoFindMany(
        $manager,
        $db,
        $returnCollection,
        [],
        ['projection' => ['_id' => 1, 'status' => 1, 'created_at' => 1, 'updated_at' => 1, 'refund_history_status' => 1, 'return_status_history' => 1]]
    );

    foreach ($returnDocs as $returnDoc) {
        if (!is_array($returnDoc)) {
            continue;
        }
        $returnsScanned++;

        $refundHistory = normalizeHistoryValue($returnDoc['refund_history_status'] ?? null);
        $returnHistory = normalizeHistoryValue($returnDoc['return_status_history'] ?? null);
        if ($refundHistory !== [] && $returnHistory !== []) {
            continue;
        }
        if (!array_key_exists('_id', $returnDoc)) {
            continue;
        }
        $returnId = mongoObjectIdFromValue($returnDoc['_id']);
        if ($returnId === null) {
            continue;
        }

        $status = asString($returnDoc['status'] ?? '');
        $entry = [
            'status' => $status !== '' ? $status : 'Pending Review',
            'timestamp' => bestTimestamp($returnDoc),
            'updated_by' => 'System',
            'remarks' => 'Backfilled from current return request status.',
        ];

        if ($dryRun) {
            $returnsUpdated++;
            continue;
        }

        $set = [];
        if ($refundHistory === []) {
            $set['refund_history_status'] = [$entry];
        }
        if ($returnHistory === []) {
            $set['return_status_history'] = [$entry];
        }
        if ($set !== []) {
            mongoUpdateOne(
                $manager,
                $db,
                $returnCollection,
                ['_id' => $returnId],
                ['$set' => $set]
            );
        }
        $returnsUpdated++;
    }
} catch (Throwable $error) {
    fwrite(STDERR, '[backfill] Return backfill failed: ' . $error->getMessage() . "\n");
    exit(1);
}

fwrite(STDOUT, '[backfill] Mode: ' . ($dryRun ? 'dry-run' : 'write') . "\n");
fwrite(STDOUT, '[backfill] Orders scanned: ' . $ordersScanned . ', updated: ' . $ordersUpdated . "\n");
fwrite(STDOUT, '[backfill] Return requests scanned: ' . $returnsScanned . ', updated: ' . $returnsUpdated . "\n");

exit(0);
