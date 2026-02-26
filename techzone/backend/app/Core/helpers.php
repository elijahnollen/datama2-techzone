<?php

declare(strict_types=1);

function tokenSecret(array $env): string
{
    $secret = (string) envValue($env, 'APP_SECRET', 'techzone-dev-secret-change-me');
    if (strlen($secret) >= 32) {
        return $secret;
    }
    return hash('sha256', $secret . '|techzone|fallback');
}

function requestBody(array $env): array
{
    $maxBytes = (int) envValue($env, 'MAX_JSON_BYTES', '1048576');
    if ($maxBytes < 1024) {
        $maxBytes = 1024;
    }
    return requestJson($maxBytes);
}

function enforceAuthRateLimit(array $env, string $scope, string $identifier): void
{
    $maxAttempts = (int) envValue($env, 'AUTH_RATE_LIMIT_MAX_ATTEMPTS', '12');
    $windowSeconds = (int) envValue($env, 'AUTH_RATE_LIMIT_WINDOW_SECONDS', '600');
    if ($maxAttempts < 3) {
        $maxAttempts = 3;
    }
    if ($windowSeconds < 60) {
        $windowSeconds = 60;
    }

    $subject = clientIp() . '|' . strtolower(trim($identifier));
    if (isRateLimited($scope, $subject, $maxAttempts, $windowSeconds)) {
        sendJson(429, [
            'ok' => false,
            'message' => 'Too many attempts. Please try again later.',
            'errors' => [
                'auth' => 'Too many login attempts were detected. Please wait and try again.',
            ],
        ]);
    }
}

function requireAuth(array $env, array $roles = []): array
{
    $token = bearerToken();
    $payload = parseToken($token, tokenSecret($env));
    if ($payload === null) {
        sendJson(401, [
            'ok' => false,
            'message' => 'Authentication required.',
            'errors' => ['auth' => 'Please log in and try again.'],
        ]);
    }

    if ($roles !== [] && !in_array((string) ($payload['role'] ?? ''), $roles, true)) {
        sendJson(403, [
            'ok' => false,
            'message' => 'Access denied.',
            'errors' => ['auth' => 'Your account does not have permission for this action.'],
        ]);
    }

    return $payload;
}

function validationFail(array $errors): never
{
    sendJson(422, [
        'ok' => false,
        'message' => 'Please correct the highlighted fields.',
        'errors' => $errors,
    ]);
}

function endpointNotFound(): never
{
    sendJson(404, [
        'ok' => false,
        'message' => 'Endpoint not found.',
    ]);
}

function queryParam(string $key, ?string $default = null): ?string
{
    $value = $_GET[$key] ?? $default;
    return is_string($value) ? $value : $default;
}

function normalizeSaleStatusForOutput(string $status, string $fulfillmentMethod): string
{
    $normalized = trim($status);
    return $normalized !== '' ? $normalized : 'Pending';
}

function orderStatusMessage(
    string $status,
    string $trackingNumber = '',
    string $courierName = '',
    string $fulfillmentMethod = 'Delivery'
): string {
    $normalized = strtolower(trim($status));

    if ($normalized === 'processing') {
        return 'We are packing your items.';
    }
    if ($normalized === 'shipped') {
        $tracking = trim($trackingNumber);
        if ($tracking !== '') {
            return 'Your item is on the way! Tracking: ' . $tracking;
        }
        return 'Your item is on the way!';
    }
    if ($normalized === 'ready for pickup') {
        return 'Please visit our store to collect your order.';
    }
    if ($normalized === 'pending') {
        return 'Order received and waiting for confirmation.';
    }
    if ($normalized === 'delivered') {
        return 'Your order has been delivered.';
    }
    if ($normalized === 'completed') {
        if (strcasecmp($fulfillmentMethod, 'Walk-in') === 0) {
            return 'Walk-in transaction recorded as completed.';
        }
        return 'Order completed. Thank you for shopping with us.';
    }
    if ($normalized === 'cancelled') {
        return 'This order was cancelled.';
    }

    return '';
}

function mongoDateToString(mixed $value, string $fallback = ''): string
{
    if (is_string($value) && trim($value) !== '') {
        return trim($value);
    }
    if (is_array($value)) {
        $date = asString($value['$date'] ?? '');
        if ($date !== '') {
            return $date;
        }
        $numberLong = asString($value['$numberLong'] ?? '');
        if ($numberLong !== '' && ctype_digit($numberLong)) {
            $seconds = (int) floor(((float) $numberLong) / 1000);
            return gmdate('Y-m-d H:i:s', $seconds);
        }
    }
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d H:i:s');
    }
    if (class_exists('MongoDB\\BSON\\UTCDateTime') && $value instanceof MongoDB\BSON\UTCDateTime) {
        return $value->toDateTime()->format('Y-m-d H:i:s');
    }
    return $fallback;
}

function orderTimelinePath(string $currentStatus, string $fulfillmentMethod): array
{
    if (strcasecmp($currentStatus, 'Cancelled') === 0) {
        if (strcasecmp($fulfillmentMethod, 'Pickup') === 0) {
            return ['Pending', 'Processing', 'Ready for Pickup', 'Cancelled'];
        }
        if (strcasecmp($fulfillmentMethod, 'Walk-in') === 0) {
            return ['Cancelled'];
        }
        return ['Pending', 'Processing', 'Cancelled'];
    }

    if (strcasecmp($fulfillmentMethod, 'Pickup') === 0) {
        $path = ['Pending', 'Processing', 'Ready for Pickup', 'Completed'];
    } elseif (strcasecmp($fulfillmentMethod, 'Walk-in') === 0) {
        $path = ['Completed'];
    } else {
        $path = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Completed'];
    }

    $normalizedCurrent = trim($currentStatus);
    if ($normalizedCurrent === '') {
        return $path;
    }

    foreach ($path as $step) {
        if (strcasecmp($step, $normalizedCurrent) === 0) {
            return $path;
        }
    }

    $path[] = $normalizedCurrent;
    return $path;
}

function buildOrderTimeline(
    ?array $orderDoc,
    string $currentStatus,
    string $saleDate,
    string $updatedAt,
    string $trackingNumber,
    string $courierName,
    string $fulfillmentMethod
): array {
    $timeline = [];
    $history = is_array($orderDoc['status_history'] ?? null) ? $orderDoc['status_history'] : [];
    $path = orderTimelinePath($currentStatus, $fulfillmentMethod);
    $currentIndex = count($path) - 1;
    foreach ($path as $idx => $step) {
        if (strcasecmp($step, $currentStatus) === 0) {
            $currentIndex = $idx;
            break;
        }
    }

    foreach ($history as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $status = asString($entry['status'] ?? '');
        if ($status === '') {
            continue;
        }
        $date = mongoDateToString($entry['timestamp'] ?? $entry['updated_at'] ?? '', '');
        $remarks = asString($entry['remarks'] ?? '');
        if (stripos($remarks, 'order synchronized from sale record') !== false) {
            $remarks = '';
        }
        $timeline[] = [
            'status' => $status,
            'date' => $date,
            'completed' => true,
            'message' => $remarks !== '' ? $remarks : orderStatusMessage($status, $trackingNumber, $courierName, $fulfillmentMethod),
        ];
    }

    if ($timeline !== []) {
        $historyQueue = $timeline;
        $timeline = [];
        foreach ($path as $idx => $step) {
            $matchedHistoryIndex = null;
            foreach ($historyQueue as $historyIndex => $historyEvent) {
                if (strcasecmp(asString($historyEvent['status'] ?? ''), $step) === 0) {
                    $matchedHistoryIndex = $historyIndex;
                    break;
                }
            }

            if ($matchedHistoryIndex !== null) {
                $event = $historyQueue[$matchedHistoryIndex];
                if (asString($event['date'] ?? '') === '' && $idx <= $currentIndex) {
                    if ($idx === 0) {
                        $event['date'] = $saleDate;
                    } elseif (strcasecmp($step, $currentStatus) === 0) {
                        $event['date'] = $updatedAt !== '' ? $updatedAt : $saleDate;
                    }
                }
                $timeline[] = $event;
                unset($historyQueue[$matchedHistoryIndex]);
                continue;
            }

            $completed = $idx <= $currentIndex;
            if ($completed) {
                $syntheticDate = '';
                if ($idx === 0) {
                    $syntheticDate = $saleDate;
                } elseif (strcasecmp($step, $currentStatus) === 0) {
                    $syntheticDate = $updatedAt !== '' ? $updatedAt : $saleDate;
                }
                $timeline[] = [
                    'status' => $step,
                    'date' => $syntheticDate,
                    'completed' => true,
                    'message' => orderStatusMessage($step, $trackingNumber, $courierName, $fulfillmentMethod),
                ];
                continue;
            }

            $timeline[] = [
                'status' => $step,
                'date' => '',
                'completed' => false,
                'message' => '',
            ];
        }

        foreach ($historyQueue as $historyEvent) {
            $status = asString($historyEvent['status'] ?? '');
            if ($status === '') {
                continue;
            }
            $alreadyInTimeline = false;
            foreach ($timeline as $timelineEvent) {
                if (strcasecmp(asString($timelineEvent['status'] ?? ''), $status) === 0) {
                    $alreadyInTimeline = true;
                    break;
                }
            }
            if ($alreadyInTimeline) {
                continue;
            }
            $timeline[] = $historyEvent;
        }

        $hasCurrentStatus = false;
        foreach ($timeline as $timelineEvent) {
            if (strcasecmp(asString($timelineEvent['status'] ?? ''), $currentStatus) === 0) {
                $hasCurrentStatus = true;
                break;
            }
        }
        if (!$hasCurrentStatus) {
            $timeline[] = [
                'status' => $currentStatus,
                'date' => $updatedAt !== '' ? $updatedAt : $saleDate,
                'completed' => true,
                'message' => orderStatusMessage($currentStatus, $trackingNumber, $courierName, $fulfillmentMethod),
            ];
        }

        return array_values($timeline);
    }

    if (strcasecmp($currentStatus, 'Cancelled') === 0) {
        return [[
            'status' => $currentStatus,
            'date' => $updatedAt !== '' ? $updatedAt : $saleDate,
            'completed' => true,
            'message' => orderStatusMessage($currentStatus, $trackingNumber, $courierName, $fulfillmentMethod),
        ]];
    }

    $events = [];
    foreach ($path as $idx => $step) {
        $completed = $idx <= $currentIndex;
        $date = '';
        if ($completed) {
            if ($idx === 0) {
                $date = $saleDate;
            } elseif (strcasecmp($step, $currentStatus) === 0) {
                $date = $updatedAt !== '' ? $updatedAt : $saleDate;
            }
        }
        $events[] = [
            'status' => $step,
            'date' => $date,
            'completed' => $completed,
            'message' => $completed ? orderStatusMessage($step, $trackingNumber, $courierName, $fulfillmentMethod) : '',
        ];
    }
    return $events;
}

function mongoTimestamp(): mixed
{
    return nowUtc();
}

function normalizeOrderPaymentStatus(string $status): string
{
    $normalized = strtolower(trim($status));
    if ($normalized === '' || $normalized === 'completed') {
        return 'Paid';
    }
    if ($normalized === 'pending') {
        return 'Pending';
    }
    if ($normalized === 'failed') {
        return 'Failed';
    }
    return ucfirst($normalized);
}

function cancelledSaleInventoryTransactionType(PDO $pdo): string
{
    static $cached = null;
    if (is_string($cached) && $cached !== '') {
        return $cached;
    }

    try {
        $column = $pdo->query("SHOW COLUMNS FROM inventory_transaction LIKE 'transaction_type'")->fetch();
        $typeDefinition = strtolower((string) ($column['Type'] ?? $column['type'] ?? ''));
        if ($typeDefinition !== '' && strpos($typeDefinition, "'cancelled sale'") !== false) {
            $cached = 'Cancelled Sale';
            return $cached;
        }
    } catch (Throwable) {
        // fallback below
    }

    $cached = 'Restock';
    return $cached;
}

function ensureStoreCreditPurchaseEntry(
    PDO $pdo,
    string $customerPublicId,
    string $salePublicId,
    float $amount
): void {
    if ($customerPublicId === '' || $salePublicId === '' || $amount <= 0) {
        return;
    }

    $creditStmt = $pdo->prepare(
        'CALL spend_credit(:customer_public_id, :amount, :sale_public_id)'
    );
    $creditStmt->execute([
        'customer_public_id' => $customerPublicId,
        'amount' => $amount,
        'sale_public_id' => $salePublicId,
    ]);
    $creditStmt->closeCursor();
}

function upsertMongoOrderSnapshot(
    array $env,
    string $orderPublicId,
    string $customerPublicId,
    array $items,
    array $payment,
    array $shippingAddress,
    string $orderStatus,
    string $statusUpdatedBy,
    string $statusRemarks,
    ?string $trackingNumber = null,
    ?string $courierName = null,
    ?string $createdAtOverride = null,
    ?string $updatedAtOverride = null
): void {
    $manager = mongoManager($env);
    if (!$manager || $orderPublicId === '') {
        return;
    }

    $timestamp = mongoTimestamp();
    $createdTimestamp = asString($createdAtOverride ?? '') !== '' ? asString($createdAtOverride ?? '') : $timestamp;
    $updatedTimestamp = asString($updatedAtOverride ?? '') !== '' ? asString($updatedAtOverride ?? '') : $timestamp;
    $historyTimestamp = $updatedTimestamp !== '' ? $updatedTimestamp : $createdTimestamp;
    $normalizedStatus = $orderStatus !== '' ? $orderStatus : 'Pending';
    $normalizedPaymentStatus = normalizeOrderPaymentStatus(asString($payment['payment_status'] ?? 'Paid'));
    $historyEntry = [
        'status' => $normalizedStatus,
        'timestamp' => $historyTimestamp,
        'updated_by' => $statusUpdatedBy !== '' ? $statusUpdatedBy : 'System',
        'remarks' => $statusRemarks !== '' ? $statusRemarks : 'Order state updated.',
    ];
    $paymentHistoryEntry = [
        'status' => $normalizedPaymentStatus,
        'timestamp' => $historyTimestamp,
        'updated_by' => $statusUpdatedBy !== '' ? $statusUpdatedBy : 'System',
        'remarks' => asString($payment['payment_history_remarks'] ?? '') !== ''
            ? asString($payment['payment_history_remarks'] ?? '')
            : 'Payment state recorded.',
    ];

    $logisticsCarrier = $courierName !== null && trim($courierName) !== '' ? trim($courierName) : null;
    $logisticsTrackingNumber = $trackingNumber !== null && trim($trackingNumber) !== '' ? trim($trackingNumber) : null;
    $existingOrderDoc = null;
    try {
        $existingOrderDoc = mongoFindOne(
            $manager,
            mongoDbName($env),
            mongoCollectionName($env, 'orders'),
            ['order_public_id' => $orderPublicId]
        );
    } catch (Throwable) {
        $existingOrderDoc = null;
    }

    $existingPaymentStatus = '';
    $existingOrderStatus = '';
    if (is_array($existingOrderDoc) && is_array($existingOrderDoc['payment'] ?? null)) {
        $existingPaymentStatus = normalizeOrderPaymentStatus(asString($existingOrderDoc['payment']['payment_status'] ?? ''));
    }
    if (is_array($existingOrderDoc)) {
        $existingOrderStatus = asString($existingOrderDoc['order_status'] ?? '');
    }
    $existingPaymentHistory = is_array($existingOrderDoc['payment_history_status'] ?? null)
        ? (array) $existingOrderDoc['payment_history_status']
        : [];
    $shouldPushPaymentHistory = is_array($existingOrderDoc)
        && ($existingPaymentStatus !== $normalizedPaymentStatus || $existingPaymentHistory === []);
    $existingStatusHistory = is_array($existingOrderDoc['status_history'] ?? null)
        ? (array) $existingOrderDoc['status_history']
        : [];
    $shouldPushStatusHistory = is_array($existingOrderDoc)
        && (
            $existingOrderStatus === ''
            || strcasecmp($existingOrderStatus, $normalizedStatus) !== 0
            || $existingStatusHistory === []
        );

    $set = [
        'order_public_id' => $orderPublicId,
        'customer_public_id' => $customerPublicId,
        'items' => $items,
        'payment' => [
            'subtotal' => round((float) ($payment['subtotal'] ?? 0), 2),
            'grand_total' => round((float) ($payment['grand_total'] ?? 0), 2),
            'payment_method' => asString($payment['payment_method'] ?? ''),
            'payment_status' => $normalizedPaymentStatus,
            'transaction_reference' => asString($payment['transaction_reference'] ?? ''),
        ],
        'shipping_address' => [
            'full_name' => asString($shippingAddress['full_name'] ?? ''),
            'phone' => asString($shippingAddress['phone'] ?? ''),
            'email' => asString($shippingAddress['email'] ?? ''),
            'street_address' => asString($shippingAddress['street_address'] ?? ''),
            'barangay' => asString($shippingAddress['barangay'] ?? ''),
            'city_municipality' => asString($shippingAddress['city_municipality'] ?? ''),
            'province' => asString($shippingAddress['province'] ?? ''),
            'zip_code' => asString($shippingAddress['zip_code'] ?? ''),
        ],
        'logistics' => [
            'carrier' => $logisticsCarrier,
            'tracking_number' => $logisticsTrackingNumber,
        ],
        'order_status' => $normalizedStatus,
        'updated_at' => $updatedTimestamp,
    ];

    $setOnInsert = [
        'created_at' => $createdTimestamp,
        'status_history' => [$historyEntry],
        'payment_history_status' => [$paymentHistoryEntry],
    ];

    try {
        $updatePayload = [
            '$set' => $set,
            '$setOnInsert' => $setOnInsert,
        ];
        $push = [];
        if ($shouldPushStatusHistory) {
            $push['status_history'] = $historyEntry;
        }
        if ($shouldPushPaymentHistory) {
            $push['payment_history_status'] = $paymentHistoryEntry;
        }
        if ($push !== []) {
            $updatePayload['$push'] = $push;
        }

        mongoUpdateOne(
            $manager,
            mongoDbName($env),
            mongoCollectionName($env, 'orders'),
            ['order_public_id' => $orderPublicId],
            $updatePayload,
            true
        );
    } catch (Throwable) {
        // noop
    }
}

function upsertMongoOrderPaymentStatus(
    array $env,
    string $orderPublicId,
    string $customerPublicId,
    string $paymentStatus,
    string $updatedBy,
    string $remarks,
    ?string $paymentMethod = null,
    ?string $transactionReference = null
): void {
    $manager = mongoManager($env);
    if (!$manager || $orderPublicId === '') {
        return;
    }

    $timestamp = mongoTimestamp();
    $normalizedPaymentStatus = normalizeOrderPaymentStatus($paymentStatus);

    $existingOrderDoc = null;
    try {
        $existingOrderDoc = mongoFindOne(
            $manager,
            mongoDbName($env),
            mongoCollectionName($env, 'orders'),
            ['order_public_id' => $orderPublicId]
        );
    } catch (Throwable) {
        $existingOrderDoc = null;
    }

    $existingPayment = is_array($existingOrderDoc['payment'] ?? null) ? (array) $existingOrderDoc['payment'] : [];
    $existingPaymentStatus = normalizeOrderPaymentStatus(asString($existingPayment['payment_status'] ?? ''));
    $existingPaymentHistory = is_array($existingOrderDoc['payment_history_status'] ?? null)
        ? (array) $existingOrderDoc['payment_history_status']
        : [];

    $resolvedPaymentMethod = $paymentMethod !== null
        ? asString($paymentMethod)
        : asString($existingPayment['payment_method'] ?? '');
    $resolvedTransactionReference = $transactionReference !== null
        ? asString($transactionReference)
        : asString($existingPayment['transaction_reference'] ?? '');

    $historyEntry = [
        'status' => $normalizedPaymentStatus,
        'timestamp' => $timestamp,
        'updated_by' => $updatedBy !== '' ? $updatedBy : 'System',
        'remarks' => $remarks !== '' ? $remarks : 'Payment state updated.',
    ];

    $set = [
        'updated_at' => $timestamp,
        'payment.payment_status' => $normalizedPaymentStatus,
    ];
    if ($customerPublicId !== '') {
        $set['customer_public_id'] = $customerPublicId;
    }
    if ($paymentMethod !== null) {
        $set['payment.payment_method'] = $resolvedPaymentMethod;
    }
    if ($transactionReference !== null) {
        $set['payment.transaction_reference'] = $resolvedTransactionReference;
    }

    $setOnInsert = [
        'order_public_id' => $orderPublicId,
        'customer_public_id' => $customerPublicId,
        'created_at' => $timestamp,
        'payment' => [
            'subtotal' => 0,
            'grand_total' => 0,
            'payment_method' => $resolvedPaymentMethod,
            'payment_status' => $normalizedPaymentStatus,
            'transaction_reference' => $resolvedTransactionReference,
        ],
        'payment_history_status' => [$historyEntry],
    ];

    $shouldPushPaymentHistory = is_array($existingOrderDoc)
        && ($existingPaymentStatus !== $normalizedPaymentStatus || $existingPaymentHistory === []);

    try {
        $updatePayload = [
            '$set' => $set,
            '$setOnInsert' => $setOnInsert,
        ];
        if ($shouldPushPaymentHistory) {
            $updatePayload['$push'] = ['payment_history_status' => $historyEntry];
        }

        mongoUpdateOne(
            $manager,
            mongoDbName($env),
            mongoCollectionName($env, 'orders'),
            ['order_public_id' => $orderPublicId],
            $updatePayload,
            true
        );
    } catch (Throwable) {
        // noop
    }
}

function upsertMongoOrderStatus(
    array $env,
    string $orderPublicId,
    string $customerPublicId,
    string $status,
    string $updatedBy,
    string $remarks,
    ?string $trackingNumber = null,
    ?string $courierName = null,
    bool $clearLogistics = false,
    ?string $timestampOverride = null
): void {
    $orderPublicId = strtoupper(trim($orderPublicId));
    $manager = mongoManager($env);
    if (!$manager || $orderPublicId === '') {
        return;
    }

    $timestamp = asString($timestampOverride ?? '') !== '' ? asString($timestampOverride ?? '') : mongoTimestamp();
    $set = [
        'order_status' => $status,
        'updated_at' => $timestamp,
    ];
    if ($customerPublicId !== '') {
        $set['customer_public_id'] = $customerPublicId;
    }
    if ($clearLogistics) {
        $set['logistics.tracking_number'] = null;
        $set['logistics.carrier'] = null;
    } else {
        if ($trackingNumber !== null) {
            $set['logistics.tracking_number'] = $trackingNumber !== '' ? $trackingNumber : null;
        }
        if ($courierName !== null) {
            $set['logistics.carrier'] = $courierName !== '' ? $courierName : null;
        }
    }

    $setOnInsert = [
        'order_public_id' => $orderPublicId,
        'customer_public_id' => $customerPublicId,
        'created_at' => $timestamp,
    ];

    $resolvedUpdatedBy = trim($updatedBy);
    if ($resolvedUpdatedBy === '') {
        $resolvedUpdatedBy = 'System';
    }
    $history = [
        'status' => $status,
        'timestamp' => $timestamp,
        'updated_by' => $resolvedUpdatedBy,
        'remarks' => $remarks,
    ];

    try {
        mongoUpdateOne(
            $manager,
            mongoDbName($env),
            mongoCollectionName($env, 'orders'),
            ['order_public_id' => $orderPublicId],
            [
                '$set' => $set,
                '$setOnInsert' => $setOnInsert,
                '$push' => ['status_history' => $history],
            ],
            true
        );
        return;
    } catch (Throwable) {
        // Retry without logistics fields in case of strict schema validation.
        $retrySet = $set;
        unset($retrySet['logistics.tracking_number'], $retrySet['logistics.carrier']);
        try {
            mongoUpdateOne(
                $manager,
                mongoDbName($env),
                mongoCollectionName($env, 'orders'),
                ['order_public_id' => $orderPublicId],
                [
                    '$set' => $retrySet,
                    '$setOnInsert' => $setOnInsert,
                    '$push' => ['status_history' => $history],
                ],
                true
            );
            return;
        } catch (Throwable) {
            // Attempt to rebuild the order snapshot before retrying.
            rebuildMongoOrderSnapshotFromMysql($env, $orderPublicId);
            try {
                mongoUpdateOne(
                    $manager,
                    mongoDbName($env),
                    mongoCollectionName($env, 'orders'),
                    ['order_public_id' => $orderPublicId],
                    [
                        '$set' => $retrySet,
                        '$setOnInsert' => $setOnInsert,
                        '$push' => ['status_history' => $history],
                    ],
                    true
                );
            } catch (Throwable) {
                // noop
            }
        }
    }
}

function rebuildMongoOrderSnapshotFromMysql(array $env, string $orderPublicId): void
{
    $orderPublicId = strtoupper(trim($orderPublicId));
    if ($orderPublicId === '') {
        return;
    }

    $manager = mongoManager($env);
    if (!$manager) {
        return;
    }

    $pdo = mysqlPdo($env);
    $db = mongoDbName($env);
    $collection = mongoCollectionName($env, 'orders');

    $saleStmt = $pdo->prepare(
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
         WHERE s.public_id = :public_id
         LIMIT 1'
    );
    $saleStmt->execute(['public_id' => $orderPublicId]);
    $row = $saleStmt->fetch();
    if (!is_array($row)) {
        return;
    }

    $saleId = (int) ($row['saleID'] ?? 0);
    if ($saleId <= 0) {
        return;
    }

    $catalog = loadProductCatalog($env);
    $catalogByPublicId = catalogMapByPublicId($catalog);

    $items = [];
    $itemsStmt = $pdo->prepare(
        'SELECT si.quantity_sold,
                si.price_at_sale,
                p.public_id AS product_public_id,
                p.product_name
         FROM sale_item si
         JOIN product p ON p.productID = si.productID
         WHERE si.saleID = :sale_id'
    );
    $itemsStmt->execute(['sale_id' => $saleId]);
    foreach ($itemsStmt->fetchAll() as $itemRow) {
        $productPublicId = asString($itemRow['product_public_id'] ?? '');
        $catalogItem = $catalogByPublicId[$productPublicId] ?? [];
        $items[] = [
            'product_public_id' => $productPublicId,
            'product_name' => asString($itemRow['product_name'] ?? ''),
            'quantity' => (int) ($itemRow['quantity_sold'] ?? 0),
            'price_at_sale' => (float) ($itemRow['price_at_sale'] ?? 0),
            'image_url' => asString($catalogItem['image_url'] ?? ''),
        ];
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
}

function mongoOrderSyncStatePath(): string
{
    $root = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'techzone_mongo_sync';
    if (!is_dir($root)) {
        @mkdir($root, 0777, true);
    }
    return $root . DIRECTORY_SEPARATOR . 'orders.json';
}

function readMongoOrderSyncState(): array
{
    $path = mongoOrderSyncStatePath();
    if (!is_file($path)) {
        return ['last_run' => 0, 'last_updated_at' => ''];
    }
    $raw = file_get_contents($path);
    if (!is_string($raw) || trim($raw) === '') {
        return ['last_run' => 0, 'last_updated_at' => ''];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return ['last_run' => 0, 'last_updated_at' => ''];
    }
    return [
        'last_run' => (int) ($decoded['last_run'] ?? 0),
        'last_updated_at' => asString($decoded['last_updated_at'] ?? ''),
    ];
}

function writeMongoOrderSyncState(int $lastRun, string $lastUpdatedAt): void
{
    $payload = [
        'last_run' => $lastRun,
        'last_updated_at' => $lastUpdatedAt,
    ];
    @file_put_contents(mongoOrderSyncStatePath(), json_encode($payload));
}

function syncMongoOrderHistoryFromMysql(array $env, PDO $pdo, ?string $since = null): string
{
    $manager = mongoManager($env);
    if (!$manager) {
        return $since ?? '';
    }

    $activityExpr = 'GREATEST('
        . 'COALESCE(s.updated_at, s.sale_date, "1970-01-01 00:00:00"), '
        . 'COALESCE(pay.updated_at, pay.payment_date, s.sale_date, "1970-01-01 00:00:00")'
        . ')';
    $sql = 'SELECT
                s.public_id,
                s.sale_date,
                s.sale_status,
                s.fulfillment_method,
                s.tracking_number,
                s.courier_name,
                ' . $activityExpr . ' AS activity_at,
                c.public_id AS customer_public_id,
                pay.payment_method,
                pay.payment_status,
                pay.public_id AS payment_public_id
            FROM api_sale s
            JOIN api_customer c ON c.customerID = s.customerID
            LEFT JOIN (
                SELECT p.saleID, p.payment_method, p.payment_status, p.public_id, p.updated_at, p.payment_date
                FROM api_payment p
                JOIN (
                    SELECT saleID, MAX(paymentID) AS latest_payment_id
                    FROM api_payment
                    WHERE saleID IS NOT NULL
                    GROUP BY saleID
                ) latest ON latest.latest_payment_id = p.paymentID
            ) pay ON pay.saleID = s.saleID
            WHERE s.public_id IS NOT NULL AND s.public_id <> ""';
    $params = [];
    $sinceValue = asString($since ?? '');
    if ($sinceValue !== '') {
        $sql .= ' AND ' . $activityExpr . ' >= :since';
        $params['since'] = $sinceValue;
    }
    $sql .= ' ORDER BY ' . $activityExpr . ' ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $latestActivity = $sinceValue;
    $db = mongoDbName($env);
    $collection = mongoCollectionName($env, 'orders');

    foreach ($rows as $row) {
        $orderPublicId = asString($row['public_id'] ?? '');
        if ($orderPublicId === '' || !isValidPublicId($orderPublicId)) {
            continue;
        }

        $activityAt = asString($row['activity_at'] ?? '');
        $saleDate = asString($row['sale_date'] ?? '');
        if ($activityAt !== '' && ($latestActivity === '' || strcmp($activityAt, $latestActivity) > 0)) {
            $latestActivity = $activityAt;
        }

        $customerPublicId = asString($row['customer_public_id'] ?? '');
        $rawStatus = asString($row['sale_status'] ?? '');
        $fulfillmentMethod = asString($row['fulfillment_method'] ?? '');
        $normalizedStatus = normalizeSaleStatusForOutput($rawStatus, $fulfillmentMethod);
        $trackingNumber = asString($row['tracking_number'] ?? '');
        $courierName = asString($row['courier_name'] ?? '');
        $paymentMethod = asString($row['payment_method'] ?? '');
        $paymentStatus = asString($row['payment_status'] ?? '');
        $paymentPublicId = asString($row['payment_public_id'] ?? '');

        $isCashOnDelivery = in_array(strtolower(trim($paymentMethod)), ['cash', 'cash on delivery', 'cod'], true);
        if (
            $isCashOnDelivery
            && in_array($normalizedStatus, ['Delivered', 'Completed'], true)
            && strcasecmp($paymentStatus, 'Completed') !== 0
        ) {
            $paymentStatus = 'Completed';
        }

        $orderDoc = null;
        try {
            $orderDoc = mongoFindOne($manager, $db, $collection, ['order_public_id' => $orderPublicId]);
        } catch (Throwable) {
            $orderDoc = null;
        }

        $existingOrderStatus = is_array($orderDoc) ? asString($orderDoc['order_status'] ?? '') : '';
        $existingStatusHistory = is_array($orderDoc['status_history'] ?? null) ? (array) $orderDoc['status_history'] : [];
        if (
            $normalizedStatus !== ''
            && ($existingOrderStatus === ''
                || strcasecmp($existingOrderStatus, $normalizedStatus) !== 0
                || $existingStatusHistory === [])
        ) {
            upsertMongoOrderStatus(
                $env,
                $orderPublicId,
                $customerPublicId,
                $normalizedStatus,
                'System',
                'Order synchronized from sale record.',
                $trackingNumber !== '' ? $trackingNumber : null,
                $courierName !== '' ? $courierName : null,
                false,
                $activityAt
            );
        }

        $path = orderTimelinePath($normalizedStatus, $fulfillmentMethod);
        $normalizedStatusKey = strtoupper($normalizedStatus);
        $currentIndex = 0;
        foreach ($path as $idx => $step) {
            if (strcasecmp($step, $normalizedStatus) === 0) {
                $currentIndex = $idx;
                break;
            }
        }
        if ($path === [] || strcasecmp($path[$currentIndex] ?? '', $normalizedStatus) !== 0) {
            $path[] = $normalizedStatus;
            $currentIndex = count($path) - 1;
        }
        $path = array_slice($path, 0, $currentIndex + 1);

        $statusIndex = [];
        foreach ($existingStatusHistory as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $entryStatus = asString($entry['status'] ?? '');
            if ($entryStatus === '') {
                continue;
            }
            $key = strtoupper($entryStatus);
            if (!isset($statusIndex[$key])) {
                $statusIndex[$key] = $entry;
            }
        }
        $missingSteps = [];
        foreach ($path as $step) {
            $key = strtoupper($step);
            if (!isset($statusIndex[$key])) {
                $missingSteps[] = $step;
            }
        }
        if ($missingSteps !== []) {
            $baseTimestamp = $saleDate !== '' ? $saleDate : ($activityAt !== '' ? $activityAt : nowUtc());
            $currentTimestamp = $activityAt !== '' ? $activityAt : $baseTimestamp;
            $appendEntries = [];
            foreach ($path as $idx => $step) {
                $key = strtoupper($step);
                if (isset($statusIndex[$key])) {
                    continue;
                }
                $timestamp = ($idx === count($path) - 1) ? $currentTimestamp : $baseTimestamp;
                $appendEntries[] = [
                    'status' => $step,
                    'timestamp' => $timestamp,
                    'updated_by' => 'System',
                    'remarks' => 'Order synchronized from sale record.',
                ];
            }
            if ($appendEntries !== []) {
                try {
                    mongoUpdateOne(
                        $manager,
                        $db,
                        $collection,
                        ['order_public_id' => $orderPublicId],
                        [
                            '$push' => ['status_history' => ['$each' => $appendEntries]],
                        ],
                        true
                    );
                } catch (Throwable) {
                    // noop
                }
            }
        }

        $normalizedPaymentStatus = normalizeOrderPaymentStatus($paymentStatus);
        $existingPaymentStatus = is_array($orderDoc)
            ? normalizeOrderPaymentStatus(asString($orderDoc['payment']['payment_status'] ?? ''))
            : '';
        $existingPaymentHistory = is_array($orderDoc['payment_history_status'] ?? null)
            ? (array) $orderDoc['payment_history_status']
            : [];
        if (
            $normalizedPaymentStatus !== ''
            && ($existingPaymentStatus === ''
                || strcasecmp($existingPaymentStatus, $normalizedPaymentStatus) !== 0
                || $existingPaymentHistory === [])
        ) {
            $paymentStatusForMongo = $paymentStatus !== '' ? $paymentStatus : $normalizedPaymentStatus;
            upsertMongoOrderPaymentStatus(
                $env,
                $orderPublicId,
                $customerPublicId,
                $paymentStatusForMongo,
                'System',
                'Payment status synchronized from sale record.',
                $paymentMethod,
                $paymentPublicId
            );
        }

        $paymentPath = [$normalizedPaymentStatus];
        if (strcasecmp($normalizedPaymentStatus, 'Paid') === 0) {
            $paymentPath = ['Pending', 'Paid'];
        } elseif (strcasecmp($normalizedPaymentStatus, 'Failed') === 0) {
            $paymentPath = ['Pending', 'Failed'];
        } elseif (strcasecmp($normalizedPaymentStatus, 'Cancelled') === 0) {
            $paymentPath = ['Pending', 'Cancelled'];
        } elseif (strcasecmp($normalizedPaymentStatus, 'Pending') === 0) {
            $paymentPath = ['Pending'];
        }

        $paymentIndex = [];
        foreach ($existingPaymentHistory as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $entryStatus = asString($entry['status'] ?? '');
            if ($entryStatus === '') {
                continue;
            }
            $key = strtoupper($entryStatus);
            if (!isset($paymentIndex[$key])) {
                $paymentIndex[$key] = $entry;
            }
        }
        $missingPaymentSteps = [];
        foreach ($paymentPath as $step) {
            $key = strtoupper($step);
            if (!isset($paymentIndex[$key])) {
                $missingPaymentSteps[] = $step;
            }
        }
        if ($missingPaymentSteps !== []) {
            $baseTimestamp = $saleDate !== '' ? $saleDate : ($activityAt !== '' ? $activityAt : nowUtc());
            $currentTimestamp = $activityAt !== '' ? $activityAt : $baseTimestamp;
            $appendEntries = [];
            foreach ($paymentPath as $idx => $step) {
                $key = strtoupper($step);
                if (isset($paymentIndex[$key])) {
                    continue;
                }
                $timestamp = ($idx === count($paymentPath) - 1) ? $currentTimestamp : $baseTimestamp;
                $appendEntries[] = [
                    'status' => $step,
                    'timestamp' => $timestamp,
                    'updated_by' => 'System',
                    'remarks' => 'Payment status synchronized from sale record.',
                ];
            }
            if ($appendEntries !== []) {
                try {
                    mongoUpdateOne(
                        $manager,
                        $db,
                        $collection,
                        ['order_public_id' => $orderPublicId],
                        [
                            '$push' => ['payment_history_status' => ['$each' => $appendEntries]],
                        ],
                        true
                    );
                } catch (Throwable) {
                    // noop
                }
            }
        }
    }

    return $latestActivity;
}

function ensureMongoOrderHistorySync(array $env, PDO $pdo, int $ttlSeconds = 45): void
{
    if ($ttlSeconds < 1) {
        $ttlSeconds = 1;
    }

    $state = readMongoOrderSyncState();
    $lastRun = (int) ($state['last_run'] ?? 0);
    $now = time();
    if ($lastRun > 0 && ($now - $lastRun) < $ttlSeconds) {
        return;
    }

    $since = asString($state['last_updated_at'] ?? '');
    $latest = syncMongoOrderHistoryFromMysql($env, $pdo, $since !== '' ? $since : null);
    writeMongoOrderSyncState($now, $latest !== '' ? $latest : $since);
}

function normalizeMongoReturnRequestStatus(string $returnProgress, string $itemStatus): string
{
    $progress = strtolower(trim($returnProgress));
    if ($progress === 'requested') {
        return 'Pending Review';
    }
    if ($progress === 'in process') {
        return 'In Process';
    }
    if ($progress === 'approved') {
        return 'Approved';
    }
    if ($progress === 'rejected') {
        return 'Rejected';
    }
    if ($progress === 'finalized') {
        $normalizedItemStatus = trim($itemStatus);
        return $normalizedItemStatus !== '' ? $normalizedItemStatus : 'Finalized';
    }
    $fallback = trim($returnProgress);
    return $fallback !== '' ? $fallback : 'Pending Review';
}

function upsertMongoReturnRequest(
    array $env,
    string $returnPublicId,
    string $customerPublicId,
    string $orderPublicId,
    array $returnedItem,
    string $reason,
    string $description,
    string $shipmentMethod,
    string $refundMethod,
    string $status,
    ?string $createdAt = null,
    string $statusUpdatedBy = 'System',
    string $statusRemarks = ''
): void {
    $manager = mongoManager($env);
    if (!$manager || $returnPublicId === '') {
        return;
    }

    $timestamp = mongoTimestamp();
    $createdAtValue = $createdAt !== null && trim($createdAt) !== '' ? $createdAt : $timestamp;
    $normalizedStatus = $status !== '' ? $status : 'Pending Review';
    $historyEntry = [
        'status' => $normalizedStatus,
        'timestamp' => $timestamp,
        'updated_by' => $statusUpdatedBy !== '' ? $statusUpdatedBy : 'System',
        'remarks' => $statusRemarks !== '' ? $statusRemarks : 'Return/refund status updated.',
    ];
    $existingRequestDoc = null;
    try {
        $existingRequestDoc = mongoFindOne(
            $manager,
            mongoDbName($env),
            mongoCollectionName($env, 'return_request'),
            ['return_public_id' => $returnPublicId]
        );
    } catch (Throwable) {
        $existingRequestDoc = null;
    }
    $existingStatus = is_array($existingRequestDoc)
        ? asString($existingRequestDoc['status'] ?? '')
        : '';
    $existingRefundHistory = is_array($existingRequestDoc['refund_history_status'] ?? null)
        ? (array) $existingRequestDoc['refund_history_status']
        : [];
    $existingReturnHistory = is_array($existingRequestDoc['return_status_history'] ?? null)
        ? (array) $existingRequestDoc['return_status_history']
        : [];

    try {
        $updatePayload = [
            '$set' => [
                'return_public_id' => $returnPublicId,
                'customer_public_id' => $customerPublicId,
                'order_public_id' => $orderPublicId,
                'returned_item' => [
                    'product_public_id' => asString($returnedItem['product_public_id'] ?? ''),
                    'product_name' => asString($returnedItem['product_name'] ?? ''),
                    'unit_price' => round((float) ($returnedItem['unit_price'] ?? 0), 2),
                ],
                'reason' => $reason,
                'description' => $description,
                'evidence_photos' => [],
                'preferences' => [
                    'shipment_method' => $shipmentMethod,
                    'refund_method' => $refundMethod,
                ],
                'status' => $normalizedStatus,
                'updated_at' => $timestamp,
            ],
            '$setOnInsert' => [
                'created_at' => $createdAtValue,
            ],
            '$push' => [
                'refund_history_status' => $historyEntry,
                'return_status_history' => $historyEntry,
            ],
        ];

        mongoUpdateOne(
            $manager,
            mongoDbName($env),
            mongoCollectionName($env, 'return_request'),
            ['return_public_id' => $returnPublicId],
            $updatePayload,
            true
        );
    } catch (Throwable) {
        // noop
    }
}

function syncMongoProductCatalogFromMysql(array $env, PDO $pdo, string $productPublicId, array $overrides = []): void
{
    $productPublicId = strtoupper(asString($productPublicId));
    if ($productPublicId === '') {
        return;
    }

    $manager = mongoManager($env);
    if (!$manager) {
        return;
    }

    $allowInsert = true;
    if (array_key_exists('allow_insert', $overrides)) {
        $resolvedAllowInsert = filter_var($overrides['allow_insert'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($resolvedAllowInsert !== null) {
            $allowInsert = $resolvedAllowInsert;
        } else {
            $allowInsert = (int) ($overrides['allow_insert'] ?? 0) === 1;
        }
    }

    $mysqlProduct = mysqlProductByPublicId($pdo, $productPublicId);
    if ($mysqlProduct === null) {
        return;
    }

    $dbName = mongoDbName($env);
    $mysqlProductId = (int) ($mysqlProduct['productID'] ?? 0);
    $existing = null;
    $updateFilter = ['product_public_id' => $productPublicId];
    try {
        $existing = mongoFindOne($manager, $dbName, mongoCollectionName($env, 'product_catalog'), ['product_public_id' => $productPublicId]);
    } catch (Throwable) {
        $existing = null;
    }
    if (!is_array($existing)) {
        try {
            $existing = mongoFindOne(
                $manager,
                $dbName,
                mongoCollectionName($env, 'product_catalog'),
                ['product_public_id' => ['$regex' => '^' . preg_quote($productPublicId, '/') . '$', '$options' => 'i']]
            );
        } catch (Throwable) {
            $existing = null;
        }
    }
    if (!is_array($existing) && $mysqlProductId > 0) {
        try {
            $existing = mongoFindOne(
                $manager,
                $dbName,
                mongoCollectionName($env, 'product_catalog'),
                ['product_id' => $mysqlProductId]
            );
        } catch (Throwable) {
            $existing = null;
        }
    }
    if (is_array($existing)) {
        $existingObjectId = mongoObjectIdFromValue($existing['_id'] ?? null);
        if ($existingObjectId !== null) {
            $updateFilter = ['_id' => $existingObjectId];
        }
    }

    $catalogSnapshot = loadProductCatalog($env);
    $catalogByPublicId = catalogMapByPublicId($catalogSnapshot);
    $catalogByLookupKey = catalogMapByLookupKey($catalogSnapshot);
    $schemaByLookupKey = catalogMapByLookupKey(readProductCatalogFallbackFile());
    $lookupKeys = [];
    $rawLookupCandidates = [
        asString($mysqlProduct['product_name'] ?? ''),
        asString($overrides['model_name'] ?? ''),
        asString($overrides['previous_model_name'] ?? ''),
        asString($overrides['previous_name'] ?? ''),
    ];
    foreach ($rawLookupCandidates as $candidate) {
        $normalizedKey = normalizeCatalogLookupKey($candidate);
        if ($normalizedKey === '' || in_array($normalizedKey, $lookupKeys, true)) {
            continue;
        }
        $lookupKeys[] = $normalizedKey;
    }
    $fallbackCatalogItem = $catalogByPublicId[$productPublicId] ?? null;
    if (!is_array($fallbackCatalogItem)) {
        foreach ($lookupKeys as $lookupKey) {
            if (isset($catalogByLookupKey[$lookupKey]) && is_array($catalogByLookupKey[$lookupKey])) {
                $fallbackCatalogItem = $catalogByLookupKey[$lookupKey];
                break;
            }
            if (isset($schemaByLookupKey[$lookupKey]) && is_array($schemaByLookupKey[$lookupKey])) {
                $fallbackCatalogItem = $schemaByLookupKey[$lookupKey];
                break;
            }
        }
    }

    if (!is_array($existing)) {
        $candidate = null;

        $legacyPublicId = strtoupper(asString($fallbackCatalogItem['product_public_id'] ?? ''));
        if ($legacyPublicId !== '' && $legacyPublicId !== $productPublicId) {
            try {
                $candidate = mongoFindOne(
                    $manager,
                    $dbName,
                    mongoCollectionName($env, 'product_catalog'),
                    ['product_public_id' => ['$regex' => '^' . preg_quote($legacyPublicId, '/') . '$', '$options' => 'i']]
                );
            } catch (Throwable) {
                $candidate = null;
            }
        }

        if (!is_array($candidate) && $lookupKeys !== []) {
            try {
                $catalogDocs = mongoFindMany(
                    $manager,
                    $dbName,
                    mongoCollectionName($env, 'product_catalog'),
                    [],
                    ['projection' => ['_id' => 1, 'product_public_id' => 1, 'model_name' => 1, 'product_name' => 1]]
                );
                foreach ($catalogDocs as $catalogDoc) {
                    if (!is_array($catalogDoc)) {
                        continue;
                    }
                    $catalogLookupKey = normalizeCatalogLookupKey(asString($catalogDoc['model_name'] ?? $catalogDoc['product_name'] ?? ''));
                    if ($catalogLookupKey === '' || !in_array($catalogLookupKey, $lookupKeys, true)) {
                        continue;
                    }
                    $candidate = $catalogDoc;
                    break;
                }
            } catch (Throwable) {
                $candidate = null;
            }
        }

        if (is_array($candidate)) {
            $existing = $candidate;
            $candidateObjectId = mongoObjectIdFromValue($candidate['_id'] ?? null);
            if ($candidateObjectId !== null) {
                $updateFilter = ['_id' => $candidateObjectId];
            } else {
                $candidatePublicId = strtoupper(asString($candidate['product_public_id'] ?? ''));
                if ($candidatePublicId !== '') {
                    $updateFilter = ['product_public_id' => $candidatePublicId];
                }
            }
        }
    }

    if (!is_array($existing) && !$allowInsert) {
        return;
    }

    if (is_array($fallbackCatalogItem)) {
        if (is_array($existing)) {
            $existing = mergeCatalogDocumentWithFallback($existing, $fallbackCatalogItem);
        } else {
            $existing = $fallbackCatalogItem;
        }
    }

    $quantity = array_key_exists('stock_level', $overrides)
        ? (int) ($overrides['stock_level'] ?? 0)
        : (int) ($mysqlProduct['quantity'] ?? 0);
    $isActive = (int) ($mysqlProduct['is_active'] ?? 0) === 1;
    if (array_key_exists('is_active', $overrides)) {
        $isActive = filter_var($overrides['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($isActive === null) {
            $isActive = (int) ($overrides['is_active'] ?? 0) === 1;
        }
    }
    $isInStock = $isActive && $quantity > 0;
    $category = asString($overrides['category'] ?? $existing['category'] ?? 'General');
    $subCategory = asString($overrides['sub_category'] ?? $existing['sub_category'] ?? $category);

    $specifications = $overrides['specifications'] ?? ($existing['specifications'] ?? []);
    if (!is_array($specifications)) {
        $specifications = [];
    }

    $reviews = $overrides['reviews'] ?? ($existing['reviews'] ?? null);
    if (!is_array($reviews)) {
        $reviews = ['average_rating' => 0, 'total_reviews' => 0, 'rating_sum' => 0];
    }
    $tags = $overrides['tags'] ?? ($existing['tags'] ?? []);
    if (!is_array($tags)) {
        $tags = [];
    }
    $uiFlags = $overrides['ui_flags'] ?? ($existing['ui_flags'] ?? []);
    if (!is_array($uiFlags)) {
        $uiFlags = [];
    }

    try {
        mongoUpdateOne($manager, $dbName, mongoCollectionName($env, 'product_catalog'), $updateFilter, [
            '$set' => [
                'product_public_id' => $productPublicId,
                'product_id' => $mysqlProductId,
                'brand' => asString($overrides['brand'] ?? $existing['brand'] ?? 'Generic'),
                'model_name' => asString($overrides['model_name'] ?? $mysqlProduct['product_name'] ?? $existing['model_name'] ?? ''),
                'category' => $category,
                'sub_category' => $subCategory,
                'display_price' => (float) ($overrides['display_price'] ?? $mysqlProduct['selling_price'] ?? 0),
                'stock_level' => $quantity,
                'is_active' => $isActive,
                'is_in_stock' => $isInStock,
                'availability_status' => $isInStock ? 'AVAILABLE' : 'SOLD OUT',
                'short_description' => asString($overrides['short_description'] ?? $existing['short_description'] ?? ''),
                'long_description' => asString($overrides['long_description'] ?? $existing['long_description'] ?? ''),
                'specifications' => $specifications,
                'image_url' => asString($overrides['image_url'] ?? $existing['image_url'] ?? ''),
                'tags' => $tags,
                'ui_flags' => $uiFlags,
                'reviews' => $reviews,
                'updated_at' => mongoTimestamp(),
            ],
        ], $allowInsert);
    } catch (Throwable) {
        // noop
    }
}

