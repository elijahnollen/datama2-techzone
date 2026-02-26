<?php

declare(strict_types=1);

if (!function_exists('adminSqlErrorMessage')) {
    function adminSqlErrorMessage(Throwable $error, string $fallback): string
    {
        $message = trim($error->getMessage());
        if (preg_match('/SQLSTATE\[[^\]]+\]:[^:]*:\s*(.+)$/i', $message, $matches) === 1) {
            $message = trim((string) ($matches[1] ?? ''));
        }
        if (preg_match('/Error:\s*(.+)$/i', $message, $matches) === 1) {
            $message = trim((string) ($matches[1] ?? ''));
        }
        if (preg_match('/duplicate entry/i', $message) === 1 || preg_match('/for key/i', $message) === 1) {
            return 'A supplier with the same name, email, or contact number already exists.';
        }
        if (preg_match('/integrity constraint violation/i', $message) === 1) {
            return 'Unable to save this supplier because it conflicts with existing records.';
        }
        return $message !== '' ? $message : $fallback;
    }
}

if (!function_exists('adminAllowedOrderStatuses')) {
    function adminAllowedOrderStatuses(string $currentStatus, string $fulfillmentMethod): array
    {
        $current = trim($currentStatus);
        if ($current === '') {
            $current = 'Pending';
        }
        if (in_array($current, ['Completed', 'Cancelled'], true)) {
            return [$current];
        }

        if (strcasecmp($fulfillmentMethod, 'Walk-in') === 0) {
            return ['Completed'];
        }

        if (strcasecmp($fulfillmentMethod, 'Pickup') === 0) {
            return match ($current) {
                'Pending' => ['Pending', 'Processing', 'Cancelled'],
                'Processing' => ['Processing', 'Ready for Pickup', 'Cancelled'],
                'Ready for Pickup' => ['Ready for Pickup', 'Completed', 'Cancelled'],
                default => [$current],
            };
        }

        return match ($current) {
            'Pending' => ['Pending', 'Processing', 'Cancelled'],
            'Processing' => ['Processing', 'Shipped', 'Cancelled'],
            'Shipped' => ['Shipped', 'Delivered'],
            'Delivered' => ['Delivered', 'Completed'],
            default => [$current],
        };
    }
}

if (!function_exists('adminNormalizeRefundMethod')) {
    function adminNormalizeRefundMethod(string $method): string
    {
        $value = trim($method);
        return in_array($value, ['Cash', 'GCash', 'Card', 'Store Credit'], true) ? $value : 'Cash';
    }
}

if (!function_exists('adminNormalizeRefundStatus')) {
    function adminNormalizeRefundStatus(string $status): string
    {
        $value = trim($status);
        if (strcasecmp($value, 'Completed') === 0) {
            return 'Refunded';
        }
        return in_array($value, ['Pending', 'Failed', 'Refunded'], true) ? $value : 'Refunded';
    }
}

if (!function_exists('adminUpsertRefundPayment')) {
    function adminUpsertRefundPayment(
        PDO $pdo,
        string $returnPublicId,
        float $amount,
        string $paymentMethod,
        string $paymentStatus,
        ?string $refundDate = null
    ): bool {
        if ($amount <= 0 || $returnPublicId === '') {
            return false;
        }

        $method = adminNormalizeRefundMethod($paymentMethod);
        $status = adminNormalizeRefundStatus($paymentStatus);
        $dateValue = asString($refundDate ?? '') !== '' ? asString($refundDate ?? '') : date('Y-m-d H:i:s');

        $existingStmt = $pdo->prepare(
            'SELECT rp.public_id
             FROM refund_payment rp
             JOIN return_transaction rt ON rt.returnID = rp.returnID
             WHERE rt.public_id = :return_public_id
             LIMIT 1'
        );
        $existingStmt->execute(['return_public_id' => $returnPublicId]);
        $existingRow = $existingStmt->fetch();
        $publicId = strtoupper(asString($existingRow['public_id'] ?? ''));
        if (!isValidPublicId($publicId)) {
            $publicId = randomPublicId('RF');
        }

        $upsert = $pdo->prepare(
            'CALL refund_payment_upsert(
                :amount, :payment_method, :payment_status, :public_id, :return_public_id, :refund_date
            )'
        );
        $upsert->execute([
            'amount' => $amount,
            'payment_method' => $method,
            'payment_status' => $status,
            'public_id' => $publicId,
            'return_public_id' => $returnPublicId,
            'refund_date' => $dateValue,
        ]);
        $upsert->closeCursor();

        return true;
    }
}

if (!function_exists('adminNormalizeTransitionValue')) {
    function adminNormalizeTransitionValue(mixed $value): mixed
    {
        if ($value instanceof stdClass) {
            $value = (array) $value;
        }

        if (is_array($value)) {
            if ($value === []) {
                return [];
            }

            $isList = array_keys($value) === range(0, count($value) - 1);
            if ($isList) {
                return array_map(static fn($entry) => adminNormalizeTransitionValue($entry), $value);
            }

            $normalized = [];
            foreach ($value as $key => $entry) {
                $normalized[(string) $key] = adminNormalizeTransitionValue($entry);
            }
            ksort($normalized);
            return $normalized;
        }

        if (is_int($value) || is_float($value)) {
            return round((float) $value, 4);
        }

        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }
}

if (!function_exists('adminCaseInsensitiveTransitionFields')) {
    function adminCaseInsensitiveTransitionFields(): array
    {
        return ['name', 'brand', 'model_name', 'category'];
    }
}

if (!function_exists('adminNormalizeTextForInsensitiveCompare')) {
    function adminNormalizeTextForInsensitiveCompare(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value));
        if (!is_string($normalized)) {
            $normalized = trim($value);
        }
        return strtoupper($normalized);
    }
}

if (!function_exists('adminNormalizeUiFlagsForCompare')) {
    function adminNormalizeUiFlagsForCompare(mixed $value): array
    {
        if ($value instanceof stdClass) {
            $value = (array) $value;
        }
        if (!is_array($value)) {
            $value = [];
        }

        $isFeaturedRaw = $value['is_featured'] ?? $value['isFeatured'] ?? false;
        $isFeatured = filter_var($isFeaturedRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($isFeatured === null) {
            $isFeatured = (int) ($isFeaturedRaw ?? 0) === 1;
        }

        $badgeRaw = $value['badge'] ?? '';
        $badge = trim((string) ($badgeRaw ?? ''));

        return [
            'is_featured' => $isFeatured ? true : false,
            'badge' => $badge,
        ];
    }
}

if (!function_exists('adminValuesEquivalentForField')) {
    function adminValuesEquivalentForField(string $field, mixed $beforeValue, mixed $afterValue): bool
    {
        if ($field === 'ui_flags') {
            return adminNormalizeUiFlagsForCompare($beforeValue) === adminNormalizeUiFlagsForCompare($afterValue);
        }

        if (
            is_string($beforeValue)
            && is_string($afterValue)
            && in_array($field, adminCaseInsensitiveTransitionFields(), true)
        ) {
            return adminNormalizeTextForInsensitiveCompare($beforeValue) === adminNormalizeTextForInsensitiveCompare($afterValue);
        }

        return adminNormalizeTransitionValue($beforeValue) === adminNormalizeTransitionValue($afterValue);
    }
}

if (!function_exists('adminBuildFieldLevelTransition')) {
    function adminBuildFieldLevelTransition(array $beforeState, array $afterState): ?array
    {
        $changedFields = [];
        $previousState = [];
        $nextState = [];

        $keys = array_values(array_unique(array_merge(array_keys($beforeState), array_keys($afterState))));
        foreach ($keys as $key) {
            $field = (string) $key;
            $beforeValue = $beforeState[$field] ?? null;
            $afterValue = $afterState[$field] ?? null;

            if (adminValuesEquivalentForField($field, $beforeValue, $afterValue)) {
                continue;
            }

            $changedFields[] = $field;
            $previousState[$field] = $beforeValue;
            $nextState[$field] = $afterValue;
        }

        if ($changedFields === []) {
            return null;
        }

        if (count($changedFields) === 1) {
            $field = $changedFields[0];
            return [
                'previous_state' => $previousState[$field] ?? null,
                'new_state' => $nextState[$field] ?? null,
            ];
        }

        return [
            'previous_state' => $previousState,
            'new_state' => $nextState,
        ];
    }
}

if (!function_exists('adminBackfillMissingRefundPayments')) {
    function adminBackfillMissingRefundPayments(PDO $pdo): int
    {
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

        if (!is_array($rows) || $rows === []) {
            return 0;
        }

        $count = 0;
        foreach ($rows as $row) {
            $ok = adminUpsertRefundPayment(
                $pdo,
                asString($row['return_public_id'] ?? ''),
                (float) ($row['refund_amount'] ?? 0),
                asString($row['payment_method'] ?? 'Cash'),
                asString($row['payment_status'] ?? 'Refunded'),
                asString($row['date_created'] ?? '')
            );
            if ($ok) {
                $count += 1;
            }
        }

        return $count;
    }
}

if (!function_exists('adminNormalizedDateRangeQuery')) {
    /**
     * @return array{start:?string,end:?string,start_sql:?string,end_sql:?string}
     */
    function adminNormalizedDateRangeQuery(): array
    {
        $startRaw = asString($_GET['start_date'] ?? '');
        $endRaw = asString($_GET['end_date'] ?? '');
        $errors = [];

        $parseDate = static function (string $value, string $label) use (&$errors): ?DateTimeImmutable {
            if ($value === '') {
                return null;
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
                $errors[$label] = 'Date must use YYYY-MM-DD format.';
                return null;
            }
            $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            if (!$parsed || $parsed->format('Y-m-d') !== $value) {
                $errors[$label] = 'Invalid calendar date.';
                return null;
            }
            return $parsed;
        };

        $startDate = $parseDate($startRaw, 'start_date');
        $endDate = $parseDate($endRaw, 'end_date');

        if ($errors !== []) {
            validationFail($errors);
        }

        if ($startDate && $endDate && $startDate->getTimestamp() > $endDate->getTimestamp()) {
            $swap = $startDate;
            $startDate = $endDate;
            $endDate = $swap;
        }

        return [
            'start' => $startDate ? $startDate->format('Y-m-d') : null,
            'end' => $endDate ? $endDate->format('Y-m-d') : null,
            'start_sql' => $startDate ? $startDate->format('Y-m-d') . ' 00:00:00' : null,
            'end_sql' => $endDate ? $endDate->format('Y-m-d') . ' 23:59:59' : null,
        ];
    }
}

if ($path === '/admin/dashboard' && $method === 'GET') {
        $claims = requireAuth($env, ['admin', 'employee']);
        try {
            adminBackfillMissingRefundPayments($pdo);
        } catch (Throwable) {
            // Best-effort repair; never block dashboard rendering.
        }

        $revenue = (float) ($pdo->query("SELECT COALESCE(SUM(total_amount),0) AS value FROM api_sale WHERE sale_status <> 'Cancelled'")->fetch()['value'] ?? 0);
        $cogs = (float) ($pdo->query(
            'SELECT COALESCE(SUM(si.quantity_sold * COALESCE(ps.max_wholesale, p.selling_price)), 0) AS value
             FROM api_sale_item si
             JOIN api_sale s ON s.saleID = si.saleID
             JOIN api_product p ON p.productID = si.productID
             LEFT JOIN api_product_supplier_max ps ON ps.productID = si.productID
             WHERE s.sale_status <> "Cancelled"'
        )->fetch()['value'] ?? 0);
        $ordersCount = (int) ($pdo->query('SELECT COUNT(*) AS value FROM api_sale')->fetch()['value'] ?? 0);
        $productCount = (int) ($pdo->query('SELECT COUNT(*) AS value FROM api_product WHERE is_active = 1')->fetch()['value'] ?? 0);
        $activeUsers = (int) ($pdo->query('SELECT COUNT(*) AS value FROM api_customer WHERE is_active = 1')->fetch()['value'] ?? 0);
        $totalReturns = (int) ($pdo->query('SELECT COUNT(*) AS value FROM api_return_transaction')->fetch()['value'] ?? 0);
        $pendingReturnRequests = (int) ($pdo->query("SELECT COUNT(*) AS value FROM api_return_transaction WHERE return_progress IN ('Requested','In Process')")->fetch()['value'] ?? 0);
        $finalizedReturns = (int) ($pdo->query("SELECT COUNT(*) AS value FROM api_return_transaction WHERE return_progress = 'Finalized'")->fetch()['value'] ?? 0);
        $pendingReturns = $totalReturns;
        $refundedAmount = (float) ($pdo->query('SELECT COALESCE(SUM(amount), 0) AS value FROM api_refund_payment')->fetch()['value'] ?? 0);
        if ($refundedAmount <= 0) {
            $refundedAmount = (float) ($pdo->query(
                'SELECT COALESCE(SUM(
                    COALESCE(
                        NULLIF(rt.refund_amount, 0),
                        items.refund_total,
                        0
                    )
                ), 0) AS value
                 FROM api_return_transaction rt
                 LEFT JOIN (
                    SELECT ri.returnID, SUM(ri.return_quantity * si.price_at_sale) AS refund_total
                    FROM api_return_item ri
                    JOIN api_sale_item si ON si.sale_itemID = ri.sale_itemID
                    WHERE ri.return_status IN ("Refunded", "Store Credit")
                    GROUP BY ri.returnID
                 ) items ON items.returnID = rt.returnID
                 WHERE rt.return_progress = "Finalized"'
            )->fetch()['value'] ?? 0);
        }
        $grossMargin = $revenue - $cogs;

        $manager = mongoManager($env);
        $logs = [];
        $customerInquiries = [];
        if ($manager) {
            $logs = loadAuditLogs($env, $manager, 12);
            try {
                $inquiryDocs = mongoFindMany(
                    $manager,
                    mongoDbName($env),
                    mongoCollectionName($env, 'customer_inquiry'),
                    [],
                    ['sort' => ['created_at' => -1], 'limit' => 20]
                );
                foreach ($inquiryDocs as $doc) {
                    $docId = '';
                    if (isset($doc['_id'])) {
                        if (is_string($doc['_id'])) {
                            $docId = $doc['_id'];
                        } elseif (is_array($doc['_id'])) {
                            $docId = asString($doc['_id']['$oid'] ?? '');
                        } elseif (class_exists('MongoDB\\BSON\\ObjectId') && $doc['_id'] instanceof MongoDB\BSON\ObjectId) {
                            $docId = (string) $doc['_id'];
                        } else {
                            $docId = asString($doc['_id']);
                        }
                    }

                    $customerInquiries[] = [
                        'id' => $docId,
                        'customer_id' => asString($doc['customer_id'] ?? $doc['customer_public_id'] ?? ''),
                        'customer_name' => asString($doc['customer_name'] ?? ''),
                        'customer_email' => asString($doc['customer_email'] ?? ''),
                        'contact_number' => asString($doc['contact_number'] ?? ''),
                        'subject' => asString($doc['subject'] ?? ''),
                        'message' => '',
                        'status' => asString($doc['status'] ?? 'Pending'),
                        'admin_notes' => '',
                        'created_at' => mongoDateToString($doc['created_at'] ?? '', ''),
                        'updated_at' => mongoDateToString($doc['updated_at'] ?? $doc['created_at'] ?? '', ''),
                        'last_message_at' => mongoDateToString($doc['last_message_at'] ?? $doc['updated_at'] ?? $doc['created_at'] ?? '', ''),
                        'message_count' => (int) ($doc['message_count'] ?? 0),
                        'communication_history' => [],
                    ];
                }
            } catch (Throwable) {
                $customerInquiries = [];
            }
        }

        sendJson(200, [
            'ok' => true,
            'data' => [
                'metrics' => [
                    'revenue' => $revenue,
                    'cogs' => $cogs,
                    'gross_margin' => $grossMargin,
                    'orders' => $ordersCount,
                    'products' => $productCount,
                    'active_users' => $activeUsers,
                    'pending_returns' => $pendingReturns,
                    'total_returns' => $totalReturns,
                    'pending_return_requests' => $pendingReturnRequests,
                    'finalized_returns' => $finalizedReturns,
                    'refunded_amount' => $refundedAmount,
                ],
                'activity' => $logs,
                'customer_inquiries' => $customerInquiries,
                'actor' => [
                    'public_id' => (string) ($claims['sub'] ?? ''),
                    'name' => (string) ($claims['name'] ?? ''),
                ],
            ],
        ]);
    }

    if ($path === '/admin/products' && $method === 'GET') {
        requireAuth($env, ['admin', 'employee']);
        $catalogManager = mongoManager($env);
        if (!$catalogManager) {
            sendJson(500, ['ok' => false, 'message' => 'Product catalog service is unavailable. MongoDB connection is required.']);
        }

        $catalog = loadProductCatalogMongoOnly($env);
        $catalogMap = catalogMapByPublicId($catalog);

        $rows = $pdo->query(
            'SELECT p.*,
                    COALESCE(MAX(ps.wholesale_cost), 0) AS wholesale_cost,
                    COALESCE(MAX(ir.stock_from_returns), 0) AS stock_from_returns,
                    COALESCE(MAX(dg.damaged_qty), 0) AS damaged_qty
             FROM api_product p
             LEFT JOIN api_product_supplier ps ON ps.productID = p.productID AND ps.is_active = 1
             LEFT JOIN (
                SELECT productID,
                       COALESCE(SUM(CASE WHEN transaction_type = "Return" THEN quantity_change ELSE 0 END), 0) AS stock_from_returns
                FROM inventory_transaction
                GROUP BY productID
             ) ir ON ir.productID = p.productID
             LEFT JOIN (
                SELECT productID, COALESCE(SUM(damaged_quantity), 0) AS damaged_qty
                FROM damaged_goods
                GROUP BY productID
             ) dg ON dg.productID = p.productID
             GROUP BY p.productID
             ORDER BY p.productID DESC'
        )->fetchAll();

        $missingCatalogPublicIds = [];
        foreach ($rows as $row) {
            $rowPublicId = strtoupper(asString($row['public_id'] ?? ''));
            if ($rowPublicId === '' || isset($catalogMap[$rowPublicId])) {
                continue;
            }
            $missingCatalogPublicIds[] = $rowPublicId;
        }

        if ($missingCatalogPublicIds !== []) {
            foreach ($missingCatalogPublicIds as $missingCatalogPublicId) {
                syncMongoProductCatalogFromMysql($env, $pdo, $missingCatalogPublicId);
            }
            $catalog = loadProductCatalogMongoOnly($env);
            $catalogMap = catalogMapByPublicId($catalog);
        }

        $products = [];
        foreach ($rows as $row) {
            $publicId = (string) $row['public_id'];
            $catalogItem = $catalogMap[$publicId]
                ?? [
                'product_public_id' => $publicId,
                'model_name' => (string) $row['product_name'],
                'category' => 'General',
                'sub_category' => 'General',
                'display_price' => (float) $row['selling_price'],
                'image_url' => '',
                'short_description' => '',
                'long_description' => '',
                'specifications' => [],
                'availability_status' => ((int) $row['quantity'] > 0) ? 'AVAILABLE' : 'SOLD OUT',
            ];

            $product = normalizeCatalogProduct($catalogItem, $row, []);
            $product['publicId'] = $publicId;
            $product['wholesaleCost'] = (float) $row['wholesale_cost'];
            $product['stockLevel'] = (int) $row['quantity'];
            $product['status'] = ((int) $row['is_active'] === 1) ? 'AVAILABLE' : 'SOLD OUT';
            $product['adminStatus'] = ((int) $row['is_active'] === 1) ? 'Active' : 'Inactive';
            $product['name'] = (string) $row['product_name'];
            $product['brand'] = asString($catalogItem['brand'] ?? 'Generic');
            $product['modelName'] = asString($catalogItem['model_name'] ?? $row['product_name']);
            $product['category'] = asString($catalogItem['category'] ?? 'General');
            $product['subCategory'] = asString($catalogItem['sub_category'] ?? $catalogItem['category'] ?? 'General');
            $product['shortDescription'] = asString($catalogItem['short_description'] ?? '');
            $product['longDescription'] = asString($catalogItem['long_description'] ?? $catalogItem['short_description'] ?? '');
            $product['specifications'] = is_array($catalogItem['specifications'] ?? null) ? $catalogItem['specifications'] : [];
            $product['tags'] = is_array($catalogItem['tags'] ?? null) ? $catalogItem['tags'] : [];
            $product['uiFlags'] = is_array($catalogItem['ui_flags'] ?? null) ? $catalogItem['ui_flags'] : [];
            $product['reviewSummary'] = is_array($catalogItem['reviews'] ?? null) ? $catalogItem['reviews'] : [
                'average_rating' => 0,
                'total_reviews' => 0,
                'rating_sum' => 0,
            ];
            $product['stockFromReturns'] = (int) ($row['stock_from_returns'] ?? 0);
            $product['damaged'] = (int) ($row['damaged_qty'] ?? 0);
            $product['reserved'] = 0;
            $products[] = $product;
        }

        sendJson(200, ['ok' => true, 'data' => ['products' => $products]]);
    }

    if ($path === '/admin/products' && $method === 'POST') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $body = requestBody($env);

        $name = asString($body['name'] ?? '');
        $brand = asString($body['brand'] ?? 'Generic');
        $modelName = asString($body['model_name'] ?? $name);
        $category = asString($body['category'] ?? 'General');
        $price = (float) ($body['price'] ?? 0);
        $stock = (int) ($body['stock_level'] ?? 0);
        $shortDescription = asString($body['short_description'] ?? $body['description'] ?? '');
        $longDescription = asString($body['long_description'] ?? $shortDescription);
        $image = asString($body['image_url'] ?? '');
        $wholesaleCost = (float) ($body['wholesale_cost'] ?? 0);
        $supplierPublicId = strtoupper(asString($body['supplier_public_id'] ?? ''));
        $supplierProductName = asString($body['supplier_product_name'] ?? $name);
        $supplierWholesaleCost = (float) ($body['supplier_wholesale_cost'] ?? $wholesaleCost);
        $specifications = is_array($body['specifications'] ?? null) ? (array) $body['specifications'] : [];
        $tags = is_array($body['tags'] ?? null) ? (array) $body['tags'] : [];
        $uiFlags = is_array($body['ui_flags'] ?? null) ? (array) $body['ui_flags'] : [];

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Product name is required.';
        } else {
            $nameError = validateTextLength($name, 'Product name', 2, 100);
            if ($nameError !== null) {
                $errors['name'] = $nameError;
            }
        }
        if ($category !== '') {
            $categoryError = validateTextLength($category, 'Category', 2, 50);
            if ($categoryError !== null) {
                $errors['category'] = $categoryError;
            }
        }
        if ($price < 0) {
            $errors['price'] = 'Price cannot be negative.';
        }
        if ($stock < 0) {
            $errors['stock_level'] = 'Stock cannot be negative.';
        }
        if ($wholesaleCost < 0) {
            $errors['wholesale_cost'] = 'Wholesale cost cannot be negative.';
        }
        if ($supplierPublicId !== '' && !isValidPublicId($supplierPublicId)) {
            $errors['supplier_public_id'] = 'Please select a valid supplier.';
        }
        if ($supplierPublicId !== '' && $supplierWholesaleCost < 0) {
            $errors['supplier_wholesale_cost'] = 'Supplier wholesale cost cannot be negative.';
        }
        if ($price < $wholesaleCost) {
            $errors['price'] = 'Selling price cannot be lower than wholesale cost.';
        }
        if ($shortDescription !== '') {
            $descriptionError = validateTextLength($shortDescription, 'Short description', 2, 1500);
            if ($descriptionError !== null) {
                $errors['short_description'] = $descriptionError;
            }
        }
        if ($longDescription !== '') {
            $longDescriptionError = validateTextLength($longDescription, 'Long description', 2, 3000);
            if ($longDescriptionError !== null) {
                $errors['long_description'] = $longDescriptionError;
            }
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        $publicId = randomPublicId('PR');
        try {
            $stmt = $pdo->prepare('CALL record_new_product(:public_id, :product_name, :quantity, :selling_price)');
            $stmt->execute([
                'public_id' => $publicId,
                'product_name' => $name,
                'quantity' => $stock,
                'selling_price' => $price,
            ]);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $message = trim($e->getMessage());
            if (preg_match('/Error:\s*(.+)$/i', $message, $matches) === 1) {
                $message = trim((string) ($matches[1] ?? ''));
            }
            validationFail([
                'product' => $message !== '' ? $message : 'Unable to create product.',
            ]);
        }

        $createdProduct = mysqlProductByPublicId($pdo, $publicId);
        $productId = (int) ($createdProduct['productID'] ?? 0);
        $supplierLinked = false;
        if ($supplierPublicId !== '' && $productId > 0) {
            $supplierStmt = $pdo->prepare(
                'SELECT supplierID, supplier_name
                 FROM api_supplier
                 WHERE public_id = :public_id AND is_active = 1
                 LIMIT 1'
            );
            $supplierStmt->execute(['public_id' => $supplierPublicId]);
            $supplier = $supplierStmt->fetch();
            if (!is_array($supplier)) {
                validationFail(['supplier_public_id' => 'Selected supplier was not found or is inactive.']);
            }

            try {
                $linkStmt = $pdo->prepare(
                    'CALL link_product_supplier(
                        :supplierID,
                        :productID,
                        :supplier_product_name,
                        :wholesale_cost
                    )'
                );
                $linkStmt->execute([
                    'supplierID' => (int) ($supplier['supplierID'] ?? 0),
                    'productID' => $productId,
                    'supplier_product_name' => $supplierProductName !== '' ? $supplierProductName : $name,
                    'wholesale_cost' => $supplierWholesaleCost,
                ]);
                $linkStmt->closeCursor();
                $supplierLinked = true;
            } catch (PDOException $e) {
                validationFail([
                    'supplier_link' => adminSqlErrorMessage($e, 'Unable to link supplier to product.'),
                ]);
            }
        }

        syncMongoProductCatalogFromMysql($env, $pdo, $publicId, [
            'brand' => $brand,
            'model_name' => $modelName !== '' ? $modelName : $name,
            'category' => $category,
            'sub_category' => $category,
            'display_price' => $price,
            'short_description' => $shortDescription,
            'long_description' => $longDescription,
            'specifications' => $specifications,
            'tags' => $tags,
            'ui_flags' => $uiFlags,
            'image_url' => $image,
            'reviews' => ['average_rating' => 0, 'total_reviews' => 0, 'rating_sum' => 0],
        ]);

        appendAuditLog($env, 'PRODUCT_LOG', 'PRODUCT_CREATED', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'ProductCatalog',
            'resource_public_id' => $publicId,
        ]);

        sendJson(201, [
            'ok' => true,
            'message' => 'Product created successfully.',
            'data' => ['public_id' => $publicId, 'productID' => $productId, 'supplier_linked' => $supplierLinked],
        ]);
    }

    if (preg_match('#^/admin/products/([A-Za-z0-9\-]+)$#', $path, $matches) === 1 && $method === 'PUT') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $publicId = strtoupper($matches[1]);

        $body = requestBody($env);
        $fallbackProductId = (int) ($body['product_id'] ?? 0);

        $product = mysqlProductByPublicId($pdo, $publicId);
        if ($product === null && $fallbackProductId > 0) {
            $byIdStmt = $pdo->prepare('SELECT * FROM api_product WHERE productID = :id LIMIT 1');
            $byIdStmt->execute(['id' => $fallbackProductId]);
            $row = $byIdStmt->fetch();
            if (is_array($row)) {
                $product = $row;
                $publicId = strtoupper(asString($row['public_id'] ?? $publicId));
            }
        }
        if ($product === null) {
            sendJson(404, ['ok' => false, 'message' => 'Product not found.']);
        }
        $publicId = strtoupper(asString($product['public_id'] ?? $publicId));

        $wholesaleStmt = $pdo->prepare('SELECT COALESCE(MAX(wholesale_cost), 0) AS c FROM api_product_supplier WHERE productID = :productID AND is_active = 1');
        $wholesaleStmt->execute(['productID' => (int) $product['productID']]);
        $currentWholesale = (float) ($wholesaleStmt->fetch()['c'] ?? 0);

        $catalog = loadProductCatalog($env);
        $catalogMap = catalogMapByPublicId($catalog);
        $catalogByLookupKey = catalogMapByLookupKey($catalog);
        $schemaByLookupKey = catalogMapByLookupKey(readProductCatalogFallbackFile());
        $currentName = asString($product['product_name'] ?? '');
        $lookupKey = normalizeCatalogLookupKey($currentName);
        $catalogItem = $catalogMap[$publicId]
            ?? (($lookupKey !== '' && isset($catalogByLookupKey[$lookupKey]) && is_array($catalogByLookupKey[$lookupKey])) ? $catalogByLookupKey[$lookupKey] : null)
            ?? (($lookupKey !== '' && isset($schemaByLookupKey[$lookupKey]) && is_array($schemaByLookupKey[$lookupKey])) ? $schemaByLookupKey[$lookupKey] : null)
            ?? [];

        $currentCategory = asString($catalogItem['category'] ?? 'General');
        $currentBrand = asString($catalogItem['brand'] ?? 'Generic');
        $currentModelName = asString($catalogItem['model_name'] ?? $currentName);
        $currentShortDescription = asString($catalogItem['short_description'] ?? '');
        $currentLongDescription = asString($catalogItem['long_description'] ?? $catalogItem['short_description'] ?? '');
        $currentImage = asString($catalogItem['image_url'] ?? '');
        $currentSpecs = is_array($catalogItem['specifications'] ?? null) ? (array) $catalogItem['specifications'] : [];
        $currentTags = is_array($catalogItem['tags'] ?? null) ? (array) $catalogItem['tags'] : [];
        $currentUiFlags = is_array($catalogItem['ui_flags'] ?? null) ? (array) $catalogItem['ui_flags'] : [];
        $currentReviews = is_array($catalogItem['reviews'] ?? null) ? (array) $catalogItem['reviews'] : ['average_rating' => 0, 'total_reviews' => 0, 'rating_sum' => 0];

        $name = array_key_exists('name', $body) ? asString($body['name'] ?? '') : $currentName;
        $brand = array_key_exists('brand', $body) ? asString($body['brand'] ?? '') : $currentBrand;
        $modelNameProvided = array_key_exists('model_name', $body);
        $modelName = $modelNameProvided ? asString($body['model_name'] ?? '') : $currentModelName;
        if (!$modelNameProvided && array_key_exists('name', $body)) {
            // Keep catalog model_name aligned when admin updates only the product name.
            $modelName = $name;
        }
        if ($modelName === '') {
            $modelName = $name;
        }
        $category = array_key_exists('category', $body) ? asString($body['category'] ?? '') : $currentCategory;

        if (adminValuesEquivalentForField('name', $currentName, $name)) {
            $name = $currentName;
        }
        if (adminValuesEquivalentForField('brand', $currentBrand, $brand)) {
            $brand = $currentBrand;
        }
        if (adminValuesEquivalentForField('model_name', $currentModelName, $modelName)) {
            $modelName = $currentModelName;
        }
        if (adminValuesEquivalentForField('category', $currentCategory, $category)) {
            $category = $currentCategory;
        }
        $price = array_key_exists('price', $body) ? (float) $body['price'] : (float) ($product['selling_price'] ?? 0);
        $stockLevel = array_key_exists('stock_level', $body) ? (int) $body['stock_level'] : (int) ($product['quantity'] ?? 0);
        $shortDescription = array_key_exists('short_description', $body)
            ? asString($body['short_description'] ?? '')
            : (array_key_exists('description', $body) ? asString($body['description'] ?? '') : $currentShortDescription);
        $longDescription = array_key_exists('long_description', $body)
            ? asString($body['long_description'] ?? '')
            : (array_key_exists('description', $body) ? asString($body['description'] ?? '') : $currentLongDescription);
        $image = array_key_exists('image_url', $body) ? asString($body['image_url'] ?? '') : $currentImage;
        $wholesaleCost = array_key_exists('wholesale_cost', $body) ? (float) $body['wholesale_cost'] : $currentWholesale;
        $specifications = is_array($body['specifications'] ?? null) ? (array) $body['specifications'] : $currentSpecs;
        $tags = is_array($body['tags'] ?? null) ? (array) $body['tags'] : $currentTags;
        $uiFlags = is_array($body['ui_flags'] ?? null) ? (array) $body['ui_flags'] : $currentUiFlags;
        if (adminValuesEquivalentForField('ui_flags', $currentUiFlags, $uiFlags)) {
            $uiFlags = $currentUiFlags;
        }
        $reviews = is_array($body['reviews'] ?? null) ? (array) $body['reviews'] : $currentReviews;

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Product name is required.';
        } else {
            $nameError = validateTextLength($name, 'Product name', 2, 100);
            if ($nameError !== null) {
                $errors['name'] = $nameError;
            }
        }
        if ($category !== '') {
            $categoryError = validateTextLength($category, 'Category', 2, 50);
            if ($categoryError !== null) {
                $errors['category'] = $categoryError;
            }
        }
        if ($price < 0) {
            $errors['price'] = 'Price cannot be negative.';
        }
        if ($stockLevel < 0) {
            $errors['stock_level'] = 'Stock cannot be negative.';
        }
        if ($wholesaleCost < 0) {
            $errors['wholesale_cost'] = 'Wholesale cost cannot be negative.';
        }
        if ($price >= 0 && $wholesaleCost >= 0 && $price < $wholesaleCost) {
            $errors['price'] = 'Selling price cannot be lower than wholesale cost.';
        }
        if ($shortDescription !== '') {
            $descriptionError = validateTextLength($shortDescription, 'Short description', 2, 1500);
            if ($descriptionError !== null) {
                $errors['short_description'] = $descriptionError;
            }
        }
        if ($longDescription !== '') {
            $longDescriptionError = validateTextLength($longDescription, 'Long description', 2, 3000);
            if ($longDescriptionError !== null) {
                $errors['long_description'] = $longDescriptionError;
            }
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        $currentQty = (int) ($product['quantity'] ?? 0);
        $delta = $stockLevel - $currentQty;
        $employee = null;
        if ($delta !== 0) {
            $employee = getEmployeeByPublicId($pdo, (string) $claims['sub']);
            if ($employee === null) {
                sendJson(404, ['ok' => false, 'message' => 'Admin employee profile not found.']);
            }
        }

        $pdo->beginTransaction();
        try {
            $updateName = $pdo->prepare('CALL product_name_update(:public_id, :product_name)');
            $updateName->execute([
                'public_id' => $publicId,
                'product_name' => $name,
            ]);
            $updateName->closeCursor();

            $updatePrice = $pdo->prepare('CALL update_product_price(:public_id, :new_price)');
            $updatePrice->execute([
                'public_id' => $publicId,
                'new_price' => $price,
            ]);
            $updatePrice->closeCursor();

            if ($delta !== 0 && is_array($employee)) {
                $inventoryStmt = $pdo->prepare(
                    'CALL inventory_adjustment_add(
                        :product_public_id,
                        :employee_public_id,
                        :quantity_change,
                        :transaction_type,
                        :reference_id
                    )'
                );
                $inventoryStmt->execute([
                    'product_public_id' => $publicId,
                    'employee_public_id' => (string) ($employee['public_id'] ?? ''),
                    'quantity_change' => $delta,
                    'transaction_type' => $delta > 0 ? 'Restock' : 'Replacement',
                    'reference_id' => null,
                ]);
                $inventoryStmt->closeCursor();
            }

            $updateWholesale = $pdo->prepare('CALL product_wholesale_update(:public_id, :wholesale_cost)');
            $updateWholesale->execute([
                'public_id' => $publicId,
                'wholesale_cost' => $wholesaleCost,
            ]);
            $updateWholesale->closeCursor();

            $pdo->commit();
        } catch (Throwable) {
            rollbackIfInTransaction($pdo);
            validationFail(['product' => 'Unable to update product details.']);
        }

        $resolvedBrand = $brand !== '' ? $brand : $currentBrand;
        $resolvedModelName = $modelName !== '' ? $modelName : $name;
        $isActive = (int) ($product['is_active'] ?? 0) === 1;

        $syncOverrides = [
            'brand' => $resolvedBrand,
            'model_name' => $resolvedModelName,
            'previous_name' => $currentName,
            'previous_model_name' => $currentModelName,
            'category' => $category,
            'sub_category' => $category,
            'display_price' => $price,
            'stock_level' => $stockLevel,
            'is_active' => $isActive,
            'allow_insert' => false,
            'short_description' => $shortDescription,
            'long_description' => $longDescription,
            'image_url' => $image,
            'tags' => $tags,
            'ui_flags' => $uiFlags,
            'reviews' => $reviews,
        ];
        if (is_array($specifications)) {
            $syncOverrides['specifications'] = $specifications;
        }
        syncMongoProductCatalogFromMysql($env, $pdo, $publicId, $syncOverrides);

        $beforeState = [
            'name' => $currentName,
            'brand' => $currentBrand,
            'model_name' => $currentModelName,
            'category' => $currentCategory,
            'price' => (float) ($product['selling_price'] ?? 0),
            'stock_level' => $currentQty,
            'availability_status' => ($isActive && $currentQty > 0) ? 'AVAILABLE' : 'SOLD OUT',
            'wholesale_cost' => $currentWholesale,
            'short_description' => $currentShortDescription,
            'long_description' => $currentLongDescription,
            'image_url' => $currentImage,
            'specifications' => $currentSpecs,
            'tags' => $currentTags,
            'ui_flags' => $currentUiFlags,
        ];
        $afterState = [
            'name' => $name,
            'brand' => $resolvedBrand,
            'model_name' => $resolvedModelName,
            'category' => $category,
            'price' => $price,
            'stock_level' => $stockLevel,
            'availability_status' => ($isActive && $stockLevel > 0) ? 'AVAILABLE' : 'SOLD OUT',
            'wholesale_cost' => $wholesaleCost,
            'short_description' => $shortDescription,
            'long_description' => $longDescription,
            'image_url' => $image,
            'specifications' => $specifications,
            'tags' => $tags,
            'ui_flags' => $uiFlags,
        ];
        $transition = adminBuildFieldLevelTransition($beforeState, $afterState);

        appendAuditLog($env, 'PRODUCT_LOG', 'PRODUCT_UPDATED', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'ProductCatalog',
            'resource_public_id' => $publicId,
        ], $transition);

        sendJson(200, ['ok' => true, 'message' => 'Product updated successfully.']);
    }

    if (preg_match('#^/admin/products/([A-Za-z0-9\-]+)/price$#', $path, $matches) === 1 && $method === 'PUT') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $publicId = strtoupper($matches[1]);
        if (!isValidPublicId($publicId)) {
            validationFail(['product' => 'Invalid product reference format.']);
        }
        $product = mysqlProductByPublicId($pdo, $publicId);
        if ($product === null) {
            sendJson(404, ['ok' => false, 'message' => 'Product not found.']);
        }
        $publicId = strtoupper(asString($product['public_id'] ?? $publicId));

        $body = requestBody($env);
        $newPrice = (float) ($body['price'] ?? -1);
        $currentPrice = (float) ($product['selling_price'] ?? 0);

        if ($newPrice < 0) {
            validationFail(['price' => 'Price must be zero or higher.']);
        }

        try {
            $proc = $pdo->prepare('CALL update_product_price(:public_id, :new_price)');
            $proc->execute(['public_id' => $publicId, 'new_price' => $newPrice]);
            $proc->closeCursor();
        } catch (PDOException $e) {
            $message = trim($e->getMessage());
            if (preg_match('/Error:\s*(.+)$/i', $message, $matches) === 1) {
                $message = trim((string) ($matches[1] ?? ''));
            }
            validationFail([
                'price' => $message !== '' ? $message : 'Unable to update product price.',
            ]);
        }

        syncMongoProductCatalogFromMysql($env, $pdo, $publicId, [
            'display_price' => $newPrice,
            'allow_insert' => false,
        ]);

        appendAuditLog($env, 'PRODUCT_LOG', 'PRICE_UPDATE', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'ProductCatalog',
            'resource_public_id' => $publicId,
        ], [
            'previous_state' => $currentPrice,
            'new_state' => $newPrice,
        ]);

        sendJson(200, ['ok' => true, 'message' => 'Price updated successfully.']);
    }

    if (preg_match('#^/admin/products/([A-Za-z0-9\-]+)/stock$#', $path, $matches) === 1 && $method === 'PUT') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $publicId = strtoupper($matches[1]);
        if (!isValidPublicId($publicId)) {
            validationFail(['product' => 'Invalid product reference format.']);
        }
        $body = requestBody($env);
        $delta = (int) ($body['delta'] ?? 0);

        if ($delta === 0) {
            validationFail(['delta' => 'Stock adjustment cannot be zero.']);
        }
        if (abs($delta) > 5000) {
            validationFail(['delta' => 'Stock adjustment is too large. Please use a value between -5000 and 5000.']);
        }

        $product = mysqlProductByPublicId($pdo, $publicId);
        if ($product === null) {
            sendJson(404, ['ok' => false, 'message' => 'Product not found.']);
        }

        $employee = getEmployeeByPublicId($pdo, (string) $claims['sub']);
        if ($employee === null) {
            sendJson(404, ['ok' => false, 'message' => 'Admin employee profile not found.']);
        }

        $next = (int) $product['quantity'] + $delta;
        if ($next < 0) {
            validationFail(['delta' => 'Stock adjustment would result in negative quantity.']);
        }

        $stmt = $pdo->prepare(
            'CALL inventory_adjustment_add(
                :product_public_id,
                :employee_public_id,
                :quantity_change,
                :transaction_type,
                :reference_id
            )'
        );
        $stmt->execute([
            'product_public_id' => $publicId,
            'employee_public_id' => (string) ($employee['public_id'] ?? ''),
            'quantity_change' => $delta,
            'transaction_type' => $delta > 0 ? 'Restock' : 'Replacement',
            'reference_id' => null,
        ]);
        $stmt->closeCursor();

        syncMongoProductCatalogFromMysql($env, $pdo, $publicId, [
            'stock_level' => $next,
            'is_active' => (int) ($product['is_active'] ?? 0) === 1,
            'allow_insert' => false,
        ]);

        sendJson(200, ['ok' => true, 'message' => 'Stock adjusted successfully.']);
    }

    if (preg_match('#^/admin/products/([A-Za-z0-9\-]+)/status$#', $path, $matches) === 1 && $method === 'PATCH') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $publicId = strtoupper($matches[1]);
        if (!isValidPublicId($publicId)) {
            validationFail(['product' => 'Invalid product reference format.']);
        }
        $body = requestBody($env);
        $isActive = parseBoolean($body['is_active'] ?? null);
        if ($isActive === null) {
            validationFail(['is_active' => 'Please provide a boolean value for is_active.']);
        }

        $product = mysqlProductByPublicId($pdo, $publicId);
        if ($product === null) {
            sendJson(404, ['ok' => false, 'message' => 'Product not found.']);
        }
        $publicId = strtoupper(asString($product['public_id'] ?? $publicId));
        $previousIsActive = (int) ($product['is_active'] ?? 0) === 1;
        $currentStock = (int) ($product['quantity'] ?? 0);

        if (!$isActive && $currentStock > 0) {
            validationFail([
                'is_active' => 'Cannot deactivate a product that still has stock on hand. Set stock to 0 first.',
            ]);
        }

        try {
            $stmt = $pdo->prepare('CALL update_product_status(:public_id, :is_active)');
            $stmt->execute([
                'public_id' => $publicId,
                'is_active' => $isActive ? 1 : 0,
            ]);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            validationFail(['is_active' => adminSqlErrorMessage($e, 'Unable to update product status.')]);
        }

        syncMongoProductCatalogFromMysql($env, $pdo, $publicId, [
            'is_active' => $isActive,
            'stock_level' => $currentStock,
            'allow_insert' => false,
        ]);

        appendAuditLog($env, 'PRODUCT_LOG', 'STATUS_UPDATE', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'ProductCatalog',
            'resource_public_id' => $publicId,
        ], [
            'previous_state' => $previousIsActive,
            'new_state' => $isActive,
        ]);

        sendJson(200, ['ok' => true, 'message' => 'Product status updated.']);
    }

    if (preg_match('#^/admin/products/([A-Za-z0-9\-]+)/suppliers$#', $path, $matches) === 1 && $method === 'GET') {
        requireAuth($env, ['admin', 'employee']);
        $productPublicId = strtoupper($matches[1]);
        if (!isValidPublicId($productPublicId)) {
            validationFail(['product' => 'Invalid product reference format.']);
        }

        $rows = $pdo->prepare(
            'SELECT s.supplierID, s.public_id AS supplier_public_id, s.supplier_name,
                    ps.supplier_product_name, ps.wholesale_cost, ps.is_active,
                    s.is_active AS supplier_is_active
             FROM api_product p
             JOIN api_product_supplier ps ON ps.productID = p.productID
             JOIN api_supplier s ON s.supplierID = ps.supplierID
             WHERE p.public_id = :public_id
             ORDER BY s.supplier_name ASC'
        );
        $rows->execute(['public_id' => $productPublicId]);
        sendJson(200, ['ok' => true, 'data' => ['suppliers' => $rows->fetchAll()]]);
    }

    if (preg_match('#^/admin/products/([A-Za-z0-9\-]+)/suppliers$#', $path, $matches) === 1 && $method === 'POST') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $productPublicId = strtoupper($matches[1]);
        if (!isValidPublicId($productPublicId)) {
            validationFail(['product' => 'Invalid product reference format.']);
        }

        $body = requestBody($env);
        $supplierPublicId = strtoupper(asString($body['supplier_public_id'] ?? ''));
        $supplierProductName = asString($body['supplier_product_name'] ?? '');
        $wholesaleCost = (float) ($body['wholesale_cost'] ?? 0);

        $errors = [];
        if ($supplierPublicId === '' || !isValidPublicId($supplierPublicId)) {
            $errors['supplier_public_id'] = 'Please select a valid supplier.';
        }
        if ($supplierProductName === '') {
            $errors['supplier_product_name'] = 'Supplier product name is required.';
        }
        if ($wholesaleCost < 0) {
            $errors['wholesale_cost'] = 'Wholesale cost cannot be negative.';
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        $productStmt = $pdo->prepare('SELECT productID FROM api_product WHERE public_id = :public_id LIMIT 1');
        $productStmt->execute(['public_id' => $productPublicId]);
        $product = $productStmt->fetch();
        if (!is_array($product)) {
            sendJson(404, ['ok' => false, 'message' => 'Product not found.']);
        }

        $supplierStmt = $pdo->prepare('SELECT supplierID FROM api_supplier WHERE public_id = :public_id AND is_active = 1 LIMIT 1');
        $supplierStmt->execute(['public_id' => $supplierPublicId]);
        $supplier = $supplierStmt->fetch();
        if (!is_array($supplier)) {
            validationFail(['supplier_public_id' => 'Selected supplier was not found or is inactive.']);
        }

        try {
            $proc = $pdo->prepare(
                'CALL link_product_supplier(
                    :supplierID,
                    :productID,
                    :supplier_product_name,
                    :wholesale_cost
                )'
            );
            $proc->execute([
                'supplierID' => (int) ($supplier['supplierID'] ?? 0),
                'productID' => (int) ($product['productID'] ?? 0),
                'supplier_product_name' => $supplierProductName,
                'wholesale_cost' => $wholesaleCost,
            ]);
            $proc->closeCursor();
        } catch (PDOException $e) {
            validationFail(['supplier_link' => adminSqlErrorMessage($e, 'Unable to link supplier to product.')]);
        }

        appendAuditLog($env, 'SUPPLIER_LOG', 'PRODUCT_SUPPLIER_LINKED', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'ProductCatalog',
            'resource_public_id' => $productPublicId,
        ], [
            'supplier_public_id' => $supplierPublicId,
            'wholesale_cost' => $wholesaleCost,
        ]);

        sendJson(201, ['ok' => true, 'message' => 'Supplier linked to product successfully.']);
    }

    if ($path === '/admin/employees' && $method === 'GET') {
        requireAuth($env, ['admin', 'employee']);
        $rows = $pdo->query(
            'SELECT employeeID, public_id, first_name, last_name, employee_role, employee_status, email_address, contact_number
             FROM api_employee
             WHERE employee_status = "Active"
             ORDER BY employeeID ASC'
        )->fetchAll();
        sendJson(200, ['ok' => true, 'data' => ['employees' => $rows]]);
    }

    if ($path === '/admin/inventory/transactions' && $method === 'GET') {
        requireAuth($env, ['admin', 'employee']);
        $rows = $pdo->query(
            'SELECT vit.transID, vit.transaction_date, vit.quantity_change,
                    COALESCE(it.transaction_type, vit.reference_description, "Adjustment") AS transaction_type,
                    vit.product_public_id, vit.product_name, vit.current_stock,
                    vit.employee_public_id, vit.employee_name, vit.referenceID, vit.reference_description
             FROM inventory_transactions vit
             LEFT JOIN inventory_transaction it ON it.transID = vit.transID
             ORDER BY vit.transaction_date DESC
             LIMIT 300'
        )->fetchAll();
        sendJson(200, ['ok' => true, 'data' => ['transactions' => $rows]]);
    }

    if ($path === '/admin/sales' && $method === 'POST') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $body = requestBody($env);

        $customerPublicId = strtoupper(asString($body['customer_public_id'] ?? ''));
        $employeePublicIdInput = strtoupper(asString($body['employee_public_id'] ?? ''));
        $fulfillment = asString($body['fulfillment_method'] ?? 'Walk-in');
        $paymentMethod = asString($body['payment_method'] ?? 'Cash');
        $paymentStatus = asString($body['payment_status'] ?? 'Completed');
        $paymentPublicIdInput = strtoupper(asString($body['payment_public_id'] ?? $body['payment_reference_number'] ?? ''));
        $items = is_array($body['items'] ?? null) ? $body['items'] : [];

        $allowedFulfillment = ['Delivery', 'Pickup', 'Walk-in'];
        $allowedPaymentMethods = ['Cash', 'GCash', 'Card', 'Store Credit'];
        $allowedPaymentStatuses = ['Completed', 'Pending', 'Failed'];

        $errors = [];
        if ($customerPublicId === '' || !isValidPublicId($customerPublicId)) {
            $errors['customer_public_id'] = 'Please select a valid customer.';
        }
        if (!in_array($fulfillment, $allowedFulfillment, true)) {
            $errors['fulfillment_method'] = 'Invalid fulfillment method.';
        }
        if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
            $errors['payment_method'] = 'Invalid payment method.';
        }
        if (!in_array($paymentStatus, $allowedPaymentStatuses, true)) {
            $errors['payment_status'] = 'Invalid payment status.';
        }
        if (!is_array($items) || $items === []) {
            $errors['items'] = 'Please add at least one sale item.';
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        $customer = getCustomerByPublicIdAny($pdo, $customerPublicId);
        if (!is_array($customer)) {
            sendJson(404, ['ok' => false, 'message' => 'Customer not found.']);
        }
        if (asString($customer['status'] ?? 'Active') !== 'Active') {
            validationFail(['customer_public_id' => 'Selected customer is not active.']);
        }

        $resolvedEmployeePublicId = $employeePublicIdInput;
        if ($resolvedEmployeePublicId === '') {
            $claimsSub = asString($claims['sub'] ?? '');
            $claimsEmployee = $claimsSub !== '' ? getEmployeeByPublicId($pdo, $claimsSub) : null;
            if (is_array($claimsEmployee)) {
                $resolvedEmployeePublicId = asString($claimsEmployee['public_id'] ?? '');
            } else {
                $fallbackEmployeePublicId = firstEmployeePublicId($pdo);
                if ($fallbackEmployeePublicId !== null) {
                    $resolvedEmployeePublicId = $fallbackEmployeePublicId;
                }
            }
        }
        if ($resolvedEmployeePublicId === '' || !isValidPublicId($resolvedEmployeePublicId)) {
            validationFail(['employee_public_id' => 'Please select a valid employee.']);
        }

        $employee = getEmployeeByPublicId($pdo, $resolvedEmployeePublicId);
        if (!is_array($employee)) {
            validationFail(['employee_public_id' => 'Selected employee was not found.']);
        }
        if (strcasecmp(asString($employee['employee_status'] ?? ''), 'Active') !== 0) {
            validationFail(['employee_public_id' => 'Selected employee is inactive.']);
        }

        $preparedItems = [];
        $subtotal = 0.0;
        foreach ($items as $index => $rawItem) {
            if (!is_array($rawItem)) {
                $errors['items'] = 'Invalid sale item payload.';
                continue;
            }
            $productPublicId = strtoupper(asString($rawItem['product_public_id'] ?? ''));
            $quantity = (int) ($rawItem['quantity'] ?? 0);
            if ($productPublicId === '' || !isValidPublicId($productPublicId)) {
                $errors["items.$index.product_public_id"] = 'Invalid product reference.';
                continue;
            }
            if ($quantity < 1 || $quantity > 50) {
                $errors["items.$index.quantity"] = 'Quantity must be between 1 and 50.';
                continue;
            }

            $product = mysqlProductByPublicId($pdo, $productPublicId);
            if (!is_array($product)) {
                $errors["items.$index.product_public_id"] = 'Selected product was not found.';
                continue;
            }
            if ((int) ($product['is_active'] ?? 0) !== 1) {
                $errors["items.$index.product_public_id"] = 'Selected product is inactive.';
                continue;
            }
            if ((int) ($product['quantity'] ?? 0) < $quantity) {
                $errors["items.$index.quantity"] = 'Insufficient stock for selected product.';
                continue;
            }

            $priceAtSale = (float) ($product['selling_price'] ?? 0);
            $subtotal += $priceAtSale * $quantity;
            $preparedItems[] = [
                'product_id' => (int) ($product['productID'] ?? 0),
                'product_public_id' => $productPublicId,
                'product_name' => asString($product['product_name'] ?? ''),
                'quantity' => $quantity,
                'price' => $priceAtSale,
            ];
        }

        if ($errors !== []) {
            validationFail($errors);
        }

        $salePublicId = randomPublicId('SL');
        $paymentPublicId = (isValidPublicId($paymentPublicIdInput) && str_starts_with($paymentPublicIdInput, 'PY-'))
            ? $paymentPublicIdInput
            : randomPublicId('PY');

        $shippingName = asString($body['shipping_name'] ?? trim(asString($customer['first_name'] ?? '') . ' ' . asString($customer['last_name'] ?? '')));
        $shippingStreet = asString($body['shipping_street'] ?? asString($customer['street_address'] ?? ''));
        $shippingBarangay = asString($body['shipping_barangay'] ?? asString($customer['barangay'] ?? ''));
        $shippingCity = asString($body['shipping_city_municipality'] ?? asString($customer['city_municipality'] ?? ''));
        $shippingProvince = asString($body['shipping_province'] ?? asString($customer['province'] ?? ''));
        $shippingZip = asString($body['shipping_zip_code'] ?? asString($customer['zip_code'] ?? ''));

        try {
            $pdo->beginTransaction();
            $lockingItems = $preparedItems;
            usort($lockingItems, static function (array $left, array $right): int {
                return ((int) ($left['product_id'] ?? 0)) <=> ((int) ($right['product_id'] ?? 0));
            });
            foreach ($lockingItems as $lockingItem) {
                $lockedProduct = lockProductRowById($pdo, (int) ($lockingItem['product_id'] ?? 0));
                if (!is_array($lockedProduct)) {
                    throw new RuntimeException('Selected product is no longer available.');
                }
                if ((int) ($lockedProduct['is_active'] ?? 0) !== 1) {
                    throw new RuntimeException('Selected product is inactive.');
                }
                if ((int) ($lockedProduct['quantity'] ?? 0) < (int) ($lockingItem['quantity'] ?? 0)) {
                    throw new RuntimeException(
                        'Insufficient stock for ' . asString($lockingItem['product_name'] ?? 'selected product') . '.'
                    );
                }
            }

            $recordSale = $pdo->prepare(
                'CALL record_sale(
                    :public_id,
                    :customerID,
                    :employeeID,
                    :fulfillment_method,
                    :shipping_name,
                    :shipping_street,
                    :shipping_barangay,
                    :shipping_city_municipality,
                    :shipping_province,
                    :shipping_zip_code
                )'
            );
            $recordSale->execute([
                'public_id' => $salePublicId,
                'customerID' => (int) ($customer['customerID'] ?? 0),
                'employeeID' => (int) ($employee['employeeID'] ?? 0),
                'fulfillment_method' => $fulfillment,
                'shipping_name' => $shippingName !== '' ? $shippingName : null,
                'shipping_street' => $shippingStreet !== '' ? $shippingStreet : null,
                'shipping_barangay' => $shippingBarangay !== '' ? $shippingBarangay : null,
                'shipping_city_municipality' => $shippingCity !== '' ? $shippingCity : null,
                'shipping_province' => $shippingProvince !== '' ? $shippingProvince : null,
                'shipping_zip_code' => $shippingZip !== '' ? $shippingZip : null,
            ]);
            $recordSale->closeCursor();

            if ($shippingName !== '' || $shippingStreet !== '' || $shippingBarangay !== '' || $shippingCity !== '') {
                $updateSaleShipping = $pdo->prepare(
                    'CALL sale_shipping_update(
                        :public_id,
                        :shipping_name,
                        :shipping_street,
                        :shipping_barangay,
                        :shipping_city_municipality,
                        :shipping_province,
                        :shipping_zip_code
                    )'
                );
                $updateSaleShipping->execute([
                    'public_id' => $salePublicId,
                    'shipping_name' => $shippingName !== '' ? $shippingName : null,
                    'shipping_street' => $shippingStreet !== '' ? $shippingStreet : null,
                    'shipping_barangay' => $shippingBarangay !== '' ? $shippingBarangay : null,
                    'shipping_city_municipality' => $shippingCity !== '' ? $shippingCity : null,
                    'shipping_province' => $shippingProvince !== '' ? $shippingProvince : null,
                    'shipping_zip_code' => $shippingZip !== '' ? $shippingZip : null,
                ]);
                $updateSaleShipping->closeCursor();
            }

            $recordSaleItem = $pdo->prepare(
                'CALL record_sale_item(
                    :product_public_id,
                    :sale_public_id,
                    :quantity,
                    :price,
                    :serialnum
                )'
            );
            foreach ($preparedItems as $preparedItem) {
                $recordSaleItem->execute([
                    'product_public_id' => $preparedItem['product_public_id'],
                    'sale_public_id' => $salePublicId,
                    'quantity' => $preparedItem['quantity'],
                    'price' => $preparedItem['price'],
                    'serialnum' => null,
                ]);
                $recordSaleItem->closeCursor();
            }

            $recordPayment = $pdo->prepare(
                'CALL record_payment(
                    :amount,
                    :payment_method,
                    :payment_status,
                    :public_id,
                    :sale_public_id,
                    :return_public_id
                )'
            );
            $recordPayment->execute([
                'amount' => round($subtotal, 2),
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'public_id' => $paymentPublicId,
                'sale_public_id' => $salePublicId,
                'return_public_id' => null,
            ]);
            $recordPayment->closeCursor();

            if ($paymentMethod === 'Store Credit' && $paymentStatus === 'Completed') {
                ensureStoreCreditPurchaseEntry($pdo, $customerPublicId, $salePublicId, round($subtotal, 2));
            }

            $pdo->commit();
        } catch (Throwable $e) {
            rollbackIfInTransaction($pdo);
            if ($e instanceof PDOException) {
                validationFail(['sale' => adminSqlErrorMessage($e, 'Unable to create sale.')]);
            }
            $message = trim($e->getMessage());
            validationFail(['sale' => $message !== '' ? $message : 'Unable to create sale.']);
        }
        $saleDateForMongo = '';
        $saleUpdatedAtForMongo = '';
        $saleTimestampStmt = $pdo->prepare(
            'SELECT sale_date, updated_at
             FROM api_sale
             WHERE public_id = :public_id
             LIMIT 1'
        );
        $saleTimestampStmt->execute(['public_id' => $salePublicId]);
        $saleTimestampRow = $saleTimestampStmt->fetch();
        if (is_array($saleTimestampRow)) {
            $saleDateForMongo = asString($saleTimestampRow['sale_date'] ?? '');
            $saleUpdatedAtForMongo = asString($saleTimestampRow['updated_at'] ?? '');
        }
        if ($saleUpdatedAtForMongo === '') {
            $saleUpdatedAtForMongo = $saleDateForMongo;
        }

        $snapshotPaymentStatus = $paymentStatus === 'Completed'
            ? 'Paid'
            : ($paymentStatus === 'Failed' ? 'Failed' : 'Pending');
        $snapshotOrderStatus = $fulfillment === 'Walk-in' ? 'Completed' : 'Pending';
        $snapshotRemarks = $snapshotOrderStatus === 'Completed'
            ? 'Walk-in transaction recorded by admin.'
            : 'Order recorded by admin.';

        $catalogMap = catalogMapByPublicId(loadProductCatalog($env));
        $mongoItems = [];
        foreach ($preparedItems as $preparedItem) {
            $catalogItem = $catalogMap[$preparedItem['product_public_id']] ?? [];
            $mongoItems[] = [
                'product_public_id' => $preparedItem['product_public_id'],
                'product_name' => $preparedItem['product_name'],
                'quantity' => (int) $preparedItem['quantity'],
                'price_at_sale' => (float) $preparedItem['price'],
                'image_url' => asString($catalogItem['image_url'] ?? ''),
            ];
        }

        upsertMongoOrderSnapshot(
            $env,
            $salePublicId,
            $customerPublicId,
            $mongoItems,
            [
                'subtotal' => round($subtotal, 2),
                'grand_total' => round($subtotal, 2),
                'payment_method' => $paymentMethod,
                'payment_status' => $snapshotPaymentStatus,
                'transaction_reference' => $paymentPublicId,
            ],
            [
                'full_name' => $shippingName,
                'phone' => asString($customer['contact_number'] ?? ''),
                'email' => asString($customer['email_address'] ?? ''),
                'street_address' => $shippingStreet,
                'barangay' => $shippingBarangay,
                'city_municipality' => $shippingCity,
                'province' => $shippingProvince,
                'zip_code' => $shippingZip,
            ],
            $snapshotOrderStatus,
            asString($claims['sub'] ?? ''),
            $snapshotRemarks,
            null,
            null,
            $saleDateForMongo,
            $saleUpdatedAtForMongo
        );

        appendAuditLog($env, 'ORDER_LOG', 'SALE_CREATED_BY_ADMIN', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'Sale',
            'resource_public_id' => $salePublicId,
        ], [
            'customer_public_id' => $customerPublicId,
            'employee_public_id' => $resolvedEmployeePublicId,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
        ]);

        sendJson(201, [
            'ok' => true,
            'message' => 'Sale created successfully.',
            'data' => [
                'sale_public_id' => $salePublicId,
                'total_amount' => round($subtotal, 2),
                'payment_reference' => $paymentPublicId,
            ],
        ]);
    }

    if (preg_match('#^/admin/inquiries/([^/]+)/reply$#', $path, $matches) === 1 && $method === 'PUT') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $manager = mongoManager($env);
        if (!$manager) {
            sendJson(500, ['ok' => false, 'message' => 'Inquiry service is unavailable.']);
        }

        $inquiryId = asString($matches[1] ?? '');
        if ($inquiryId === '') {
            validationFail(['inquiry' => 'Invalid inquiry reference.']);
        }

        $body = requestBody($env);
        $message = trim(asString($body['message'] ?? ''));
        $status = asString($body['status'] ?? 'Responded');
        $allowedStatuses = ['Pending', 'In Progress', 'Responded', 'Resolved', 'Closed'];

        if ($message !== '') {
            $messageError = validateTextLength($message, 'Reply message', 2, 2000);
            if ($messageError !== null) {
                validationFail(['message' => $messageError]);
            }
        }
        if (!in_array($status, $allowedStatuses, true)) {
            validationFail(['status' => 'Invalid inquiry status selected.']);
        }

        $dbName = mongoDbName($env);
        $collection = mongoCollectionName($env, 'customer_inquiry');

        $filters = [];
        $objectId = mongoObjectIdFromValue($inquiryId);
        if ($objectId instanceof MongoDB\BSON\ObjectId) {
            $filters[] = ['_id' => $objectId];
        }
        $filters[] = ['_id' => $inquiryId];

        $inquiry = null;
        $selectedFilter = null;
        foreach ($filters as $filter) {
            $found = mongoFindOne($manager, $dbName, $collection, $filter);
            if (is_array($found)) {
                $inquiry = $found;
                $selectedFilter = $filter;
                break;
            }
        }

        if (!is_array($inquiry) || !is_array($selectedFilter)) {
            sendJson(404, ['ok' => false, 'message' => 'Inquiry not found.']);
        }

        $timestamp = mongoTimestamp();
        $senderPublicId = asString($claims['sub'] ?? '');

        try {
            mongoUpdateOne(
                $manager,
                $dbName,
                $collection,
                $selectedFilter,
                [
                    '$set' => [
                        'status' => $status,
                        'updated_at' => $timestamp,
                        'last_message_at' => $timestamp,
                    ],
                    '$inc' => [
                        'message_count' => 1,
                    ],
                ],
                false
            );
        } catch (Throwable) {
            sendJson(500, ['ok' => false, 'message' => 'Unable to save inquiry response right now.']);
        }

        appendAuditLog($env, 'CUSTOMER_INQUIRY_LOG', 'ADMIN_INQUIRY_RESPONSE', [
            'actor_type' => 'ADMIN',
            'public_id' => $senderPublicId,
        ], [
            'resource_type' => 'CustomerInquiry',
            'resource_public_id' => $inquiryId,
        ], [
            'new_state' => $status,
        ]);

        sendJson(200, ['ok' => true, 'message' => 'Inquiry status updated.']);
    }

    if ($path === '/admin/customers' && $method === 'POST') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $body = requestBody($env);

        $firstName = asString($body['first_name'] ?? '');
        $middleName = asString($body['middle_name'] ?? '');
        $lastName = asString($body['last_name'] ?? '');
        $email = strtolower(asString($body['email_address'] ?? ''));
        $contactRaw = asString($body['contact_number'] ?? '');
        $street = asString($body['street_address'] ?? '');
        $barangay = asString($body['barangay'] ?? '');
        $city = asString($body['city_municipality'] ?? '');
        $province = asString($body['province'] ?? '');
        $zip = asString($body['zip_code'] ?? '');
        $contact = $contactRaw !== '' ? normalizePhoneNumber($contactRaw) : null;

        $errors = [];
        if ($firstName === '') {
            $errors['first_name'] = 'First name is required.';
        } else {
            $firstNameError = validateHumanName($firstName, 'First name');
            if ($firstNameError !== null) $errors['first_name'] = $firstNameError;
        }
        if ($middleName !== '') {
            $middleNameError = validateHumanName($middleName, 'Middle name');
            if ($middleNameError !== null) $errors['middle_name'] = $middleNameError;
        }
        if ($lastName === '') {
            $errors['last_name'] = 'Last name is required.';
        } else {
            $lastNameError = validateHumanName($lastName, 'Last name');
            if ($lastNameError !== null) $errors['last_name'] = $lastNameError;
        }
        if ($email !== '' && !validateEmail($email)) {
            $errors['email_address'] = 'Please enter a valid email address.';
        }
        if ($contactRaw !== '' && $contact === null) {
            $errors['contact_number'] = 'Please enter a valid contact number (example: 09171234567 or 639171234567).';
        }
        if ($email === '' && $contact === null) {
            $errors['contact'] = 'Please provide at least an email address or contact number.';
        }
        if ($barangay === '') {
            $errors['barangay'] = 'Barangay is required.';
        } else {
            $barangayError = validateTextLength($barangay, 'Barangay', 2, 50);
            if ($barangayError !== null) $errors['barangay'] = $barangayError;
        }
        if ($city === '') {
            $errors['city_municipality'] = 'City/Municipality is required.';
        } else {
            $cityError = validateTextLength($city, 'City/Municipality', 2, 50);
            if ($cityError !== null) $errors['city_municipality'] = $cityError;
        }
        if ($zip !== '' && !validateZipCode($zip)) {
            $errors['zip_code'] = 'Please enter a valid 4-digit zip code.';
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        $customerPublicId = randomPublicId('CS');
        try {
            $proc = $pdo->prepare(
                'CALL add_customer(
                    :public_id, :first_name, :middle_name, :last_name,
                    :email_address, :contact_number, :street_address, :barangay,
                    :province, :city_municipality, :zip_code
                )'
            );
            $proc->execute([
                'public_id' => $customerPublicId,
                'first_name' => $firstName,
                'middle_name' => $middleName !== '' ? $middleName : null,
                'last_name' => $lastName,
                'email_address' => $email !== '' ? $email : null,
                'contact_number' => $contact,
                'street_address' => $street !== '' ? $street : null,
                'barangay' => $barangay,
                'province' => $province !== '' ? $province : null,
                'city_municipality' => $city,
                'zip_code' => $zip !== '' ? $zip : null,
            ]);
            $proc->closeCursor();
        } catch (PDOException $e) {
            validationFail(['customer' => adminSqlErrorMessage($e, 'Unable to create customer.')]);
        }

        appendAuditLog($env, 'USER_ACCOUNT_LOG', 'CUSTOMER_CREATED_BY_ADMIN', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'CustomerProfile',
            'resource_public_id' => $customerPublicId,
        ]);

        sendJson(201, [
            'ok' => true,
            'message' => 'Customer added successfully.',
            'data' => ['public_id' => $customerPublicId],
        ]);
    }

    if ($path === '/admin/restock' && $method === 'POST') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $body = requestBody($env);

        $productPublicId = strtoupper(asString($body['product_public_id'] ?? ''));
        $supplierPublicId = strtoupper(asString($body['supplier_public_id'] ?? ''));
        $employeePublicId = strtoupper(asString($claims['sub'] ?? ''));
        $quantity = (int) ($body['quantity'] ?? 0);

        $errors = [];
        if ($productPublicId === '' || !isValidPublicId($productPublicId)) {
            $errors['product_public_id'] = 'Please select a valid product.';
        }
        if ($supplierPublicId === '' || !isValidPublicId($supplierPublicId)) {
            $errors['supplier_public_id'] = 'Please select a valid supplier.';
        }
        if ($quantity < 1) {
            $errors['quantity'] = 'Quantity must be at least 1.';
        }
        if ($errors !== []) {
            validationFail($errors);
        }
        if ($employeePublicId === '' || !isValidPublicId($employeePublicId)) {
            sendJson(403, ['ok' => false, 'message' => 'Authenticated employee account is invalid.']);
        }

        $product = mysqlProductByPublicId($pdo, $productPublicId);
        if (!is_array($product)) {
            sendJson(404, ['ok' => false, 'message' => 'Product not found.']);
        }

        $supplierStmt = $pdo->prepare('SELECT supplierID FROM api_supplier WHERE public_id = :public_id AND is_active = 1 LIMIT 1');
        $supplierStmt->execute(['public_id' => $supplierPublicId]);
        $supplier = $supplierStmt->fetch();
        if (!is_array($supplier)) {
            validationFail(['supplier_public_id' => 'Selected supplier was not found or is inactive.']);
        }

        $employee = getEmployeeByPublicId($pdo, $employeePublicId);
        if (!is_array($employee)) {
            sendJson(403, ['ok' => false, 'message' => 'Authenticated employee record was not found.']);
        }
        if (strcasecmp(asString($employee['employee_status'] ?? ''), 'Active') !== 0) {
            sendJson(403, ['ok' => false, 'message' => 'Authenticated employee account is inactive.']);
        }

        try {
            $proc = $pdo->prepare(
                'CALL restock_product_secure(
                    :product_public_id,
                    :quantity,
                    :employee_public_id,
                    :supplier_public_id
                )'
            );
            $proc->execute([
                'product_public_id' => $productPublicId,
                'quantity' => $quantity,
                'employee_public_id' => $employeePublicId,
                'supplier_public_id' => $supplierPublicId,
            ]);
            $proc->closeCursor();
        } catch (PDOException $e) {
            rollbackIfInTransaction($pdo);
            validationFail(['restock' => adminSqlErrorMessage($e, 'Unable to restock product.')]);
        }

        syncMongoProductCatalogFromMysql($env, $pdo, $productPublicId, [
            'allow_insert' => false,
        ]);

        appendAuditLog($env, 'INVENTORY_LOG', 'PRODUCT_RESTOCK', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'ProductCatalog',
            'resource_public_id' => $productPublicId,
        ], [
            'quantity_added' => $quantity,
            'supplier_public_id' => $supplierPublicId,
        ]);

        sendJson(200, ['ok' => true, 'message' => 'Product restocked successfully.']);
    }

      if ($path === '/admin/orders' && $method === 'GET') {
          requireAuth($env, ['admin', 'employee']);
        $dateRange = adminNormalizedDateRangeQuery();
        $whereParts = [];
        $params = [];
        if ($dateRange['start_sql'] !== null) {
            $whereParts[] = 's.sale_date >= :start_date';
            $params['start_date'] = $dateRange['start_sql'];
        }
        if ($dateRange['end_sql'] !== null) {
            $whereParts[] = 's.sale_date <= :end_date';
            $params['end_date'] = $dateRange['end_sql'];
        }

        $sql = 'SELECT s.saleID, s.public_id, s.sale_date, s.total_amount,
                    CASE
                        WHEN s.fulfillment_method = "Walk-in"
                             AND s.sale_status <> "Cancelled"
                        THEN "Completed"
                        ELSE s.sale_status
                    END AS sale_status,
                    s.fulfillment_method, s.tracking_number, s.courier_name,
                    s.shipping_name, s.shipping_street, s.shipping_barangay, s.shipping_city_municipality, s.shipping_province, s.shipping_zip_code,
                    pay.payment_method, pay.payment_status, pay.public_id AS payment_public_id,
                    COALESCE(NULLIF(pay.public_id, ""), s.public_id) AS payment_reference,
                    c.public_id AS customer_public_id,
                    CONCAT(c.first_name, " ", c.last_name) AS customer_name,
                    c.email_address,
                    CONCAT_WS(", ",
                        NULLIF(TRIM(s.shipping_street), ""),
                        NULLIF(TRIM(s.shipping_barangay), ""),
                        NULLIF(TRIM(s.shipping_city_municipality), ""),
                        NULLIF(TRIM(s.shipping_province), ""),
                        NULLIF(TRIM(s.shipping_zip_code), "")
                    ) AS shipping_address
             FROM api_sale s
             JOIN api_customer c ON c.customerID = s.customerID
             LEFT JOIN (
                SELECT p.saleID, p.payment_method, p.payment_status, p.public_id
                FROM api_payment p
                JOIN (
                    SELECT saleID, MAX(paymentID) AS latest_payment_id
                    FROM api_payment
                    WHERE saleID IS NOT NULL
                    GROUP BY saleID
                ) latest ON latest.latest_payment_id = p.paymentID
             ) pay ON pay.saleID = s.saleID
             ' . ($whereParts !== [] ? ('WHERE ' . implode(' AND ', $whereParts)) : '') . '
             ORDER BY s.sale_date DESC';
        $ordersStmt = $pdo->prepare($sql);
        $ordersStmt->execute($params);
        $rawRows = $ordersStmt->fetchAll();

        $rows = [];
        foreach ($rawRows as $row) {
            $row['allowed_next_statuses'] = adminAllowedOrderStatuses(
                asString($row['sale_status'] ?? ''),
                asString($row['fulfillment_method'] ?? '')
            );
            $rows[] = $row;
        }

        sendJson(200, ['ok' => true, 'data' => ['orders' => $rows]]);
    }

    if (preg_match('#^/admin/orders/([A-Za-z0-9\-]+)/status$#', $path, $matches) === 1 && $method === 'PUT') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $publicId = $matches[1];
        if (!isValidPublicId($publicId)) {
            validationFail(['order' => 'Invalid order reference format.']);
        }
        $body = requestBody($env);
        $status = asString($body['status'] ?? '');
        $trackingNumber = asString($body['tracking_number'] ?? '');
        $courierName = asString($body['courier_name'] ?? '');
        $allowed = ['Pending', 'Processing', 'Ready for Pickup', 'Shipped', 'Delivered', 'Completed', 'Cancelled'];
        if (!in_array($status, $allowed, true)) {
            validationFail(['status' => 'Invalid order status selected.']);
        }

        $saleStmt = $pdo->prepare(
            'SELECT s.saleID, s.sale_status, s.fulfillment_method, s.tracking_number, s.courier_name,
                    c.public_id AS customer_public_id
                    ,pay.paymentID AS payment_id, pay.public_id AS payment_public_id, pay.payment_method, pay.payment_status
             FROM api_sale s
             JOIN api_customer c ON c.customerID = s.customerID
             LEFT JOIN (
                SELECT p.paymentID, p.saleID, p.public_id, p.payment_method, p.payment_status
                FROM api_payment p
                JOIN (
                    SELECT saleID, MAX(paymentID) AS latest_payment_id
                    FROM api_payment
                    WHERE saleID IS NOT NULL
                    GROUP BY saleID
                ) latest ON latest.latest_payment_id = p.paymentID
             ) pay ON pay.saleID = s.saleID
             WHERE s.public_id = :public_id
             LIMIT 1'
        );
        $saleStmt->execute(['public_id' => $publicId]);
        $sale = $saleStmt->fetch();
        if (!is_array($sale)) {
            sendJson(404, ['ok' => false, 'message' => 'Order not found.']);
        }

        $employee = getEmployeeByPublicId($pdo, (string) $claims['sub']);
        if ($employee === null) {
            sendJson(404, ['ok' => false, 'message' => 'Admin employee profile not found.']);
        }

        $currentStatus = asString($sale['sale_status'] ?? '');
        $fulfillmentMethod = asString($sale['fulfillment_method'] ?? '');
        $allowedTransitions = adminAllowedOrderStatuses($currentStatus, $fulfillmentMethod);
        if (!in_array($status, $allowedTransitions, true)) {
            validationFail([
                'status' => 'Invalid status transition for this order. Allowed statuses: ' . implode(', ', $allowedTransitions),
            ]);
        }
        if (in_array($currentStatus, ['Completed', 'Cancelled'], true) && $status !== $currentStatus) {
            validationFail(['status' => 'Finalized or cancelled orders cannot be changed.']);
        }
        if ($currentStatus === 'Shipped' && $status === 'Pending') {
            validationFail(['status' => 'Shipped orders cannot be reverted to pending.']);
        }
        if (in_array($status, ['Shipped', 'Delivered'], true)) {
            if ($trackingNumber === '') {
                validationFail(['tracking_number' => 'Tracking number is required when marking an order as shipped or delivered.']);
            }
            if ($courierName === '') {
                validationFail(['courier_name' => 'Courier name is required when marking an order as shipped or delivered.']);
            }
        }
        if ($fulfillmentMethod === 'Pickup' && ($trackingNumber !== '' || $courierName !== '')) {
            validationFail(['tracking_number' => 'Pickup orders must not include tracking or courier details.']);
        }
        $statusTimestamp = '';
        $trackingToSave = '';
        $courierToSave = '';
        $paymentMethod = asString($sale['payment_method'] ?? '');
        $paymentStatus = asString($sale['payment_status'] ?? '');
        $paymentId = (int) ($sale['payment_id'] ?? 0);
        $isCashOnDelivery = in_array(strtolower(trim($paymentMethod)), ['cash', 'cash on delivery', 'cod'], true);

        try {
            $update = $pdo->prepare(
                'CALL order_status_update_full(
                    :sale_public_id,
                    :sale_status,
                    :tracking_number,
                    :courier_name,
                    :employee_public_id
                )'
            );
            $update->execute([
                'sale_public_id' => $publicId,
                'sale_status' => $status,
                'tracking_number' => $trackingNumber !== '' ? $trackingNumber : null,
                'courier_name' => $courierName !== '' ? $courierName : null,
                'employee_public_id' => (string) ($employee['public_id'] ?? ''),
            ]);
            $update->closeCursor();

            $statusTimestampStmt = $pdo->prepare(
                'SELECT updated_at
                 FROM api_sale
                 WHERE public_id = :public_id
                 LIMIT 1'
            );
            $statusTimestampStmt->execute(['public_id' => $publicId]);
            $statusTimestampRow = $statusTimestampStmt->fetch();
            if (is_array($statusTimestampRow)) {
                $statusTimestamp = asString($statusTimestampRow['updated_at'] ?? '');
            }
        } catch (Throwable $e) {
            rollbackIfInTransaction($pdo);
            if ($e instanceof PDOException) {
                validationFail(['status' => adminSqlErrorMessage($e, 'Unable to update order status.')]);
            }
            $message = trim($e->getMessage());
            validationFail(['status' => $message !== '' ? $message : 'Unable to update order status.']);
        }

        $saleAfter = $pdo->prepare(
            'SELECT tracking_number, courier_name
             FROM api_sale
             WHERE public_id = :public_id
             LIMIT 1'
        );
        $saleAfter->execute(['public_id' => $publicId]);
        $saleAfterRow = $saleAfter->fetch();
        if (is_array($saleAfterRow)) {
            $trackingToSave = asString($saleAfterRow['tracking_number'] ?? '');
            $courierToSave = asString($saleAfterRow['courier_name'] ?? '');
        }

        $latestPaymentStatus = $paymentStatus;
        $latestPaymentMethod = $paymentMethod;
        if ($paymentId > 0) {
            $paymentAfter = $pdo->prepare(
                'SELECT payment_status, payment_method
                 FROM payment
                 WHERE paymentID = :payment_id
                 LIMIT 1'
            );
            $paymentAfter->execute(['payment_id' => $paymentId]);
            $paymentAfterRow = $paymentAfter->fetch();
            if (is_array($paymentAfterRow)) {
                $latestPaymentStatus = asString($paymentAfterRow['payment_status'] ?? $latestPaymentStatus);
                $latestPaymentMethod = asString($paymentAfterRow['payment_method'] ?? $latestPaymentMethod);
            }
        }

        $statusMessage = orderStatusMessage($status, $trackingToSave, $courierToSave, $fulfillmentMethod);
        $transition = [
            'previous_state' => $currentStatus,
            'new_state' => $status,
        ];
        if (
            $isCashOnDelivery
            && strcasecmp($paymentStatus, 'Completed') !== 0
            && strcasecmp($latestPaymentStatus, 'Completed') === 0
        ) {
            $transition['payment_status_synced'] = 'Completed';
        }
        if ($status === 'Cancelled' && strcasecmp($currentStatus, 'Cancelled') !== 0) {
            $transition['payment_status_synced'] = 'Cancelled';

            $inventoryCheck = $pdo->prepare(
                'SELECT 1
                 FROM inventory_transaction it
                 JOIN sale s ON s.saleID = it.referenceID
                 WHERE s.public_id = :public_id
                   AND it.transaction_type = "Cancelled Sale"
                 LIMIT 1'
            );
            $inventoryCheck->execute(['public_id' => $publicId]);
            if ($inventoryCheck->fetchColumn() !== false) {
                $transition['inventory_restocked'] = true;
                $transition['inventory_transaction_type'] = 'Cancelled Sale';
            }

            if (strcasecmp($latestPaymentMethod, 'Store Credit') === 0) {
                $creditCheck = $pdo->prepare(
                    'SELECT ch.amount
                     FROM credit_history ch
                     JOIN sale s ON s.saleID = ch.reference_id
                     WHERE s.public_id = :public_id
                       AND ch.transaction_type = "ADJUSTMENT"
                       AND ch.amount > 0
                     ORDER BY ch.credit_transactionID DESC
                     LIMIT 1'
                );
                $creditCheck->execute(['public_id' => $publicId]);
                $creditRow = $creditCheck->fetch();
                if (is_array($creditRow)) {
                    $transition['store_credit_refunded'] = true;
                    $transition['refunded_amount'] = round((float) ($creditRow['amount'] ?? 0), 2);
                }
            }
        }
        if (in_array($status, ['Shipped', 'Delivered'], true)) {
            $transition['tracking_number'] = $trackingToSave;
        }

        $clearLogistics = in_array($status, ['Pending', 'Processing', 'Ready for Pickup', 'Cancelled'], true);

        $statusUpdatedBy = trim(asString($employee['first_name'] ?? '') . ' ' . asString($employee['last_name'] ?? ''));
        if ($statusUpdatedBy === '') {
            $statusUpdatedBy = asString($employee['public_id'] ?? ($claims['sub'] ?? ''));
        }
        if ($statusUpdatedBy === '') {
            $statusUpdatedBy = 'System';
        }

        appendAuditLog($env, 'ORDER_LOG', 'ORDER_STATUS_UPDATE', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'Sale',
            'resource_public_id' => $publicId,
        ], $transition);

        upsertMongoOrderStatus(
            $env,
            $publicId,
            asString($sale['customer_public_id'] ?? ''),
            $status,
            $statusUpdatedBy,
            $statusMessage,
            in_array($status, ['Shipped', 'Delivered'], true) ? $trackingToSave : null,
            in_array($status, ['Shipped', 'Delivered'], true) ? $courierToSave : null,
            $clearLogistics,
            $statusTimestamp
        );
        if ($codPaymentSynced) {
            upsertMongoOrderPaymentStatus(
                $env,
                $publicId,
                asString($sale['customer_public_id'] ?? ''),
                'Completed',
                $statusUpdatedBy,
                'Cash on delivery payment marked completed after order status update.',
                $paymentMethod,
                asString($sale['payment_public_id'] ?? '')
            );
        }
        if ($cancellationEffects['payment_cancelled']) {
            upsertMongoOrderPaymentStatus(
                $env,
                $publicId,
                asString($sale['customer_public_id'] ?? ''),
                'Cancelled',
                $statusUpdatedBy,
                $cancellationEffects['store_credit_refunded']
                    ? 'Store credit refunded after order cancellation.'
                    : 'Payment marked cancelled after order cancellation.',
                asString($cancellationEffects['payment_method'] ?? '') !== ''
                    ? asString($cancellationEffects['payment_method'] ?? '')
                    : null,
                asString($cancellationEffects['payment_public_id'] ?? '') !== ''
                    ? asString($cancellationEffects['payment_public_id'] ?? '')
                    : null
            );
        }

        sendJson(200, ['ok' => true, 'message' => 'Order status updated.']);
    }

    if ($path === '/admin/users' && $method === 'GET') {
        requireAuth($env, ['admin', 'employee']);

        $rows = $pdo->query(
            'SELECT c.public_id, c.first_name, c.middle_name, c.last_name, c.email_address, c.contact_number,
                    c.street_address, c.barangay, c.city_municipality, c.province, c.zip_code,
                    c.customer_type, c.status AS customer_status, c.deleted_at, c.created_at, c.updated_at, c.is_active,
                    COUNT(s.saleID) AS total_orders,
                    COALESCE(SUM(s.total_amount), 0) AS total_spent
             FROM api_customer c
             LEFT JOIN api_sale s ON s.customerID = c.customerID
             GROUP BY c.customerID
             ORDER BY c.created_at DESC'
        )->fetchAll();

        sendJson(200, ['ok' => true, 'data' => ['users' => $rows]]);
    }

    if (preg_match('#^/admin/users/([A-Za-z0-9\-]+)/status$#', $path, $matches) === 1 && $method === 'PATCH') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $publicId = $matches[1];
        if (!isValidPublicId($publicId)) {
            validationFail(['user' => 'Invalid user reference format.']);
        }
        $body = requestBody($env);
        $isActive = parseBoolean($body['is_active'] ?? null);
        if ($isActive === null) {
            validationFail(['is_active' => 'Please provide a boolean value for is_active.']);
        }

        if (getCustomerByPublicIdAny($pdo, $publicId) === null) {
            sendJson(404, ['ok' => false, 'message' => 'User not found.']);
        }

        $stmt = $pdo->prepare(
            'CALL update_customer_master(
                :public_id, :first_name, :last_name, :middle_name, :email, :contact,
                :street, :barangay, :city, :province, :zip, :status, :deleted_at
            )'
        );
        $stmt->execute([
            'public_id' => $publicId,
            'first_name' => null,
            'last_name' => null,
            'middle_name' => null,
            'email' => null,
            'contact' => null,
            'street' => null,
            'barangay' => null,
            'city' => null,
            'province' => null,
            'zip' => null,
            'status' => 'Active',
            'deleted_at' => $isActive ? null : nowUtc(),
        ]);
        $stmt->closeCursor();

        appendAuditLog($env, 'USER_ACCOUNT_LOG', 'CUSTOMER_STATUS_UPDATE', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'CustomerProfile',
            'resource_public_id' => $publicId,
        ], [
            'new_state' => $isActive ? 'Active' : 'Admin Deactivated',
        ]);

        sendJson(200, ['ok' => true, 'message' => 'User status updated.']);
    }

    if (preg_match('#^/admin/users/([A-Za-z0-9\-]+)$#', $path, $matches) === 1 && $method === 'PUT') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $publicId = $matches[1];
        if (!isValidPublicId($publicId)) {
            validationFail(['user' => 'Invalid user reference format.']);
        }

        if (getCustomerByPublicIdAny($pdo, $publicId) === null) {
            sendJson(404, ['ok' => false, 'message' => 'User not found.']);
        }

        $body = requestBody($env);
        $firstName = asString($body['first_name'] ?? '');
        $middleName = asString($body['middle_name'] ?? '');
        $lastName = asString($body['last_name'] ?? '');
        $email = strtolower(asString($body['email_address'] ?? ''));
        $contactRaw = asString($body['contact_number'] ?? '');
        $street = asString($body['street_address'] ?? '');
        $barangay = asString($body['barangay'] ?? '');
        $city = asString($body['city_municipality'] ?? '');
        $province = asString($body['province'] ?? '');
        $zip = asString($body['zip_code'] ?? '');
        $status = asString($body['status'] ?? '');
        $deletedAt = asString($body['deleted_at'] ?? '');
        $contactNormalized = $contactRaw !== '' ? normalizePhoneNumber($contactRaw) : '';

        $errors = [];
        if ($firstName !== '') {
            $firstNameError = validateHumanName($firstName, 'First name');
            if ($firstNameError !== null) {
                $errors['first_name'] = $firstNameError;
            }
        }
        if ($middleName !== '') {
            $middleNameError = validateHumanName($middleName, 'Middle name');
            if ($middleNameError !== null) {
                $errors['middle_name'] = $middleNameError;
            }
        }
        if ($lastName !== '') {
            $lastNameError = validateHumanName($lastName, 'Last name');
            if ($lastNameError !== null) {
                $errors['last_name'] = $lastNameError;
            }
        }
        if ($email !== '' && !validateEmail($email)) {
            $errors['email_address'] = 'Please enter a valid email address.';
        }
        if ($contactRaw !== '' && $contactNormalized === null) {
            $errors['contact_number'] = 'Please enter a valid phone number (example: 09171234567 or 639171234567).';
        }
        if ($zip !== '' && !validateZipCode($zip)) {
            $errors['zip_code'] = 'Please enter a valid 4-digit zip code.';
        }
        if ($status !== '' && !in_array($status, ['Active', 'Merged', 'Deleted_by_User'], true)) {
            $errors['status'] = 'Invalid customer status.';
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        try {
            $stmt = $pdo->prepare(
                'CALL update_customer_master(
                    :public_id, :first_name, :last_name, :middle_name, :email, :contact,
                    :street, :barangay, :city, :province, :zip, :status, :deleted_at
                )'
            );
            $stmt->execute([
                'public_id' => $publicId,
                'first_name' => $firstName !== '' ? $firstName : null,
                'last_name' => $lastName !== '' ? $lastName : null,
                'middle_name' => $middleName !== '' ? $middleName : null,
                'email' => $email !== '' ? $email : null,
                'contact' => $contactNormalized !== '' ? $contactNormalized : null,
                'street' => $street !== '' ? $street : null,
                'barangay' => $barangay !== '' ? $barangay : null,
                'city' => $city !== '' ? $city : null,
                'province' => $province !== '' ? $province : null,
                'zip' => $zip !== '' ? $zip : null,
                'status' => $status !== '' ? $status : null,
                'deleted_at' => $deletedAt !== '' ? $deletedAt : null,
            ]);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            validationFail(['user' => adminSqlErrorMessage($e, 'Unable to update user profile.')]);
        }

        appendAuditLog($env, 'USER_ACCOUNT_LOG', 'CUSTOMER_UPDATED', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'CustomerProfile',
            'resource_public_id' => $publicId,
        ]);

        sendJson(200, ['ok' => true, 'message' => 'User updated successfully.']);
    }

    if (preg_match('#^/admin/sales/([A-Za-z0-9\-]+)/items$#', $path, $matches) === 1 && $method === 'GET') {
        requireAuth($env, ['admin', 'employee']);
        $salePublicId = strtoupper($matches[1]);
        if (!isValidPublicId($salePublicId)) {
            validationFail(['sale' => 'Invalid sale reference format.']);
        }

        $saleStmt = $pdo->prepare('SELECT saleID FROM api_sale WHERE public_id = :public_id LIMIT 1');
        $saleStmt->execute(['public_id' => $salePublicId]);
        $sale = $saleStmt->fetch();
        if (!is_array($sale)) {
            sendJson(404, ['ok' => false, 'message' => 'Sale not found.']);
        }

        $rows = $pdo->prepare(
            'SELECT si.sale_itemID, si.quantity_sold, si.price_at_sale, si.serial_number,
                    p.productID, p.public_id AS product_public_id, p.product_name
             FROM api_sale_item si
             JOIN api_product p ON p.productID = si.productID
             WHERE si.saleID = :saleID
             ORDER BY si.sale_itemID ASC'
        );
        $rows->execute(['saleID' => (int) ($sale['saleID'] ?? 0)]);
        sendJson(200, ['ok' => true, 'data' => ['items' => $rows->fetchAll()]]);
    }

    if ($path === '/admin/returns' && $method === 'POST') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $body = requestBody($env);

        $salePublicId = strtoupper(asString($body['sale_public_id'] ?? ''));
        $employeePublicIdInput = strtoupper(asString($body['employee_public_id'] ?? ''));
        $returnMethod = asString($body['return_method'] ?? 'Drop-off');
        $items = is_array($body['items'] ?? null) ? $body['items'] : [];

        $errors = [];
        if ($salePublicId === '' || !isValidPublicId($salePublicId)) {
            $errors['sale_public_id'] = 'Please select a valid sale.';
        }
        if (!in_array($returnMethod, ['Drop-off', 'Courier'], true)) {
            $errors['return_method'] = 'Invalid return method.';
        }
        if (!is_array($items) || $items === []) {
            $errors['items'] = 'Please add at least one return item.';
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        $saleStmt = $pdo->prepare('SELECT saleID, public_id FROM api_sale WHERE public_id = :public_id LIMIT 1');
        $saleStmt->execute(['public_id' => $salePublicId]);
        $sale = $saleStmt->fetch();
        if (!is_array($sale)) {
            sendJson(404, ['ok' => false, 'message' => 'Sale not found.']);
        }

        $resolvedEmployeePublicId = $employeePublicIdInput;
        if ($resolvedEmployeePublicId === '') {
            $claimsSub = asString($claims['sub'] ?? '');
            $claimsEmployee = $claimsSub !== '' ? getEmployeeByPublicId($pdo, $claimsSub) : null;
            if (is_array($claimsEmployee)) {
                $resolvedEmployeePublicId = asString($claimsEmployee['public_id'] ?? '');
            } else {
                $fallbackEmployeePublicId = firstEmployeePublicId($pdo);
                if ($fallbackEmployeePublicId !== null) {
                    $resolvedEmployeePublicId = $fallbackEmployeePublicId;
                }
            }
        }
        if ($resolvedEmployeePublicId === '' || !isValidPublicId($resolvedEmployeePublicId)) {
            validationFail(['employee_public_id' => 'Please select a valid employee.']);
        }
        $employee = getEmployeeByPublicId($pdo, $resolvedEmployeePublicId);
        if (!is_array($employee)) {
            validationFail(['employee_public_id' => 'Selected employee was not found.']);
        }

        $preparedItems = [];
        $estimatedRefund = 0.0;
        foreach ($items as $index => $rawItem) {
            if (!is_array($rawItem)) {
                $errors['items'] = 'Invalid return item payload.';
                continue;
            }
            $productPublicId = strtoupper(asString($rawItem['product_public_id'] ?? ''));
            $quantity = (int) ($rawItem['quantity'] ?? 0);
            $reason = asString($rawItem['reason'] ?? '');
            $notes = asString($rawItem['notes'] ?? '');
            $serialNumber = asString($rawItem['serial_number'] ?? '');
            $status = 'Pending';

            if ($productPublicId === '' || !isValidPublicId($productPublicId)) {
                $errors["items.$index.product_public_id"] = 'Invalid product reference.';
                continue;
            }
            if ($quantity < 1) {
                $errors["items.$index.quantity"] = 'Quantity must be at least 1.';
                continue;
            }
            if (!in_array($reason, ['Defective', 'Change of Mind'], true)) {
                $errors["items.$index.reason"] = 'Invalid return reason.';
                continue;
            }

            $saleItemStmt = $pdo->prepare(
                'SELECT si.sale_itemID, si.quantity_sold, si.price_at_sale
                 FROM api_sale_item si
                 JOIN api_sale s ON s.saleID = si.saleID
                 JOIN api_product p ON p.productID = si.productID
                 WHERE s.public_id = :sale_public_id
                   AND p.public_id = :product_public_id
                 LIMIT 1'
            );
            $saleItemStmt->execute([
                'sale_public_id' => $salePublicId,
                'product_public_id' => $productPublicId,
            ]);
            $saleItem = $saleItemStmt->fetch();
            if (!is_array($saleItem)) {
                $errors["items.$index.product_public_id"] = 'Selected product is not part of the selected sale.';
                continue;
            }

            $saleItemId = (int) ($saleItem['sale_itemID'] ?? 0);
            $alreadyReturnedQty = returnedQuantityForSaleItem($pdo, $saleItemId);
            $soldQty = (int) ($saleItem['quantity_sold'] ?? 0);
            $remainingQty = $soldQty - $alreadyReturnedQty;
            if ($quantity > $remainingQty) {
                $errors["items.$index.quantity"] = 'Requested return quantity exceeds remaining returnable quantity.';
                continue;
            }

            $estimatedRefund += ((float) ($saleItem['price_at_sale'] ?? 0)) * $quantity;
            $preparedItems[] = [
                'product_public_id' => $productPublicId,
                'quantity' => $quantity,
                'reason' => $reason,
                'status' => $status,
                'notes' => $notes !== '' ? $notes : null,
                'serial_number' => $serialNumber !== '' ? $serialNumber : null,
            ];
        }

        if ($errors !== []) {
            validationFail($errors);
        }

        $refundAmountInput = (float) ($body['refund_amount'] ?? 0);
        $refundAmount = $refundAmountInput > 0 ? $refundAmountInput : round($estimatedRefund, 2);
        $returnPublicId = randomPublicId('RT');

        try {
            $pdo->beginTransaction();

            $masterStmt = $pdo->prepare(
                'CALL return_transaction_add(
                    :public_id,
                    :sale_public_id,
                    :employee_public_id,
                    :refund_amount,
                    :return_method
                )'
            );
            $masterStmt->execute([
                'public_id' => $returnPublicId,
                'sale_public_id' => $salePublicId,
                'employee_public_id' => $resolvedEmployeePublicId,
                'refund_amount' => $refundAmount,
                'return_method' => $returnMethod,
            ]);
            $masterStmt->closeCursor();

            $itemStmt = $pdo->prepare(
                'CALL record_return_item_secure(
                    :return_public_id,
                    :sale_public_id,
                    :product_public_id,
                    :quantity,
                    :reason,
                    :status,
                    :serial_number,
                    :notes
                )'
            );
            foreach ($preparedItems as $preparedItem) {
                $itemStmt->execute([
                    'return_public_id' => $returnPublicId,
                    'sale_public_id' => $salePublicId,
                    'product_public_id' => $preparedItem['product_public_id'],
                    'quantity' => $preparedItem['quantity'],
                    'reason' => $preparedItem['reason'],
                    'status' => $preparedItem['status'],
                    'serial_number' => $preparedItem['serial_number'],
                    'notes' => $preparedItem['notes'],
                ]);
                $itemStmt->closeCursor();
            }

            $pdo->commit();
        } catch (PDOException $e) {
            rollbackIfInTransaction($pdo);
            validationFail(['return' => adminSqlErrorMessage($e, 'Unable to create return transaction.')]);
        }

        appendAuditLog($env, 'RETURN_LOG', 'RETURN_CREATED_BY_ADMIN', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'ReturnTransaction',
            'resource_public_id' => $returnPublicId,
        ], [
            'sale_public_id' => $salePublicId,
            'return_method' => $returnMethod,
            'estimated_refund' => $refundAmount,
        ]);

        sendJson(201, [
            'ok' => true,
            'message' => 'Return transaction created successfully.',
            'data' => ['public_id' => $returnPublicId],
        ]);
    }

    if ($path === '/admin/returns' && $method === 'GET') {
        requireAuth($env, ['admin', 'employee']);
        try {
            adminBackfillMissingRefundPayments($pdo);
        } catch (Throwable) {
            // Best-effort repair; do not block return listing when backfill fails.
        }

        $dateRange = adminNormalizedDateRangeQuery();
        $whereParts = [];
        $params = [];
        if ($dateRange['start_sql'] !== null) {
            $whereParts[] = 'rt.date_created >= :start_date';
            $params['start_date'] = $dateRange['start_sql'];
        }
        if ($dateRange['end_sql'] !== null) {
            $whereParts[] = 'rt.date_created <= :end_date';
            $params['end_date'] = $dateRange['end_sql'];
        }

        $sql = 'SELECT
                rt.public_id,
                rt.date_created,
                COALESCE(
                    NULLIF(rt.refund_amount, 0),
                    rp.refund_total,
                    (ri.return_quantity * COALESCE(si.price_at_sale, 0)),
                    0
                ) AS refund_amount,
                rt.return_progress,
                rt.return_method,
                COALESCE(ri.reason, "") AS reason,
                COALESCE(
                    ri.return_status,
                    CASE
                        WHEN rt.return_progress = "Finalized" THEN "Refunded"
                        ELSE "Pending"
                    END
                ) AS return_status,
                COALESCE(ri.return_quantity, 0) AS return_quantity,
                COALESCE(ri.notes, "") AS notes,
                COALESCE(c.public_id, "") AS customer_public_id,
                COALESCE(s.public_id, "") AS sale_public_id,
                COALESCE(s.fulfillment_method, "Delivery") AS shipping_method,
                COALESCE(p.public_id, "") AS product_public_id,
                COALESCE(p.product_name, "Unknown Product") AS product_name,
                TRIM(CONCAT(COALESCE(c.first_name, ""), " ", COALESCE(c.last_name, ""))) AS customer_name
             FROM api_return_transaction rt
             LEFT JOIN api_return_item ri ON ri.returnID = rt.returnID
             LEFT JOIN api_sale s ON s.saleID = rt.saleID
             LEFT JOIN api_customer c ON c.customerID = s.customerID
             LEFT JOIN api_sale_item si ON si.sale_itemID = ri.sale_itemID
             LEFT JOIN api_product p ON p.productID = si.productID
             LEFT JOIN (
                SELECT returnID, COALESCE(SUM(amount), 0) AS refund_total
                FROM api_refund_payment
                GROUP BY returnID
             ) rp ON rp.returnID = rt.returnID
             ' . ($whereParts !== [] ? ('WHERE ' . implode(' AND ', $whereParts)) : '') . '
             ORDER BY rt.date_created DESC, rt.returnID DESC, ri.return_itemID DESC';
        $returnsStmt = $pdo->prepare($sql);
        $returnsStmt->execute($params);
        $rawRows = $returnsStmt->fetchAll();

        $catalogMap = catalogMapByPublicId(loadProductCatalog($env));
        $rows = [];
        foreach ($rawRows as $row) {
            $productPublicId = asString($row['product_public_id'] ?? '');
            $catalogItem = $catalogMap[$productPublicId] ?? [];
            $returnStatus = asString($row['return_status'] ?? '');
            $row['refund_method'] = $returnStatus === 'Store Credit'
                ? 'Store Credit'
                : ($returnStatus === 'Replaced' ? 'Replacement' : 'Original Payment Method');
            $row['image_url'] = asString($catalogItem['image_url'] ?? '');
            if (asString($row['customer_name'] ?? '') === '') {
                $row['customer_name'] = 'Walk-in Customer';
            }
            $rows[] = $row;
        }

        sendJson(200, ['ok' => true, 'data' => ['returns' => $rows]]);
    }

    if (preg_match('#^/admin/returns/([A-Za-z0-9\-]+)$#', $path, $matches) === 1 && $method === 'PUT') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $publicId = $matches[1];
        if (!isValidPublicId($publicId)) {
            validationFail(['return' => 'Invalid return reference format.']);
        }
        $body = requestBody($env);
        $progress = asString($body['return_progress'] ?? '');
        $itemStatus = asString($body['return_status'] ?? '');

        if ($progress === '') {
            validationFail(['return_progress' => 'Return progress is required.']);
        }
        if ($itemStatus === '') {
            validationFail(['return_status' => 'Return item status is required.']);
        }

        $find = $pdo->prepare(
            'SELECT
                rt.returnID,
                rt.public_id AS return_public_id,
                rt.date_created,
                rt.return_method,
                rt.return_progress,
                ri.return_itemID,
                ri.return_status,
                ri.return_quantity,
                ri.reason,
                ri.notes,
                rt.refund_amount,
                si.price_at_sale,
                s.public_id AS sale_public_id,
                s.sale_status,
                s.tracking_number,
                s.courier_name,
                c.customerID,
                c.current_credit,
                c.public_id AS customer_public_id,
                p.public_id AS product_public_id,
                p.product_name
             FROM api_return_transaction rt
             JOIN api_return_item ri ON ri.returnID = rt.returnID
             JOIN api_sale_item si ON si.sale_itemID = ri.sale_itemID
             JOIN api_sale s ON s.saleID = rt.saleID
             JOIN api_customer c ON c.customerID = s.customerID
             JOIN api_product p ON p.productID = si.productID
             WHERE rt.public_id = :public_id
             LIMIT 1'
        );
        $find->execute(['public_id' => $publicId]);
        $row = $find->fetch();
        if (!is_array($row)) {
            sendJson(404, ['ok' => false, 'message' => 'Return transaction not found.']);
        }

        $currentProgress = asString($row['return_progress'] ?? '');
        $salePublicId = asString($row['sale_public_id'] ?? '');

        try {
            $proc = $pdo->prepare(
                'CALL return_update_admin(:return_public_id, :item_status, :progress, :employee_public_id)'
            );
            $proc->execute([
                'return_public_id' => $publicId,
                'item_status' => $itemStatus,
                'progress' => $progress,
                'employee_public_id' => (string) $claims['sub'],
            ]);
            $proc->closeCursor();
        } catch (PDOException $e) {
            $message = trim($e->getMessage());
            if (preg_match('/SQLSTATE\\[[^\\]]+\\]:[^:]*:\\s*(.+)$/', $message, $matches) === 1) {
                $message = trim((string) ($matches[1] ?? ''));
            }
            validationFail([
                'return' => $message !== '' ? $message : 'Unable to update return transaction.',
            ]);
        }

        $findUpdated = $pdo->prepare(
            'SELECT
                rt.returnID,
                rt.public_id AS return_public_id,
                rt.date_created,
                rt.return_method,
                rt.return_progress,
                ri.return_itemID,
                ri.return_status,
                ri.return_quantity,
                ri.reason,
                ri.notes,
                rt.refund_amount,
                si.price_at_sale,
                s.public_id AS sale_public_id,
                s.sale_status,
                s.tracking_number,
                s.courier_name,
                c.customerID,
                c.current_credit,
                c.public_id AS customer_public_id,
                p.public_id AS product_public_id,
                p.product_name
             FROM api_return_transaction rt
             JOIN api_return_item ri ON ri.returnID = rt.returnID
             JOIN api_sale_item si ON si.sale_itemID = ri.sale_itemID
             JOIN api_sale s ON s.saleID = rt.saleID
             JOIN api_customer c ON c.customerID = s.customerID
             JOIN api_product p ON p.productID = si.productID
             WHERE rt.public_id = :public_id
             LIMIT 1'
        );
        $findUpdated->execute(['public_id' => $publicId]);
        $updatedRow = $findUpdated->fetch();
        if (is_array($updatedRow)) {
            $row = $updatedRow;
        }

        $shipmentMethodLabel = asString($row['return_method'] ?? '') === 'Courier' ? 'Courier Pick-up' : 'Drop-off';
        $refundMethodLabel = $itemStatus === 'Store Credit'
            ? 'Store Credit'
            : ($itemStatus === 'Replaced' ? 'Replacement' : 'Original Payment Method');

        $normalizedReturnStatus = normalizeMongoReturnRequestStatus($progress, $itemStatus);
        upsertMongoReturnRequest(
            $env,
            asString($row['return_public_id'] ?? $publicId),
            asString($row['customer_public_id'] ?? ''),
            $salePublicId,
            [
                'product_public_id' => asString($row['product_public_id'] ?? ''),
                'product_name' => asString($row['product_name'] ?? ''),
                'unit_price' => (float) ($row['price_at_sale'] ?? 0),
            ],
            asString($row['reason'] ?? ''),
            asString($row['notes'] ?? ''),
            $shipmentMethodLabel,
            $refundMethodLabel,
            $normalizedReturnStatus,
            asString($row['date_created'] ?? ''),
            (string) $claims['sub'],
            'Admin updated return request status to ' . $normalizedReturnStatus . '.'
        );

        appendAuditLog($env, 'RETURN_LOG', 'RETURN_STATUS_UPDATE', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'ReturnTransaction',
            'resource_public_id' => $publicId,
        ], [
            'previous_state' => asString($row['return_progress'] ?? ''),
            'new_state' => $progress,
            'item_status' => $itemStatus,
        ]);

        sendJson(200, ['ok' => true, 'message' => 'Return transaction updated.']);
    }

    if ($path === '/admin/suppliers' && $method === 'GET') {
        requireAuth($env, ['admin', 'employee']);
        $rows = $pdo->query('SELECT DISTINCT * FROM api_supplier ORDER BY is_active DESC, supplier_name ASC')->fetchAll();
        sendJson(200, ['ok' => true, 'data' => ['suppliers' => $rows]]);
    }

    if ($path === '/admin/suppliers' && $method === 'POST') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $body = requestBody($env);

        $supplierName = asString($body['supplier_name'] ?? '');
        $firstName = asString($body['contact_first_name'] ?? '');
        $lastName = asString($body['contact_last_name'] ?? '');
        $email = strtolower(asString($body['email_address'] ?? ''));
        $phone = asString($body['contact_number'] ?? '');
        $barangay = asString($body['barangay'] ?? '');
        $city = asString($body['city_municipality'] ?? '');
        $phoneNormalized = normalizePhoneNumber($phone);

        $errors = [];
        if ($supplierName === '') $errors['supplier_name'] = 'Supplier name is required.';
        if ($firstName === '') $errors['contact_first_name'] = 'Contact first name is required.';
        if ($lastName === '') $errors['contact_last_name'] = 'Contact last name is required.';
        if ($barangay === '') $errors['barangay'] = 'Barangay is required.';
        if ($city === '') $errors['city_municipality'] = 'City/Municipality is required.';
        if ($email !== '' && !validateEmail($email)) $errors['email_address'] = 'Please enter a valid email address.';
        if ($phone !== '' && $phoneNormalized === null) $errors['contact_number'] = 'Please enter a valid phone number (example: 09171234567 or 639171234567).';
        if ($supplierName !== '') {
            $supplierNameError = validateTextLength($supplierName, 'Supplier name', 2, 100);
            if ($supplierNameError !== null) $errors['supplier_name'] = $supplierNameError;
        }
        if ($firstName !== '') {
            $firstNameError = validateHumanName($firstName, 'Contact first name');
            if ($firstNameError !== null) $errors['contact_first_name'] = $firstNameError;
        }
        if ($lastName !== '') {
            $lastNameError = validateHumanName($lastName, 'Contact last name');
            if ($lastNameError !== null) $errors['contact_last_name'] = $lastNameError;
        }
        if ($supplierName !== '') {
            $existingNameStmt = $pdo->prepare('SELECT public_id FROM api_supplier WHERE UPPER(TRIM(supplier_name)) = UPPER(TRIM(:supplier_name)) LIMIT 1');
            $existingNameStmt->execute(['supplier_name' => $supplierName]);
            if (is_array($existingNameStmt->fetch())) {
                $errors['supplier_name'] = 'This supplier name already exists.';
            }
        }
        if ($email !== '') {
            $existingEmailStmt = $pdo->prepare('SELECT public_id FROM api_supplier WHERE email_address = :email LIMIT 1');
            $existingEmailStmt->execute(['email' => $email]);
            if (is_array($existingEmailStmt->fetch())) {
                $errors['email_address'] = 'This supplier email is already in use.';
            }
        }
        if ($phoneNormalized !== null && $phoneNormalized !== '') {
            $existingPhoneStmt = $pdo->prepare('SELECT public_id FROM api_supplier WHERE contact_number = :phone LIMIT 1');
            $existingPhoneStmt->execute(['phone' => $phoneNormalized]);
            if (is_array($existingPhoneStmt->fetch())) {
                $errors['contact_number'] = 'This supplier contact number is already in use.';
            }
        }
        if ($errors !== []) validationFail($errors);

        $supplierPublicId = randomPublicId('SP');
        try {
            $proc = $pdo->prepare('CALL record_new_supplier(:public_id, :name, :first_name, :last_name, :email, :phone, :barangay, :city)');
            $proc->execute([
                'public_id' => $supplierPublicId,
                'name' => $supplierName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email !== '' ? $email : null,
                'phone' => $phoneNormalized,
                'barangay' => $barangay,
                'city' => $city,
            ]);
            $proc->closeCursor();
        } catch (PDOException $e) {
            validationFail(['supplier' => adminSqlErrorMessage($e, 'Unable to create supplier.')]);
        }

        appendAuditLog($env, 'SUPPLIER_LOG', 'SUPPLIER_CREATED', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'Supplier',
            'resource_public_id' => $supplierPublicId,
        ]);

        sendJson(201, ['ok' => true, 'message' => 'Supplier created successfully.']);
    }

    if (preg_match('#^/admin/suppliers/([A-Za-z0-9\-]+)$#', $path, $matches) === 1 && $method === 'PUT') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $publicId = $matches[1];
        if (!isValidPublicId($publicId)) {
            validationFail(['supplier' => 'Invalid supplier reference format.']);
        }

        $body = requestBody($env);
        $supplierName = asString($body['supplier_name'] ?? '');
        $firstName = asString($body['contact_first_name'] ?? '');
        $lastName = asString($body['contact_last_name'] ?? '');
        $email = strtolower(asString($body['email_address'] ?? ''));
        $phone = asString($body['contact_number'] ?? '');
        $street = asString($body['street_address'] ?? '');
        $barangay = asString($body['barangay'] ?? '');
        $city = asString($body['city_municipality'] ?? '');
        $province = asString($body['province'] ?? '');
        $zip = asString($body['zip_code'] ?? '');
        $phoneNormalized = normalizePhoneNumber($phone);

        $errors = [];
        if ($supplierName === '') $errors['supplier_name'] = 'Supplier name is required.';
        if ($firstName === '') $errors['contact_first_name'] = 'Contact first name is required.';
        if ($lastName === '') $errors['contact_last_name'] = 'Contact last name is required.';
        if ($barangay === '') $errors['barangay'] = 'Barangay is required.';
        if ($city === '') $errors['city_municipality'] = 'City/Municipality is required.';
        if ($email !== '' && !validateEmail($email)) $errors['email_address'] = 'Please enter a valid email address.';
        if ($phone !== '' && $phoneNormalized === null) $errors['contact_number'] = 'Please enter a valid phone number (example: 09171234567 or 639171234567).';
        if ($zip !== '' && !validateZipCode($zip)) $errors['zip_code'] = 'Please enter a valid 4-digit zip code.';
        if ($email === '' && $phone === '') $errors['contact'] = 'Please provide at least email or contact number.';
        if ($supplierName !== '') {
            $supplierNameError = validateTextLength($supplierName, 'Supplier name', 2, 100);
            if ($supplierNameError !== null) $errors['supplier_name'] = $supplierNameError;
        }
        if ($firstName !== '') {
            $firstNameError = validateHumanName($firstName, 'Contact first name');
            if ($firstNameError !== null) $errors['contact_first_name'] = $firstNameError;
        }
        if ($lastName !== '') {
            $lastNameError = validateHumanName($lastName, 'Contact last name');
            if ($lastNameError !== null) $errors['contact_last_name'] = $lastNameError;
        }
        if ($supplierName !== '') {
            $existingNameStmt = $pdo->prepare('SELECT public_id FROM api_supplier WHERE UPPER(TRIM(supplier_name)) = UPPER(TRIM(:supplier_name)) AND public_id <> :public_id LIMIT 1');
            $existingNameStmt->execute(['supplier_name' => $supplierName, 'public_id' => $publicId]);
            if (is_array($existingNameStmt->fetch())) {
                $errors['supplier_name'] = 'This supplier name already exists.';
            }
        }
        if ($email !== '') {
            $existingEmailStmt = $pdo->prepare('SELECT public_id FROM api_supplier WHERE email_address = :email AND public_id <> :public_id LIMIT 1');
            $existingEmailStmt->execute(['email' => $email, 'public_id' => $publicId]);
            if (is_array($existingEmailStmt->fetch())) {
                $errors['email_address'] = 'This supplier email is already in use.';
            }
        }
        if ($phoneNormalized !== null && $phoneNormalized !== '') {
            $existingPhoneStmt = $pdo->prepare('SELECT public_id FROM api_supplier WHERE contact_number = :phone AND public_id <> :public_id LIMIT 1');
            $existingPhoneStmt->execute(['phone' => $phoneNormalized, 'public_id' => $publicId]);
            if (is_array($existingPhoneStmt->fetch())) {
                $errors['contact_number'] = 'This supplier contact number is already in use.';
            }
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        try {
            $proc = $pdo->prepare(
                'CALL update_supplier_master(
                    :public_id, :supplier_name, :contact_first_name, :contact_last_name,
                    :contact_number, :email_address, :street_address, :barangay,
                    :city_municipality, :province, :zip_code
                )'
            );
            $proc->execute([
                'public_id' => $publicId,
                'supplier_name' => $supplierName,
                'contact_first_name' => $firstName,
                'contact_last_name' => $lastName,
                'contact_number' => $phoneNormalized,
                'email_address' => $email !== '' ? $email : null,
                'street_address' => $street !== '' ? $street : null,
                'barangay' => $barangay,
                'city_municipality' => $city,
                'province' => $province !== '' ? $province : null,
                'zip_code' => $zip !== '' ? $zip : null,
            ]);
            $proc->closeCursor();
        } catch (PDOException $e) {
            validationFail(['supplier' => adminSqlErrorMessage($e, 'Unable to update supplier.')]);
        }

        appendAuditLog($env, 'SUPPLIER_LOG', 'SUPPLIER_UPDATED', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'Supplier',
            'resource_public_id' => $publicId,
        ]);

        sendJson(200, ['ok' => true, 'message' => 'Supplier updated successfully.']);
    }

    if (preg_match('#^/admin/suppliers/([A-Za-z0-9\-]+)/status$#', $path, $matches) === 1 && $method === 'PATCH') {
        $claims = requireAuth($env, ['admin', 'employee']);
        $publicId = $matches[1];
        if (!isValidPublicId($publicId)) {
            validationFail(['supplier' => 'Invalid supplier reference format.']);
        }

        $body = requestBody($env);
        $isActive = parseBoolean($body['is_active'] ?? null);
        if ($isActive === null) {
            validationFail(['is_active' => 'Please provide a boolean value for is_active.']);
        }

        try {
            $stmt = $pdo->prepare('CALL update_supplier_status(:public_id, :is_active)');
            $stmt->execute([
                'public_id' => $publicId,
                'is_active' => $isActive ? 1 : 0,
            ]);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            validationFail(['supplier' => adminSqlErrorMessage($e, 'Unable to update supplier status.')]);
        }

        appendAuditLog($env, 'SUPPLIER_LOG', 'SUPPLIER_STATUS_UPDATED', [
            'actor_type' => 'ADMIN',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'Supplier',
            'resource_public_id' => $publicId,
        ], [
            'new_state' => $isActive,
        ]);

        sendJson(200, ['ok' => true, 'message' => 'Supplier status updated successfully.']);
    }

    if ($path === '/admin/activity' && $method === 'GET') {
        requireAuth($env, ['admin', 'employee']);
        $manager = mongoManager($env);
        if (!$manager) {
            sendJson(200, ['ok' => true, 'data' => ['logs' => []]]);
        }

        $logs = loadAuditLogs($env, $manager, 100);
        sendJson(200, ['ok' => true, 'data' => ['logs' => $logs]]);
    }

