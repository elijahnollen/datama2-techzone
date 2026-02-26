<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/common.php';
require_once __DIR__ . '/../app/Models/services.php';
require_once __DIR__ . '/../app/Core/helpers.php';

$dryRun = in_array('--dry-run', $argv, true);
$env = loadEnv(__DIR__ . '/../.env');
$pdo = mysqlPdo($env);

$rows = $pdo->query(
    'SELECT
        rt.public_id AS return_public_id,
        rt.date_created,
        COALESCE(
            NULLIF(rt.refund_amount, 0),
            (
                SELECT SUM(ri.return_quantity * si.price_at_sale)
                FROM return_item ri
                JOIN sale_item si ON si.sale_itemID = ri.sale_itemID
                WHERE ri.returnID = rt.returnID
                  AND ri.return_status IN ("Refunded", "Store Credit")
            ),
            0
        ) AS refund_amount,
        CASE
            WHEN EXISTS (
                SELECT 1
                FROM return_item ri_store
                WHERE ri_store.returnID = rt.returnID
                  AND ri_store.return_status = "Store Credit"
            )
            THEN "Store Credit"
            ELSE COALESCE(
                (
                    SELECT pay.payment_method
                    FROM payment pay
                    WHERE pay.saleID = rt.saleID
                    ORDER BY pay.paymentID DESC
                    LIMIT 1
                ),
                "Cash"
            )
        END AS payment_method,
        CASE
            WHEN EXISTS (
                SELECT 1
                FROM return_item ri_store
                WHERE ri_store.returnID = rt.returnID
                  AND ri_store.return_status = "Store Credit"
            )
            THEN "Refunded"
            ELSE "Refunded"
        END AS payment_status
     FROM return_transaction rt
     WHERE rt.return_progress = "Finalized"
       AND (
           rt.refund_amount > 0
           OR EXISTS (
                SELECT 1
                FROM return_item ri
                WHERE ri.returnID = rt.returnID
                  AND ri.return_status IN ("Refunded", "Store Credit")
           )
       )
       AND NOT EXISTS (
           SELECT 1
           FROM refund_payment rp
           WHERE rp.returnID = rt.returnID
       )'
)->fetchAll();

if (!is_array($rows)) {
    $rows = [];
}

fwrite(STDOUT, '[backfill-refund] Mode: ' . ($dryRun ? 'dry-run' : 'write') . PHP_EOL);
fwrite(STDOUT, '[backfill-refund] Missing refund payments: ' . count($rows) . PHP_EOL);

if ($dryRun) {
    exit(0);
}

$normalizeMethod = static function (string $method): string {
    $value = trim($method);
    return in_array($value, ['Cash', 'GCash', 'Card', 'Store Credit'], true) ? $value : 'Cash';
};

$normalizeStatus = static function (string $status): string {
    $value = trim($status);
    if (strcasecmp($value, 'Completed') === 0) {
        return 'Refunded';
    }
    return in_array($value, ['Pending', 'Failed', 'Refunded'], true) ? $value : 'Refunded';
};

$inserted = 0;
$failed = 0;
$usedPublicIds = [];
$upsert = $pdo->prepare(
    'CALL refund_payment_upsert(
        :amount, :payment_method, :payment_status, :public_id, :return_public_id, :refund_date
    )'
);

foreach ($rows as $row) {
    if (!is_array($row)) {
        continue;
    }

    $returnPublicId = asString($row['return_public_id'] ?? '');
    $amount = (float) ($row['refund_amount'] ?? 0);
    if ($returnPublicId === '' || $amount <= 0) {
        continue;
    }

    $publicId = randomPublicId('RF');
    while (isset($usedPublicIds[$publicId])) {
        $publicId = randomPublicId('RF');
    }
    $usedPublicIds[$publicId] = true;

    $refundDate = asString($row['date_created'] ?? '');
    if ($refundDate === '') {
        $refundDate = date('Y-m-d H:i:s');
    }

    try {
        $upsert->execute([
            'amount' => $amount,
            'payment_method' => $normalizeMethod(asString($row['payment_method'] ?? 'Cash')),
            'payment_status' => $normalizeStatus(asString($row['payment_status'] ?? 'Refunded')),
            'public_id' => $publicId,
            'return_public_id' => $returnPublicId,
            'refund_date' => $refundDate,
        ]);
        $upsert->closeCursor();
        $inserted += 1;
    } catch (Throwable $error) {
        $failed += 1;
        $message = $error->getMessage();
        fwrite(STDERR, '[backfill-refund] Failed for ' . $returnPublicId . ': ' . $message . PHP_EOL);
        try {
            $upsert->closeCursor();
        } catch (Throwable) {
            // ignore
        }
    }
}

fwrite(STDOUT, '[backfill-refund] Inserted/updated refund payments: ' . $inserted . PHP_EOL);
fwrite(STDOUT, '[backfill-refund] Failed rows: ' . $failed . PHP_EOL);

exit(0);
