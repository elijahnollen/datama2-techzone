<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/common.php';

function mysqlPdo(array $env): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = envValue($env, 'DB_HOST', '127.0.0.1');
    $port = envValue($env, 'DB_PORT', '3306');
    $db = envValue($env, 'DB_NAME', 'techzone_new_inventory');
    $user = envValue($env, 'DB_USER', 'root');
    $pass = envValue($env, 'DB_PASS', '');

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $db);
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
    ]);

    $autoSchemaSync = strtolower(asString(envValue($env, 'DB_AUTO_SCHEMA_SYNC', 'false')));
    if (in_array($autoSchemaSync, ['1', 'true', 'yes', 'on'], true)) {
        ensureLegacyWalkInSalesCompleted($pdo);
        ensureOrderStatusSchema($pdo);
        ensureInventoryTransactionSchema($pdo);
        ensureRefundPaymentSchema($pdo);
    }

    return $pdo;
}

function ensureLegacyWalkInSalesCompleted(PDO $pdo): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;
    // Legacy normalization is now handled in seed/setup SQL where needed.
}

function ensureOrderStatusSchema(PDO $pdo): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    $saleStatusColumn = $pdo->query("SHOW COLUMNS FROM sale LIKE 'sale_status'")->fetch();
    $saleStatusType = strtolower((string) ($saleStatusColumn['Type'] ?? $saleStatusColumn['type'] ?? ''));
    if ($saleStatusType !== '' && strpos($saleStatusType, "'returned'") !== false) {
        $pdo->exec("UPDATE sale SET sale_status = 'Completed' WHERE sale_status = 'Returned'");
        $pdo->exec(
            "ALTER TABLE sale
             MODIFY sale_status ENUM('Pending', 'Processing', 'Ready for Pickup', 'Shipped', 'Delivered', 'Completed', 'Cancelled')
             DEFAULT 'Pending' NOT NULL"
        );
    }

    $routineStmt = $pdo->prepare(
        'SELECT ROUTINE_DEFINITION
         FROM information_schema.ROUTINES
         WHERE ROUTINE_SCHEMA = DATABASE()
           AND ROUTINE_TYPE = "PROCEDURE"
           AND ROUTINE_NAME = "order_status_update"
         LIMIT 1'
    );
    $routineStmt->execute();
    $routine = $routineStmt->fetch();
    $routineDefinition = strtolower((string) ($routine['ROUTINE_DEFINITION'] ?? $routine['routine_definition'] ?? ''));
    $needsProcedureRefresh = $routineDefinition === '' || strpos($routineDefinition, 'returned') !== false;

    if (!$needsProcedureRefresh) {
        return;
    }

    $pdo->exec('DROP PROCEDURE IF EXISTS order_status_update');
    $pdo->exec(
        "CREATE PROCEDURE order_status_update(
            IN p_sale_public_id VARCHAR(20),
            IN p_sale_status VARCHAR(30),
            IN p_tracking_number VARCHAR(50),
            IN p_courier_name VARCHAR(50),
            IN p_employee_public_id VARCHAR(20)
        )
        BEGIN
            DECLARE v_saleID INT;
            DECLARE v_employeeID INT;
            DECLARE v_current_status VARCHAR(30);

            SELECT saleID, sale_status INTO v_saleID, v_current_status
            FROM sale
            WHERE public_id = p_sale_public_id
            LIMIT 1;

            SELECT employeeID INTO v_employeeID
            FROM employee
            WHERE public_id = p_employee_public_id
            LIMIT 1;

            IF v_saleID IS NULL THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Sale not found.';
            ELSEIF v_employeeID IS NULL THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Employee not found.';
            ELSEIF p_sale_status NOT IN ('Pending', 'Processing', 'Ready for Pickup', 'Shipped', 'Delivered', 'Completed', 'Cancelled') THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid sale status.';
            ELSEIF v_current_status = 'Cancelled' AND p_sale_status <> 'Cancelled' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Cannot change status of a cancelled order.';
            ELSEIF v_current_status = 'Completed' AND p_sale_status <> 'Completed' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Cannot change status of a completed order.';
            ELSEIF v_current_status = 'Shipped' AND p_sale_status = 'Pending' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Fraud Alert: Cannot revert a shipped order backward to pending.';
            ELSE
                UPDATE sale
                SET sale_status = p_sale_status,
                    tracking_number = p_tracking_number,
                    courier_name = p_courier_name,
                    employeeID = v_employeeID
                WHERE saleID = v_saleID;
            END IF;
        END"
    );
}

function ensureInventoryTransactionSchema(PDO $pdo): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    $inventoryTypeColumn = $pdo->query("SHOW COLUMNS FROM inventory_transaction LIKE 'transaction_type'")->fetch();
    $inventoryType = strtolower((string) ($inventoryTypeColumn['Type'] ?? $inventoryTypeColumn['type'] ?? ''));
    if ($inventoryType !== '' && strpos($inventoryType, "'cancelled sale'") === false) {
        $pdo->exec(
            "ALTER TABLE inventory_transaction
             MODIFY transaction_type ENUM('Sale', 'Return', 'Replacement', 'Restock', 'Cancelled Sale') NOT NULL"
        );
    }

    $routineStmt = $pdo->prepare(
        'SELECT ROUTINE_DEFINITION
         FROM information_schema.ROUTINES
         WHERE ROUTINE_SCHEMA = DATABASE()
           AND ROUTINE_TYPE = "PROCEDURE"
           AND ROUTINE_NAME = "inventory_adjustment_add"
         LIMIT 1'
    );
    $routineStmt->execute();
    $routine = $routineStmt->fetch();
    $routineDefinition = strtolower((string) ($routine['ROUTINE_DEFINITION'] ?? $routine['routine_definition'] ?? ''));
    $needsProcedureRefresh = $routineDefinition === '' || strpos($routineDefinition, 'cancelled sale') === false;

    if ($needsProcedureRefresh) {
        $pdo->exec('DROP PROCEDURE IF EXISTS inventory_adjustment_add');
        $pdo->exec(
            "CREATE PROCEDURE inventory_adjustment_add(
                IN p_product_public_id VARCHAR(20),
                IN p_employee_public_id VARCHAR(20),
                IN p_quantity_change INT,
                IN p_transaction_type VARCHAR(20),
                IN p_reference_id INT
            )
            BEGIN
                DECLARE v_productID INT;
                DECLARE v_employeeID INT;
                DECLARE v_current_qty INT;

                SELECT productID, quantity INTO v_productID, v_current_qty
                FROM product
                WHERE public_id = p_product_public_id
                LIMIT 1;

                SELECT employeeID INTO v_employeeID
                FROM employee
                WHERE public_id = p_employee_public_id
                LIMIT 1;

                IF v_productID IS NULL THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Product not found.';
                ELSEIF v_employeeID IS NULL THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Employee not found.';
                ELSEIF p_quantity_change = 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Quantity change cannot be zero.';
                ELSEIF p_transaction_type NOT IN ('Sale', 'Return', 'Replacement', 'Restock', 'Cancelled Sale') THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Invalid transaction type.';
                ELSEIF (v_current_qty + p_quantity_change) < 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Stock cannot go below zero.';
                ELSE
                    INSERT INTO inventory_transaction (
                        transaction_date,
                        quantity_change,
                        transaction_type,
                        referenceID,
                        productID,
                        employeeID
                    )
                    VALUES (
                        NOW(),
                        p_quantity_change,
                        p_transaction_type,
                        p_reference_id,
                        v_productID,
                        v_employeeID
                    );
                END IF;
            END"
        );
    }

    $pdo->exec(
        "CREATE OR REPLACE VIEW inventory_transactions AS
         SELECT
            it.transID,
            it.transaction_date,
            it.quantity_change,
            p.public_id AS product_public_id,
            p.product_name,
            p.quantity AS current_stock,
            e.public_id AS employee_public_id,
            e.first_name AS employee_first_name,
            e.last_name AS employee_last_name,
            CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
            it.referenceID,
            CASE
                WHEN it.transaction_type = 'Sale' THEN CONCAT('Sale #', it.referenceID)
                WHEN it.transaction_type = 'Return' THEN CONCAT('Return #', it.referenceID)
                WHEN it.transaction_type = 'Replacement' THEN CONCAT('Replacement #', it.referenceID)
                WHEN it.transaction_type = 'Restock' THEN 'Restock'
                WHEN it.transaction_type = 'Cancelled Sale' THEN CONCAT('Cancelled Sale #', it.referenceID)
                ELSE 'Unknown'
            END AS reference_description
         FROM inventory_transaction it
         JOIN product p ON it.productID = p.productID
         JOIN employee e ON it.employeeID = e.employeeID"
    );
}

function ensureRefundPaymentSchema(PDO $pdo): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    $tableExistsStmt = $pdo->prepare(
        'SELECT COUNT(*) AS c
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "refund_payment"'
    );
    $tableExistsStmt->execute();
    $tableExists = ((int) ($tableExistsStmt->fetch()['c'] ?? 0)) > 0;
    if (!$tableExists) {
        $pdo->exec(
            'CREATE TABLE refund_payment (
                refund_paymentID INT AUTO_INCREMENT PRIMARY KEY,
                refund_date DATETIME DEFAULT NOW(),
                updated_at DATETIME DEFAULT NOW() ON UPDATE NOW(),
                amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
                payment_method ENUM("Cash", "GCash", "Card", "Store Credit") NOT NULL,
                payment_status ENUM("Pending", "Failed", "Refunded") NOT NULL DEFAULT "Refunded",
                public_id VARCHAR(20) UNIQUE NOT NULL,
                returnID INT NOT NULL UNIQUE,
                FOREIGN KEY (returnID) REFERENCES return_transaction(returnID)
            )'
        );
    }

    $paymentColumnsStmt = $pdo->prepare(
        'SELECT COLUMN_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "payment"'
    );
    $paymentColumnsStmt->execute();
    $paymentColumns = [];
    foreach ($paymentColumnsStmt->fetchAll() as $columnRow) {
        $column = strtolower((string) ($columnRow['COLUMN_NAME'] ?? $columnRow['column_name'] ?? ''));
        if ($column !== '') {
            $paymentColumns[$column] = true;
        }
    }
    if (!isset($paymentColumns['public_id'])) {
        $pdo->exec('ALTER TABLE payment ADD COLUMN public_id VARCHAR(20) NULL AFTER payment_status');
        $paymentColumns['public_id'] = true;
    }

    $refundColumnsStmt = $pdo->prepare(
        'SELECT COLUMN_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "refund_payment"'
    );
    $refundColumnsStmt->execute();
    $refundColumns = [];
    foreach ($refundColumnsStmt->fetchAll() as $columnRow) {
        $column = strtolower((string) ($columnRow['COLUMN_NAME'] ?? $columnRow['column_name'] ?? ''));
        if ($column !== '') {
            $refundColumns[$column] = true;
        }
    }
    if (!isset($refundColumns['public_id'])) {
        $pdo->exec('ALTER TABLE refund_payment ADD COLUMN public_id VARCHAR(20) NULL AFTER payment_status');
        $refundColumns['public_id'] = true;
    }

    // Normalize legacy duplicate semantics before enforcing the new enum.
    $pdo->exec('UPDATE refund_payment SET payment_status = "Refunded" WHERE payment_status = "Completed"');
    $pdo->exec('ALTER TABLE refund_payment MODIFY payment_status ENUM("Pending", "Failed", "Refunded") NOT NULL DEFAULT "Refunded"');

    $hasReturnIdColumn = isset($paymentColumns['returnid']);
    $hasPaymentReferenceColumn = isset($paymentColumns['reference_number']);
    $hasRefundReferenceColumn = isset($refundColumns['reference_number']);

    if (!$hasReturnIdColumn) {
        // No legacy refund rows in payment to migrate.
    }

    $usedPaymentPublicIds = [];
    $paymentRowsSql = 'SELECT paymentID, public_id'
        . ($hasPaymentReferenceColumn ? ', reference_number' : ', NULL AS reference_number')
        . ' FROM payment
           ORDER BY paymentID ASC';
    $paymentRows = $pdo->query($paymentRowsSql)->fetchAll();
    $updatePaymentPublicId = $pdo->prepare(
        'UPDATE payment
         SET public_id = :public_id
         WHERE paymentID = :paymentID'
    );
    foreach ($paymentRows as $paymentRow) {
        $paymentId = (int) ($paymentRow['paymentID'] ?? 0);
        if ($paymentId < 1) {
            continue;
        }
        $currentPublicId = strtoupper(asString($paymentRow['public_id'] ?? ''));
        if ($currentPublicId === '' && $hasPaymentReferenceColumn) {
            $currentPublicId = strtoupper(asString($paymentRow['reference_number'] ?? ''));
        }
        if (isValidPublicId($currentPublicId) && str_starts_with($currentPublicId, 'PM-')) {
            $currentPublicId = 'PY-' . substr($currentPublicId, 3);
        }
        if (
            !isValidPublicId($currentPublicId)
            || !str_starts_with($currentPublicId, 'PY-')
            || isset($usedPaymentPublicIds[$currentPublicId])
        ) {
            do {
                $currentPublicId = randomPublicId('PY');
            } while (isset($usedPaymentPublicIds[$currentPublicId]));
        }
        if ($currentPublicId !== asString($paymentRow['public_id'] ?? '')) {
            $updatePaymentPublicId->execute([
                'public_id' => $currentPublicId,
                'paymentID' => $paymentId,
            ]);
        }
        $usedPaymentPublicIds[$currentPublicId] = true;
    }

    $usedRefundPublicIds = [];
    $refundRowsSql = 'SELECT refund_paymentID, public_id'
        . ($hasRefundReferenceColumn ? ', reference_number' : ', NULL AS reference_number')
        . ' FROM refund_payment
           ORDER BY refund_paymentID ASC';
    $refundRows = $pdo->query($refundRowsSql)->fetchAll();
    $updateRefundPublicId = $pdo->prepare(
        'UPDATE refund_payment
         SET public_id = :public_id
         WHERE refund_paymentID = :refund_paymentID'
    );
    foreach ($refundRows as $refundRow) {
        $refundPaymentId = (int) ($refundRow['refund_paymentID'] ?? 0);
        if ($refundPaymentId < 1) {
            continue;
        }
        $currentPublicId = strtoupper(asString($refundRow['public_id'] ?? ''));
        if ($currentPublicId === '' && $hasRefundReferenceColumn) {
            $currentPublicId = strtoupper(asString($refundRow['reference_number'] ?? ''));
        }
        if (!isValidPublicId($currentPublicId) || isset($usedRefundPublicIds[$currentPublicId])) {
            do {
                $currentPublicId = randomPublicId('RF');
            } while (isset($usedRefundPublicIds[$currentPublicId]));
        }
        if ($currentPublicId !== asString($refundRow['public_id'] ?? '')) {
            $updateRefundPublicId->execute([
                'public_id' => $currentPublicId,
                'refund_paymentID' => $refundPaymentId,
            ]);
        }
        $usedRefundPublicIds[$currentPublicId] = true;
    }

    $historicalRefundRows = $pdo->query(
        'SELECT
            rt.returnID,
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
    if (is_array($historicalRefundRows) && $historicalRefundRows !== []) {
        $insertHistoricalRefund = $pdo->prepare(
            'INSERT INTO refund_payment (refund_date, amount, payment_method, payment_status, public_id, returnID)
             VALUES (:refund_date, :amount, :payment_method, :payment_status, :public_id, :returnID)
             ON DUPLICATE KEY UPDATE
                refund_date = VALUES(refund_date),
                amount = VALUES(amount),
                payment_method = VALUES(payment_method),
                payment_status = VALUES(payment_status),
                public_id = VALUES(public_id),
                updated_at = NOW()'
        );
        foreach ($historicalRefundRows as $historicalRefundRow) {
            $refundAmount = (float) ($historicalRefundRow['refund_amount'] ?? 0);
            if ($refundAmount <= 0) {
                continue;
            }
            $paymentMethod = asString($historicalRefundRow['payment_method'] ?? 'Cash');
            if (!in_array($paymentMethod, ['Cash', 'GCash', 'Card', 'Store Credit'], true)) {
                $paymentMethod = 'Cash';
            }
            $paymentStatus = 'Refunded';
            do {
                $refundPublicId = randomPublicId('RF');
            } while (isset($usedRefundPublicIds[$refundPublicId]));
            $usedRefundPublicIds[$refundPublicId] = true;

            $insertHistoricalRefund->execute([
                'refund_date' => asString($historicalRefundRow['date_created'] ?? '') !== '' ? asString($historicalRefundRow['date_created']) : date('Y-m-d H:i:s'),
                'amount' => $refundAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'public_id' => $refundPublicId,
                'returnID' => (int) ($historicalRefundRow['returnID'] ?? 0),
            ]);
        }
    }

    $indexExistsStmt = $pdo->prepare(
        'SELECT COUNT(*) AS c
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table_name
           AND INDEX_NAME = :index_name'
    );
    $ensureUniqueIndex = static function (string $tableName, string $indexName, string $ddl) use ($pdo, $indexExistsStmt): void {
        $indexExistsStmt->execute([
            'table_name' => $tableName,
            'index_name' => $indexName,
        ]);
        $exists = ((int) ($indexExistsStmt->fetch()['c'] ?? 0)) > 0;
        if (!$exists) {
            $pdo->exec($ddl);
        }
    };
    $ensureUniqueIndex('payment', 'uq_payment_public_id', 'ALTER TABLE payment ADD UNIQUE KEY uq_payment_public_id (public_id)');
    $ensureUniqueIndex('refund_payment', 'uq_refund_payment_public_id', 'ALTER TABLE refund_payment ADD UNIQUE KEY uq_refund_payment_public_id (public_id)');

    $pdo->exec('ALTER TABLE payment MODIFY payment_status ENUM("Completed", "Pending", "Failed", "Refunded", "Cancelled") NOT NULL');
    $pdo->exec('ALTER TABLE payment MODIFY public_id VARCHAR(20) NOT NULL');
    $pdo->exec('ALTER TABLE refund_payment MODIFY public_id VARCHAR(20) NOT NULL');

    if ($hasPaymentReferenceColumn) {
        $pdo->exec('ALTER TABLE payment DROP COLUMN reference_number');
    }
    if ($hasRefundReferenceColumn) {
        $pdo->exec('ALTER TABLE refund_payment DROP COLUMN reference_number');
    }

    $pdo->exec('DROP PROCEDURE IF EXISTS record_payment');
    $pdo->exec(
        "CREATE PROCEDURE record_payment(
            IN p_amount DECIMAL(10,2),
            IN p_method ENUM('Cash', 'GCash', 'Card', 'Store Credit'),
            IN p_status ENUM('Completed', 'Pending', 'Failed', 'Refunded', 'Cancelled'),
            IN p_public_id VARCHAR(20),
            IN p_sale_public_id VARCHAR(20),
            IN p_return_public_id VARCHAR(20)
        )
        BEGIN
            DECLARE v_saleID INT DEFAULT NULL;
            DECLARE v_payment_public_id VARCHAR(20) DEFAULT NULL;
            DECLARE v_fulfillment_method VARCHAR(20) DEFAULT NULL;
            DECLARE v_effective_status VARCHAR(20) DEFAULT NULL;

            IF p_sale_public_id IS NOT NULL THEN
                SELECT saleID, fulfillment_method INTO v_saleID, v_fulfillment_method
                FROM sale
                WHERE public_id = p_sale_public_id;
            END IF;

            SET v_effective_status = p_status;
            IF v_fulfillment_method = 'Walk-in' THEN
                SET v_effective_status = 'Completed';
            END IF;

            IF p_public_id IS NULL OR TRIM(p_public_id) = '' OR UPPER(LEFT(TRIM(p_public_id), 3)) <> 'PY-' THEN
                SET v_payment_public_id = CONCAT('PY-', UPPER(LEFT(REPLACE(UUID(), '-', ''), 10)));
            ELSE
                SET v_payment_public_id = UPPER(TRIM(p_public_id));
            END IF;

            IF p_amount IS NULL OR p_amount <= 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Amount must be greater than zero.';
            ELSEIF v_saleID IS NULL THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid Sale Reference.';
            ELSE
                INSERT INTO payment (
                    payment_date, amount, payment_method,
                    payment_status, public_id, saleID
                ) VALUES (
                    NOW(), p_amount, p_method,
                    v_effective_status, v_payment_public_id, v_saleID
                );
            END IF;
        END"
    );

    $pdo->exec('DROP PROCEDURE IF EXISTS record_refund_payment');
    $pdo->exec(
        "CREATE PROCEDURE record_refund_payment(
            IN p_amount DECIMAL(10,2),
            IN p_method ENUM('Cash', 'GCash', 'Card', 'Store Credit'),
            IN p_status ENUM('Pending', 'Failed', 'Refunded'),
            IN p_public_id VARCHAR(20),
            IN p_return_public_id VARCHAR(20)
        )
        BEGIN
            DECLARE v_returnID INT DEFAULT NULL;
            DECLARE v_refund_public_id VARCHAR(20) DEFAULT NULL;

            IF p_return_public_id IS NOT NULL THEN
                SELECT returnID INTO v_returnID FROM return_transaction WHERE public_id = p_return_public_id;
            END IF;

            IF p_public_id IS NULL OR TRIM(p_public_id) = '' THEN
                SET v_refund_public_id = CONCAT('RF-', UPPER(LEFT(REPLACE(UUID(), '-', ''), 10)));
            ELSE
                SET v_refund_public_id = p_public_id;
            END IF;

            IF p_amount IS NULL OR p_amount <= 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Amount must be greater than zero.';
            ELSEIF v_returnID IS NULL THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid Return Reference.';
            ELSE
                INSERT INTO refund_payment (refund_date, amount, payment_method, payment_status, public_id, returnID)
                VALUES (NOW(), p_amount, p_method, p_status, v_refund_public_id, v_returnID)
                ON DUPLICATE KEY UPDATE
                    amount = VALUES(amount),
                    payment_method = VALUES(payment_method),
                    payment_status = VALUES(payment_status),
                    public_id = VALUES(public_id),
                    updated_at = NOW();
            END IF;
        END"
    );

    $pdo->exec('DROP PROCEDURE IF EXISTS update_payment_status');
    $pdo->exec(
        "CREATE PROCEDURE update_payment_status(
            IN p_payment_public_id VARCHAR(20),
            IN p_new_status ENUM('Completed', 'Pending', 'Failed', 'Refunded', 'Cancelled')
        )
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM payment WHERE public_id = p_payment_public_id) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Payment reference not found.';
            ELSE
                UPDATE payment
                SET payment_status = p_new_status
                WHERE public_id = p_payment_public_id;
            END IF;
        END"
    );
}

function mongoManager(array $env): ?MongoDB\Driver\Manager
{
    static $manager = null;
    static $initialized = false;

    if ($initialized) {
        return $manager;
    }

    $initialized = true;
    $uri = envValue($env, 'MONGO_URI', 'mongodb://127.0.0.1:27017');

    try {
        $manager = new MongoDB\Driver\Manager($uri);
        ensureMongoOperationalState($env, $manager);
        return $manager;
    } catch (Throwable) {
        return null;
    }
}

function mongoDbName(array $env): string
{
    return (string) envValue($env, 'MONGO_DB', 'techzone');
}

function mongoCollectionName(array $env, string $key): string
{
    $names = [
        'audit_logs' => 'admin_audit_log',
        'admin_audit_logs' => 'admin_audit_log',
        'customer_audit_logs' => 'customer_audit_log',
        'product_catalog' => 'product_catalog',
        'product_reviews' => 'product_review',
        'shopping_cart' => 'shopping_cart',
        'orders' => 'order',
        'return_request' => 'return_request',
        'customer_inquiry' => 'customer_inquiry',
    ];

    return $names[$key] ?? $key;
}

function ensureMongoOperationalState(array $env, MongoDB\Driver\Manager $manager): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    if (!shouldRunMongoMaintenance('techzone_mongo_maintenance_v1', 120)) {
        return;
    }

    ensureShoppingCartUniqueIndex($env, $manager);
    ensureProductCatalogUniqueIndex($env, $manager);
    reconcileMongoProductCatalogAgainstMysql($env, $manager);
    purgeAuthenticationAuditLogDocuments($env, $manager);
}

function shouldRunMongoMaintenance(string $taskKey, int $ttlSeconds): bool
{
    if ($ttlSeconds < 1) {
        $ttlSeconds = 1;
    }

    $root = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'techzone_mongo_maintenance';
    if (!is_dir($root)) {
        @mkdir($root, 0777, true);
    }

    $safeTaskKey = preg_replace('/[^a-zA-Z0-9_\-]/', '', $taskKey) ?? 'default';
    $path = $root . DIRECTORY_SEPARATOR . $safeTaskKey . '.lock';
    $now = time();
    $lastRun = 0;
    if (is_file($path)) {
        $raw = file_get_contents($path);
        if (is_string($raw) && ctype_digit(trim($raw))) {
            $lastRun = (int) trim($raw);
        }
    }

    if ($lastRun > 0 && ($now - $lastRun) < $ttlSeconds) {
        return false;
    }

    @file_put_contents($path, (string) $now);
    return true;
}

function ensureShoppingCartUniqueIndex(array $env, MongoDB\Driver\Manager $manager): void
{
    $db = mongoDbName($env);
    $cartCollection = mongoCollectionName($env, 'shopping_cart');

    if (tryCreateShoppingCartUniqueIndex($manager, $db, $cartCollection)) {
        return;
    }

    compactShoppingCartDocuments($env, $manager);
    tryCreateShoppingCartUniqueIndex($manager, $db, $cartCollection);
}

function tryCreateShoppingCartUniqueIndex(MongoDB\Driver\Manager $manager, string $db, string $collection): bool
{
    $createCommand = [
        'createIndexes' => $collection,
        'indexes' => [[
            'key' => ['customer_public_id' => 1],
            'name' => 'uniq_customer_public_id',
            'unique' => true,
        ]],
    ];

    try {
        $manager->executeCommand($db, new MongoDB\Driver\Command($createCommand));
        return true;
    } catch (Throwable) {
        // continue below; index may exist with conflicting options
    }

    try {
        $manager->executeCommand($db, new MongoDB\Driver\Command([
            'dropIndexes' => $collection,
            'index' => 'uniq_customer_public_id',
        ]));
    } catch (Throwable) {
        // noop
    }

    try {
        $manager->executeCommand($db, new MongoDB\Driver\Command($createCommand));
        return true;
    } catch (Throwable) {
        return false;
    }
}

function compactShoppingCartDocuments(array $env, MongoDB\Driver\Manager $manager): void
{
    $db = mongoDbName($env);
    $cartCollection = mongoCollectionName($env, 'shopping_cart');

    try {
        $cartDocs = mongoFindMany($manager, $db, $cartCollection, [], ['sort' => ['last_updated' => -1]]);
    } catch (Throwable) {
        return;
    }

    if ($cartDocs === []) {
        return;
    }

    $grouped = [];
    foreach ($cartDocs as $cartDoc) {
        if (!is_array($cartDoc)) {
            continue;
        }

        $customerPublicId = strtoupper(asString($cartDoc['customer_public_id'] ?? ''));
        if ($customerPublicId === '' || !isValidPublicId($customerPublicId)) {
            continue;
        }

        if (!isset($grouped[$customerPublicId])) {
            $grouped[$customerPublicId] = [
                'items' => [],
                'doc_ids' => [],
                'doc_total' => 0,
            ];
        }
        $grouped[$customerPublicId]['doc_total'] = (int) ($grouped[$customerPublicId]['doc_total'] ?? 0) + 1;

        $docItems = normalizeCustomerCartItems(is_array($cartDoc['items'] ?? null) ? $cartDoc['items'] : []);
        $grouped[$customerPublicId]['items'] = normalizeCustomerCartItems(array_merge(
            $grouped[$customerPublicId]['items'],
            $docItems
        ));

        $objectId = mongoObjectIdFromValue($cartDoc['_id'] ?? null);
        if ($objectId instanceof MongoDB\BSON\ObjectId) {
            $grouped[$customerPublicId]['doc_ids'][] = $objectId;
        }
    }

    foreach ($grouped as $customerPublicId => $payload) {
        if ((int) ($payload['doc_total'] ?? 0) <= 1) {
            continue;
        }

        $docIds = is_array($payload['doc_ids'] ?? null) ? $payload['doc_ids'] : [];
        foreach ($docIds as $docId) {
            if (!$docId instanceof MongoDB\BSON\ObjectId) {
                continue;
            }
            try {
                mongoDeleteOne($manager, $db, $cartCollection, ['_id' => $docId]);
            } catch (Throwable) {
                // noop
            }
        }

        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        try {
            upsertCustomerCartDocument($env, $manager, $customerPublicId, $items);
        } catch (Throwable) {
            // noop
        }
    }
}

function ensureProductCatalogUniqueIndex(array $env, MongoDB\Driver\Manager $manager): void
{
    $db = mongoDbName($env);
    $catalogCollection = mongoCollectionName($env, 'product_catalog');

    if (tryCreateProductCatalogUniqueIndex($manager, $db, $catalogCollection)) {
        return;
    }

    compactProductCatalogDocuments($env, $manager);
    tryCreateProductCatalogUniqueIndex($manager, $db, $catalogCollection);
}

function tryCreateProductCatalogUniqueIndex(MongoDB\Driver\Manager $manager, string $db, string $collection): bool
{
    $createCommand = [
        'createIndexes' => $collection,
        'indexes' => [[
            'key' => ['product_public_id' => 1],
            'name' => 'uniq_product_public_id',
            'unique' => true,
        ]],
    ];

    try {
        $manager->executeCommand($db, new MongoDB\Driver\Command($createCommand));
        return true;
    } catch (Throwable) {
        // continue below; index may exist with conflicting options
    }

    try {
        $manager->executeCommand($db, new MongoDB\Driver\Command([
            'dropIndexes' => $collection,
            'index' => 'uniq_product_public_id',
        ]));
    } catch (Throwable) {
        // noop
    }

    try {
        $manager->executeCommand($db, new MongoDB\Driver\Command($createCommand));
        return true;
    } catch (Throwable) {
        return false;
    }
}

function compactProductCatalogDocuments(array $env, MongoDB\Driver\Manager $manager): void
{
    $db = mongoDbName($env);
    $catalogCollection = mongoCollectionName($env, 'product_catalog');

    try {
        $catalogDocs = mongoFindMany(
            $manager,
            $db,
            $catalogCollection,
            [],
            [
                'projection' => [
                    '_id' => 1,
                    'product_public_id' => 1,
                    'updated_at' => 1,
                    'brand' => 1,
                    'model_name' => 1,
                    'category' => 1,
                    'sub_category' => 1,
                    'display_price' => 1,
                    'stock_level' => 1,
                    'is_active' => 1,
                    'is_in_stock' => 1,
                    'availability_status' => 1,
                    'short_description' => 1,
                    'long_description' => 1,
                    'specifications' => 1,
                    'image_url' => 1,
                    'tags' => 1,
                    'ui_flags' => 1,
                    'reviews' => 1,
                ],
            ]
        );
    } catch (Throwable) {
        return;
    }

    if ($catalogDocs === []) {
        return;
    }

    $grouped = [];
    foreach ($catalogDocs as $catalogDoc) {
        if (!is_array($catalogDoc)) {
            continue;
        }
        $publicId = strtoupper(asString($catalogDoc['product_public_id'] ?? ''));
        if ($publicId === '' || !isValidPublicId($publicId)) {
            continue;
        }
        if (!isset($grouped[$publicId])) {
            $grouped[$publicId] = [];
        }
        $grouped[$publicId][] = $catalogDoc;
    }

    foreach ($grouped as $publicId => $docs) {
        if (!is_array($docs) || count($docs) <= 1) {
            continue;
        }

        usort($docs, static function (array $left, array $right): int {
            $leftScore = catalogQualityScore($left);
            $rightScore = catalogQualityScore($right);
            if ($leftScore !== $rightScore) {
                return $rightScore <=> $leftScore;
            }
            $leftTs = mongoAuditTimestamp($left['updated_at'] ?? null);
            $rightTs = mongoAuditTimestamp($right['updated_at'] ?? null);
            return $rightTs <=> $leftTs;
        });

        $primary = $docs[0];
        $primaryObjectId = mongoObjectIdFromValue($primary['_id'] ?? null);
        if (!$primaryObjectId instanceof MongoDB\BSON\ObjectId) {
            continue;
        }

        $merged = $primary;
        for ($i = 1; $i < count($docs); $i++) {
            if (!is_array($docs[$i])) {
                continue;
            }
            $merged = mergeCatalogDocumentWithFallback($merged, $docs[$i]);
        }
        unset($merged['_id']);
        $merged['product_public_id'] = $publicId;
        $merged['updated_at'] = mongoTimestamp();

        try {
            mongoUpdateOne($manager, $db, $catalogCollection, ['_id' => $primaryObjectId], ['$set' => $merged], false);
        } catch (Throwable) {
            // continue deleting duplicates below to avoid blocking future unique index creation
        }

        for ($i = 1; $i < count($docs); $i++) {
            $duplicateObjectId = mongoObjectIdFromValue($docs[$i]['_id'] ?? null);
            if (!$duplicateObjectId instanceof MongoDB\BSON\ObjectId) {
                continue;
            }
            try {
                mongoDeleteOne($manager, $db, $catalogCollection, ['_id' => $duplicateObjectId]);
            } catch (Throwable) {
                // noop
            }
        }
    }
}

function reconcileMongoProductCatalogAgainstMysql(array $env, MongoDB\Driver\Manager $manager): void
{
    if (!function_exists('syncMongoProductCatalogFromMysql')) {
        return;
    }

    try {
        $pdo = mysqlPdo($env);
    } catch (Throwable) {
        return;
    }

    try {
        $rows = $pdo->query(
            'SELECT public_id
             FROM api_product
             WHERE public_id IS NOT NULL
               AND public_id <> ""
             ORDER BY productID ASC'
        )->fetchAll();
    } catch (Throwable) {
        return;
    }

    if (!is_array($rows) || $rows === []) {
        return;
    }

    $db = mongoDbName($env);
    $collection = mongoCollectionName($env, 'product_catalog');
    $mysqlPublicIds = [];

    foreach ($rows as $row) {
        $publicId = strtoupper(asString($row['public_id'] ?? ''));
        if ($publicId === '' || !isValidPublicId($publicId)) {
            continue;
        }
        $mysqlPublicIds[$publicId] = true;
        try {
            syncMongoProductCatalogFromMysql($env, $pdo, $publicId);
        } catch (Throwable) {
            // continue syncing next product
        }
    }

    try {
        $catalogDocs = mongoFindMany(
            $manager,
            $db,
            $collection,
            [],
            ['projection' => ['_id' => 1, 'product_public_id' => 1]]
        );
    } catch (Throwable) {
        return;
    }

    foreach ($catalogDocs as $catalogDoc) {
        if (!is_array($catalogDoc)) {
            continue;
        }
        $catalogPublicId = strtoupper(asString($catalogDoc['product_public_id'] ?? ''));
        if ($catalogPublicId === '' || isset($mysqlPublicIds[$catalogPublicId])) {
            continue;
        }
        $objectId = mongoObjectIdFromValue($catalogDoc['_id'] ?? null);
        if (!$objectId instanceof MongoDB\BSON\ObjectId) {
            continue;
        }
        try {
            mongoDeleteOne($manager, $db, $collection, ['_id' => $objectId]);
        } catch (Throwable) {
            // noop
        }
    }
}

function mongoObjectIdFromValue(mixed $value): ?MongoDB\BSON\ObjectId
{
    if (!class_exists('MongoDB\\BSON\\ObjectId')) {
        return null;
    }

    if ($value instanceof MongoDB\BSON\ObjectId) {
        return $value;
    }

    if (is_array($value)) {
        $value = asString($value['$oid'] ?? '');
    }

    if (is_string($value) && preg_match('/^[a-f0-9]{24}$/i', $value) === 1) {
        try {
            return new MongoDB\BSON\ObjectId($value);
        } catch (Throwable) {
            return null;
        }
    }

    return null;
}

function purgeAuthenticationAuditLogDocuments(array $env, MongoDB\Driver\Manager $manager): void
{
    $db = mongoDbName($env);
    $collections = array_values(array_unique([
        mongoCollectionName($env, 'admin_audit_logs'),
        mongoCollectionName($env, 'customer_audit_logs'),
        mongoCollectionName($env, 'audit_logs'),
    ]));

    $filter = [
        '$or' => [
            ['action_type' => ['$in' => ['CUSTOMER_REGISTER', 'CUSTOMER_LOGIN', 'ADMIN_LOGIN']]],
            ['log_category' => ['$in' => ['AUTHENTICATION_LOG', 'AUTH_LOG']]],
            ['target.resource_type' => ['$in' => ['Session', 'CustomerSession', 'EmployeeSession', 'AdminSession']]],
        ],
    ];

    foreach ($collections as $collection) {
        try {
            mongoDeleteMany($manager, $db, $collection, $filter);
        } catch (Throwable) {
            // noop
        }
    }
}

function mongoAuditCollectionForActor(array $env, array $actor): string
{
    $actorType = strtoupper(asString($actor['actor_type'] ?? ''));
    if ($actorType === 'CUSTOMER') {
        return mongoCollectionName($env, 'customer_audit_logs');
    }
    if (in_array($actorType, ['ADMIN', 'EMPLOYEE'], true)) {
        return mongoCollectionName($env, 'admin_audit_logs');
    }
    return mongoCollectionName($env, 'audit_logs');
}

function mongoAuditTimestamp(mixed $value): int
{
    if (is_array($value)) {
        $date = asString($value['$date'] ?? '');
        if ($date !== '') {
            $parsed = strtotime($date);
            return $parsed !== false ? $parsed : 0;
        }
        $numberLong = asString($value['$numberLong'] ?? '');
        if ($numberLong !== '' && is_numeric($numberLong)) {
            return (int) floor(((float) $numberLong) / 1000);
        }
    }
    if (is_string($value)) {
        $parsed = strtotime($value);
        return $parsed !== false ? $parsed : 0;
    }
    if ($value instanceof DateTimeInterface) {
        return $value->getTimestamp();
    }
    if (class_exists('MongoDB\\BSON\\UTCDateTime') && $value instanceof MongoDB\BSON\UTCDateTime) {
        return $value->toDateTime()->getTimestamp();
    }
    return 0;
}

function isAuthenticationAuditEvent(string $category, string $actionType, array $target = []): bool
{
    $categoryUpper = strtoupper(trim($category));
    $actionUpper = strtoupper(trim($actionType));
    $resourceTypeUpper = strtoupper(asString($target['resource_type'] ?? ''));

    if (
        $categoryUpper === 'AUTHENTICATION_LOG'
        || $categoryUpper === 'AUTH_LOG'
        || str_contains($categoryUpper, 'AUTHENTICATION')
    ) {
        return true;
    }

    if (
        $actionUpper !== ''
        && (
            str_contains($actionUpper, 'LOGIN')
            || str_contains($actionUpper, 'LOGOUT')
            || str_contains($actionUpper, 'REGISTER')
            || str_contains($actionUpper, 'SIGNUP')
            || str_contains($actionUpper, 'SIGN_UP')
            || str_contains($actionUpper, 'SIGNIN')
            || str_contains($actionUpper, 'SIGNOUT')
            || str_contains($actionUpper, 'SESSION')
        )
    ) {
        return true;
    }

    if (
        $resourceTypeUpper === 'SESSION'
        || $resourceTypeUpper === 'CUSTOMERSESSION'
        || $resourceTypeUpper === 'EMPLOYEESESSION'
        || $resourceTypeUpper === 'ADMINSESSION'
    ) {
        return true;
    }

    return false;
}

function loadAuditLogs(array $env, MongoDB\Driver\Manager $manager, int $limit): array
{
    if ($limit < 1) {
        $limit = 1;
    }

    $db = mongoDbName($env);
    $collections = array_values(array_unique([
        mongoCollectionName($env, 'admin_audit_logs'),
        mongoCollectionName($env, 'customer_audit_logs'),
        mongoCollectionName($env, 'audit_logs'),
    ]));

    $logs = [];
    foreach ($collections as $collection) {
        try {
            $docs = mongoFindMany($manager, $db, $collection, [], ['sort' => ['timestamp' => -1], 'limit' => $limit]);
            foreach ($docs as $doc) {
                $target = is_array($doc['target'] ?? null) ? $doc['target'] : [];
                if (isAuthenticationAuditEvent(asString($doc['log_category'] ?? ''), asString($doc['action_type'] ?? ''), $target)) {
                    continue;
                }
                $logs[] = $doc;
            }
        } catch (Throwable) {
            // ignore missing collection and continue
        }
    }

    usort($logs, static function (array $left, array $right): int {
        $leftTs = mongoAuditTimestamp($left['timestamp'] ?? $left['created_at'] ?? null);
        $rightTs = mongoAuditTimestamp($right['timestamp'] ?? $right['created_at'] ?? null);
        return $rightTs <=> $leftTs;
    });

    if (count($logs) > $limit) {
        $logs = array_slice($logs, 0, $limit);
    }

    return $logs;
}

function mongoFindMany(
    MongoDB\Driver\Manager $manager,
    string $db,
    string $collection,
    array $filter = [],
    array $options = []
): array {
    $query = new MongoDB\Driver\Query($filter, $options);
    $cursor = $manager->executeQuery($db . '.' . $collection, $query);
    $rows = [];
    foreach ($cursor as $document) {
        $rows[] = toArrayDocument($document);
    }
    return $rows;
}

function mongoFindOne(
    MongoDB\Driver\Manager $manager,
    string $db,
    string $collection,
    array $filter = [],
    array $options = []
): ?array {
    $options['limit'] = 1;
    $rows = mongoFindMany($manager, $db, $collection, $filter, $options);
    return $rows[0] ?? null;
}

function mongoInsertOne(
    MongoDB\Driver\Manager $manager,
    string $db,
    string $collection,
    array $document
): void {
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->insert($document);
    $manager->executeBulkWrite($db . '.' . $collection, $bulk);
}

function mongoUpdateOne(
    MongoDB\Driver\Manager $manager,
    string $db,
    string $collection,
    array $filter,
    array $update,
    bool $upsert = false
): void {
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->update($filter, $update, ['multi' => false, 'upsert' => $upsert]);
    $manager->executeBulkWrite($db . '.' . $collection, $bulk);
}

function mongoDeleteOne(
    MongoDB\Driver\Manager $manager,
    string $db,
    string $collection,
    array $filter
): void {
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->delete($filter, ['limit' => 1]);
    $manager->executeBulkWrite($db . '.' . $collection, $bulk);
}

function mongoDeleteMany(
    MongoDB\Driver\Manager $manager,
    string $db,
    string $collection,
    array $filter
): void {
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->delete($filter, ['limit' => 0]);
    $manager->executeBulkWrite($db . '.' . $collection, $bulk);
}

function mongoUpdateMany(
    MongoDB\Driver\Manager $manager,
    string $db,
    string $collection,
    array $filter,
    array $update
): void {
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->update($filter, $update, ['multi' => true, 'upsert' => false]);
    $manager->executeBulkWrite($db . '.' . $collection, $bulk);
}

function appendAuditLog(array $env, string $category, string $actionType, array $actor, array $target, ?array $transition = null): void
{
    if (isAuthenticationAuditEvent($category, $actionType, $target)) {
        return;
    }

    $manager = mongoManager($env);
    if (!$manager) {
        return;
    }

    $doc = [
        'log_category' => $category,
        'action_type' => $actionType,
        'actor' => $actor,
        'target' => $target,
        'state_transition' => $transition,
        'timestamp' => nowUtc(),
    ];

    try {
        $collection = mongoAuditCollectionForActor($env, $actor);
        mongoInsertOne($manager, mongoDbName($env), $collection, $doc);
    } catch (Throwable) {
        // noop
    }
}

function normalizeCustomerCartItems(array $items): array
{
    $normalizedMap = [];
    foreach ($items as $rawItem) {
        if (!is_array($rawItem)) {
            continue;
        }

        $productPublicId = strtoupper(asString($rawItem['product_public_id'] ?? ''));
        if ($productPublicId === '' || !isValidPublicId($productPublicId)) {
            continue;
        }

        $quantity = (int) ($rawItem['quantity'] ?? 0);
        if ($quantity < 1) {
            continue;
        }
        $quantity = min(50, $quantity);

        $productName = asString($rawItem['product_name'] ?? '');
        $priceAtAddition = (float) ($rawItem['price_at_addition'] ?? 0);
        if ($priceAtAddition < 0) {
            $priceAtAddition = 0.0;
        }
        $imageUrl = asString($rawItem['image_url'] ?? '');
        $addedAt = asString($rawItem['added_at'] ?? nowUtc());

        if (isset($normalizedMap[$productPublicId])) {
            $normalizedMap[$productPublicId]['quantity'] = min(
                50,
                (int) ($normalizedMap[$productPublicId]['quantity'] ?? 0) + $quantity
            );
            if (asString($normalizedMap[$productPublicId]['product_name'] ?? '') === '' && $productName !== '') {
                $normalizedMap[$productPublicId]['product_name'] = $productName;
            }
            if ((float) ($normalizedMap[$productPublicId]['price_at_addition'] ?? 0) <= 0 && $priceAtAddition > 0) {
                $normalizedMap[$productPublicId]['price_at_addition'] = $priceAtAddition;
            }
            if (asString($normalizedMap[$productPublicId]['image_url'] ?? '') === '' && $imageUrl !== '') {
                $normalizedMap[$productPublicId]['image_url'] = $imageUrl;
            }
            continue;
        }

        $normalizedMap[$productPublicId] = [
            'product_public_id' => $productPublicId,
            'product_name' => $productName,
            'quantity' => $quantity,
            'price_at_addition' => $priceAtAddition,
            'image_url' => $imageUrl,
            'added_at' => $addedAt,
        ];
    }

    return array_values($normalizedMap);
}

function customerCartSummary(array $items): array
{
    $totalItems = 0;
    $subtotal = 0.0;
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $quantity = (int) ($item['quantity'] ?? 0);
        if ($quantity < 1) {
            continue;
        }
        $price = (float) ($item['price_at_addition'] ?? 0);
        $totalItems += $quantity;
        $subtotal += $price * $quantity;
    }

    return [
        'total_items' => $totalItems,
        'subtotal_price' => round($subtotal, 2),
    ];
}

function upsertCustomerCartDocument(
    array $env,
    MongoDB\Driver\Manager $manager,
    string $customerPublicId,
    array $items
): array {
    $customerPublicId = strtoupper(asString($customerPublicId));
    $normalizedItems = normalizeCustomerCartItems($items);
    $summary = customerCartSummary($normalizedItems);
    $db = mongoDbName($env);
    $cartCollection = mongoCollectionName($env, 'shopping_cart');
    $lastUpdated = nowUtc();

    if ((int) ($summary['total_items'] ?? 0) <= 0) {
        mongoDeleteOne($manager, $db, $cartCollection, ['customer_public_id' => $customerPublicId]);
        return [
            'customer_public_id' => $customerPublicId,
            'items' => [],
            'cart_summary' => ['total_items' => 0, 'subtotal_price' => 0.0],
            'last_updated' => $lastUpdated,
        ];
    }

    mongoUpdateOne($manager, $db, $cartCollection, ['customer_public_id' => $customerPublicId], [
        '$set' => [
            'customer_public_id' => $customerPublicId,
            'items' => $normalizedItems,
            'cart_summary' => $summary,
            'last_updated' => $lastUpdated,
        ],
    ], true);

    return [
        'customer_public_id' => $customerPublicId,
        'items' => $normalizedItems,
        'cart_summary' => $summary,
        'last_updated' => $lastUpdated,
    ];
}

function mergeCustomerCartItems(array $env, PDO $pdo, string $customerPublicId, array $incomingItems): array
{
    $manager = mongoManager($env);
    if (!$manager) {
        throw new RuntimeException('Cart service is unavailable.');
    }

    $customerPublicId = strtoupper(asString($customerPublicId));
    if ($customerPublicId === '' || !isValidPublicId($customerPublicId)) {
        throw new RuntimeException('Invalid customer ID.');
    }

    $db = mongoDbName($env);
    $cartCollection = mongoCollectionName($env, 'shopping_cart');
    $existing = mongoFindOne($manager, $db, $cartCollection, ['customer_public_id' => $customerPublicId]);
    $existingItems = normalizeCustomerCartItems(is_array($existing['items'] ?? null) ? $existing['items'] : []);

    $catalogMap = catalogMapByPublicId(loadProductCatalog($env));
    $mergedMap = [];

    foreach ($existingItems as $existingItem) {
        if (!is_array($existingItem)) {
            continue;
        }
        $productPublicId = strtoupper(asString($existingItem['product_public_id'] ?? ''));
        if ($productPublicId === '' || !isValidPublicId($productPublicId)) {
            continue;
        }
        $qty = (int) ($existingItem['quantity'] ?? 0);
        if ($qty < 1) {
            continue;
        }
        $mergedMap[$productPublicId] = [
            'product_public_id' => $productPublicId,
            'product_name' => asString($existingItem['product_name'] ?? ''),
            'quantity' => max(1, min(50, $qty)),
            'price_at_addition' => (float) ($existingItem['price_at_addition'] ?? 0),
            'image_url' => asString($existingItem['image_url'] ?? ''),
            'added_at' => asString($existingItem['added_at'] ?? nowUtc()),
        ];
    }

    foreach ($incomingItems as $incomingItem) {
        if (!is_array($incomingItem)) {
            continue;
        }
        $productPublicId = strtoupper(asString($incomingItem['product_public_id'] ?? ''));
        if ($productPublicId === '' || !isValidPublicId($productPublicId)) {
            continue;
        }

        $qty = (int) ($incomingItem['quantity'] ?? 1);
        if ($qty < 1) {
            continue;
        }
        $qty = min($qty, 50);

        $catalogItem = $catalogMap[$productPublicId] ?? null;
        if (!is_array($catalogItem)) {
            continue;
        }
        if (strtoupper(asString($catalogItem['availability_status'] ?? 'AVAILABLE')) !== 'AVAILABLE') {
            continue;
        }

        $mysqlProduct = mysqlProductByPublicId($pdo, $productPublicId);
        if ($mysqlProduct !== null) {
            if ((int) ($mysqlProduct['is_active'] ?? 0) !== 1) {
                continue;
            }
            if ((int) ($mysqlProduct['quantity'] ?? 0) <= 0) {
                continue;
            }
        }

        $baseName = asString($catalogItem['model_name'] ?? '');
        if ($baseName === '') {
            $baseName = asString($incomingItem['product_name'] ?? 'Unknown Product');
        }
        $basePrice = $mysqlProduct !== null
            ? (float) ($mysqlProduct['selling_price'] ?? 0)
            : (float) ($catalogItem['display_price'] ?? 0);
        if ($basePrice <= 0) {
            $basePrice = (float) ($incomingItem['price_at_addition'] ?? 0);
        }
        $baseImage = asString($catalogItem['image_url'] ?? '');
        if ($baseImage === '') {
            $baseImage = asString($incomingItem['image_url'] ?? '');
        }

        if (isset($mergedMap[$productPublicId])) {
            $mergedMap[$productPublicId]['quantity'] = min(
                50,
                (int) ($mergedMap[$productPublicId]['quantity'] ?? 0) + $qty
            );
            if ((float) ($mergedMap[$productPublicId]['price_at_addition'] ?? 0) <= 0 && $basePrice > 0) {
                $mergedMap[$productPublicId]['price_at_addition'] = $basePrice;
            }
            if (asString($mergedMap[$productPublicId]['product_name'] ?? '') === '' && $baseName !== '') {
                $mergedMap[$productPublicId]['product_name'] = $baseName;
            }
            if (asString($mergedMap[$productPublicId]['image_url'] ?? '') === '' && $baseImage !== '') {
                $mergedMap[$productPublicId]['image_url'] = $baseImage;
            }
            continue;
        }

        $mergedMap[$productPublicId] = [
            'product_public_id' => $productPublicId,
            'product_name' => $baseName,
            'quantity' => $qty,
            'price_at_addition' => $basePrice,
            'image_url' => $baseImage,
            'added_at' => nowUtc(),
        ];
    }

    return upsertCustomerCartDocument($env, $manager, $customerPublicId, array_values($mergedMap));
}

function loadProductCatalog(array $env): array
{
    $fallbackCatalog = readProductCatalogFallbackFile();
    $fallbackToggle = strtolower(asString(envValue($env, 'MONGO_PRODUCT_CATALOG_FALLBACK', 'false')));
    $allowFallback = in_array($fallbackToggle, ['1', 'true', 'yes', 'on'], true);

    $manager = mongoManager($env);
    $db = mongoDbName($env);
    if (!$manager) {
        return $allowFallback ? $fallbackCatalog : [];
    }

    try {
        $docs = mongoFindMany($manager, $db, mongoCollectionName($env, 'product_catalog'), [], ['sort' => ['model_name' => 1]]);
        if (count($docs) > 0) {
            if (count($fallbackCatalog) === 0) {
                return $docs;
            }

            $fallbackByPublicId = catalogMapByPublicId($fallbackCatalog);
            $fallbackByLookupKey = catalogMapByLookupKey($fallbackCatalog);

            $merged = [];
            foreach ($docs as $doc) {
                if (!is_array($doc)) {
                    continue;
                }

                $fallback = null;
                $publicId = asString($doc['product_public_id'] ?? '');
                if ($publicId !== '' && isset($fallbackByPublicId[$publicId]) && is_array($fallbackByPublicId[$publicId])) {
                    $fallback = $fallbackByPublicId[$publicId];
                }

                if (!is_array($fallback)) {
                    $lookupKey = normalizeCatalogLookupKey(asString($doc['model_name'] ?? $doc['product_name'] ?? ''));
                    if ($lookupKey !== '' && isset($fallbackByLookupKey[$lookupKey]) && is_array($fallbackByLookupKey[$lookupKey])) {
                        $fallback = $fallbackByLookupKey[$lookupKey];
                    }
                }

                if (is_array($fallback)) {
                    $doc = mergeCatalogDocumentWithFallback($doc, $fallback);
                }

                $merged[] = $doc;
            }

            return $merged;
        }
    } catch (Throwable) {
        return $allowFallback ? $fallbackCatalog : [];
    }

    return $allowFallback ? $fallbackCatalog : [];
}

function loadProductCatalogMongoOnly(array $env): array
{
    $manager = mongoManager($env);
    if (!$manager) {
        return [];
    }

    try {
        return mongoFindMany(
            $manager,
            mongoDbName($env),
            mongoCollectionName($env, 'product_catalog'),
            [],
            ['sort' => ['model_name' => 1]]
        );
    } catch (Throwable) {
        return [];
    }
}

function catalogMapByPublicId(array $catalog): array
{
    $map = [];
    foreach ($catalog as $item) {
        $publicId = asString($item['product_public_id'] ?? '');
        if ($publicId === '') {
            continue;
        }
        $map[$publicId] = $item;
        $map[strtoupper($publicId)] = $item;
    }
    return $map;
}

function normalizeCatalogLookupKey(string $value): string
{
    $upper = strtoupper(trim($value));
    if ($upper === '') {
        return '';
    }

    return preg_replace('/[^A-Z0-9]+/', '', $upper) ?? '';
}

function catalogQualityScore(array $item): int
{
    $score = 0;

    $brand = asString($item['brand'] ?? '');
    if ($brand !== '' && strcasecmp($brand, 'Generic') !== 0) {
        $score += 3;
    }

    if (asString($item['short_description'] ?? '') !== '') {
        $score += 1;
    }
    if (asString($item['long_description'] ?? '') !== '') {
        $score += 1;
    }
    if (asString($item['image_url'] ?? '') !== '') {
        $score += 1;
    }

    if (is_array($item['specifications'] ?? null) && count((array) $item['specifications']) > 0) {
        $score += 1;
    }
    if (is_array($item['tags'] ?? null) && count((array) $item['tags']) > 0) {
        $score += 1;
    }

    return $score;
}

function catalogMapByLookupKey(array $catalog): array
{
    $map = [];
    $scores = [];

    foreach ($catalog as $item) {
        if (!is_array($item)) {
            continue;
        }

        $lookupKey = normalizeCatalogLookupKey(asString($item['model_name'] ?? $item['product_name'] ?? ''));
        if ($lookupKey === '') {
            continue;
        }

        $score = catalogQualityScore($item);
        if (!isset($map[$lookupKey]) || $score > (int) ($scores[$lookupKey] ?? -1)) {
            $map[$lookupKey] = $item;
            $scores[$lookupKey] = $score;
        }
    }

    return $map;
}

function readProductCatalogFallbackFile(): array
{
    $fallbackFile = __DIR__ . '/../../mongodb_seed/product_catalog.json';
    if (!is_file($fallbackFile)) {
        return [];
    }

    $raw = file_get_contents($fallbackFile);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function mergeCatalogDocumentWithFallback(array $doc, array $fallback): array
{
    $merged = $doc;

    $docBrand = asString($doc['brand'] ?? '');
    $fallbackBrand = asString($fallback['brand'] ?? '');
    if (
        ($docBrand === '' || strcasecmp($docBrand, 'Generic') === 0)
        && $fallbackBrand !== ''
        && strcasecmp($fallbackBrand, 'Generic') !== 0
    ) {
        $merged['brand'] = $fallbackBrand;
    }

    $docCategory = asString($doc['category'] ?? '');
    $fallbackCategory = asString($fallback['category'] ?? '');
    if (($docCategory === '' || strcasecmp($docCategory, 'General') === 0) && $fallbackCategory !== '') {
        $merged['category'] = $fallbackCategory;
    }

    $docSubCategory = asString($doc['sub_category'] ?? '');
    $fallbackSubCategory = asString($fallback['sub_category'] ?? '');
    if (($docSubCategory === '' || strcasecmp($docSubCategory, 'General') === 0) && $fallbackSubCategory !== '') {
        $merged['sub_category'] = $fallbackSubCategory;
    }

    $stringKeys = ['model_name', 'short_description', 'long_description', 'image_url'];
    foreach ($stringKeys as $key) {
        if (asString($merged[$key] ?? '') === '' && asString($fallback[$key] ?? '') !== '') {
            $merged[$key] = $fallback[$key];
        }
    }

    $arrayKeys = ['specifications', 'tags', 'ui_flags', 'reviews'];
    foreach ($arrayKeys as $key) {
        $docValue = $merged[$key] ?? null;
        $fallbackValue = $fallback[$key] ?? null;
        if ((!is_array($docValue) || count($docValue) === 0) && is_array($fallbackValue) && count($fallbackValue) > 0) {
            $merged[$key] = $fallbackValue;
        }
    }

    $docPrice = (float) ($merged['display_price'] ?? 0);
    $fallbackPrice = (float) ($fallback['display_price'] ?? 0);
    if ($docPrice <= 0 && $fallbackPrice > 0) {
        $merged['display_price'] = $fallbackPrice;
    }

    if (asString($merged['product_public_id'] ?? '') === '' && asString($fallback['product_public_id'] ?? '') !== '') {
        $merged['product_public_id'] = $fallback['product_public_id'];
    }

    return $merged;
}

function mysqlProductByPublicId(PDO $pdo, string $publicId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM api_product WHERE UPPER(public_id) = UPPER(:id) LIMIT 1');
    $stmt->execute(['id' => $publicId]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function mysqlProductById(PDO $pdo, int $productId): ?array
{
    if ($productId <= 0) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM api_product WHERE productID = :id LIMIT 1');
    $stmt->execute(['id' => $productId]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function lockProductRowById(PDO $pdo, int $productId): ?array
{
    if ($productId <= 0) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT productID, public_id, product_name, quantity, is_active
         FROM product
         WHERE productID = :product_id
         LIMIT 1
         FOR UPDATE'
    );
    $stmt->execute(['product_id' => $productId]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function mysqlProductByName(PDO $pdo, string $name): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM api_product WHERE UPPER(product_name) = UPPER(:name) LIMIT 1');
    $stmt->execute(['name' => $name]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function ensureMysqlProduct(PDO $pdo, array $catalogItem): array
{
    $publicId = asString($catalogItem['product_public_id'] ?? '');
    $name = asString($catalogItem['model_name'] ?? $catalogItem['product_name'] ?? '');
    $price = (float) ($catalogItem['display_price'] ?? 0);

    if ($publicId !== '') {
        $existing = mysqlProductByPublicId($pdo, $publicId);
        if ($existing) {
            return $existing;
        }
    }

    if ($name !== '') {
        $existingByName = mysqlProductByName($pdo, $name);
        if ($existingByName) {
            if ($publicId !== '' && ($existingByName['public_id'] ?? '') !== $publicId) {
                $stmt = $pdo->prepare('CALL product_public_id_update(:id, :public_id)');
                $stmt->execute([
                    'id' => $existingByName['productID'],
                    'public_id' => $publicId,
                ]);
                $stmt->closeCursor();
                $existingByName['public_id'] = $publicId;
            }
            return $existingByName;
        }
    }

    $insertPublic = $publicId !== '' ? $publicId : randomPublicId('PR');
    $stmt = $pdo->prepare('CALL record_new_product(:public_id, :product_name, :quantity, :selling_price)');
    $stmt->execute([
        'public_id' => $insertPublic,
        'product_name' => $name !== '' ? $name : 'Unnamed Product',
        'quantity' => ($catalogItem['is_in_stock'] ?? false) ? 100 : 0,
        'selling_price' => $price,
    ]);
    $stmt->closeCursor();

    $row = mysqlProductByPublicId($pdo, $insertPublic);
    return is_array($row) ? $row : [];
}

function normalizeCatalogProduct(array $catalogItem, ?array $mysqlProduct = null, array $reviews = []): array
{
    $status = (string) ($catalogItem['availability_status'] ?? 'AVAILABLE');
    $isAvailable = strtoupper($status) === 'AVAILABLE';
    $resolvedName = asString($catalogItem['model_name'] ?? $catalogItem['product_name'] ?? '');
    if (is_array($mysqlProduct)) {
        $isAvailable = ((int) ($mysqlProduct['is_active'] ?? 0) === 1) && ((int) ($mysqlProduct['quantity'] ?? 0) > 0);
        $mysqlName = asString($mysqlProduct['product_name'] ?? '');
        if ($mysqlName !== '') {
            $resolvedName = $mysqlName;
        }
    }

    $ratingAvg = (float) ($catalogItem['reviews']['average_rating'] ?? 0);

    return [
        'id' => $mysqlProduct ? (int) ($mysqlProduct['productID'] ?? 0) : crc32((string) ($catalogItem['product_public_id'] ?? '0')),
        'publicId' => asString($catalogItem['product_public_id'] ?? ''),
        'name' => $resolvedName,
        'category' => asString($catalogItem['sub_category'] ?? $catalogItem['category'] ?? 'General'),
        'price' => (float) ($mysqlProduct['selling_price'] ?? $catalogItem['display_price'] ?? 0),
        'status' => $isAvailable ? 'AVAILABLE' : 'SOLD OUT',
        'image' => asString($catalogItem['image_url'] ?? ''),
        'description' => asString($catalogItem['long_description'] ?? $catalogItem['short_description'] ?? ''),
        'specs' => array_values(array_map(
            static fn($k, $v) => ucfirst(str_replace('_', ' ', (string) $k)) . ': ' . (string) $v,
            array_keys((array) ($catalogItem['specifications'] ?? [])),
            array_values((array) ($catalogItem['specifications'] ?? [])),
        )),
        'rating' => $ratingAvg,
        'reviews' => $reviews,
        'stockLevel' => (int) ($mysqlProduct['quantity'] ?? 0),
        'wholesaleCost' => 0,
        'isActive' => (int) ($mysqlProduct['is_active'] ?? 1) === 1,
    ];
}

function getCustomerByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM api_customer
         WHERE email_address = :email
           AND deleted_at IS NULL
           AND status = "Active"
         ORDER BY customerID ASC
         LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function getCustomerByContact(PDO $pdo, string $contactNumber): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM api_customer
         WHERE contact_number = :contact_number
           AND deleted_at IS NULL
           AND status = "Active"
         ORDER BY customerID ASC
         LIMIT 1'
    );
    $stmt->execute(['contact_number' => $contactNumber]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function getCustomerByPublicId(PDO $pdo, string $publicId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM api_customer
         WHERE public_id = :public_id
           AND deleted_at IS NULL
           AND status = "Active"
         LIMIT 1'
    );
    $stmt->execute(['public_id' => $publicId]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function lockActiveCustomerRowByPublicId(PDO $pdo, string $publicId): ?array
{
    $normalizedPublicId = strtoupper(asString($publicId));
    if ($normalizedPublicId === '') {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT customerID, public_id, status, deleted_at
         FROM customer
         WHERE UPPER(public_id) = :public_id
         LIMIT 1
         FOR UPDATE'
    );
    $stmt->execute(['public_id' => $normalizedPublicId]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function getCustomerByPublicIdAny(PDO $pdo, string $publicId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM api_customer WHERE public_id = :public_id LIMIT 1');
    $stmt->execute(['public_id' => $publicId]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function getCustomersByEmailAny(PDO $pdo, string $email): array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM api_customer
         WHERE email_address = :email
         ORDER BY (deleted_at IS NULL) DESC, customerID ASC'
    );
    $stmt->execute(['email' => $email]);
    return $stmt->fetchAll() ?: [];
}

function getCustomersByContactAny(PDO $pdo, string $contactNumber): array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM api_customer
         WHERE contact_number = :contact_number
         ORDER BY (deleted_at IS NULL) DESC, customerID ASC'
    );
    $stmt->execute(['contact_number' => $contactNumber]);
    return $stmt->fetchAll() ?: [];
}

function getCustomerByEmailAny(PDO $pdo, string $email): ?array
{
    $rows = getCustomersByEmailAny($pdo, $email);
    return $rows[0] ?? null;
}

function getCustomerByContactAny(PDO $pdo, string $contactNumber): ?array
{
    $rows = getCustomersByContactAny($pdo, $contactNumber);
    return $rows[0] ?? null;
}

function getActiveCustomerByEmailExcluding(PDO $pdo, string $email, int $excludeCustomerId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM api_customer
         WHERE email_address = :email
           AND deleted_at IS NULL
           AND status = "Active"
           AND customerID <> :exclude
         LIMIT 1'
    );
    $stmt->execute([
        'email' => $email,
        'exclude' => $excludeCustomerId,
    ]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function getActiveCustomerByContactExcluding(PDO $pdo, string $contactNumber, int $excludeCustomerId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM api_customer
         WHERE contact_number = :contact
           AND deleted_at IS NULL
           AND status = "Active"
           AND customerID <> :exclude
         LIMIT 1'
    );
    $stmt->execute([
        'contact' => $contactNumber,
        'exclude' => $excludeCustomerId,
    ]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function mergeWalkInAndOnlineCustomer(PDO $pdo, int $walkInCustomerId, int $onlineCustomerId): array
{
    if ($walkInCustomerId <= 0 || $onlineCustomerId <= 0 || $walkInCustomerId === $onlineCustomerId) {
        throw new RuntimeException('Invalid customer merge request.');
    }

    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }
    try {
        $walkInStmt = $pdo->prepare('SELECT * FROM api_customer WHERE customerID = :id LIMIT 1');
        $walkInStmt->execute(['id' => $walkInCustomerId]);
        $walkIn = $walkInStmt->fetch();

        $onlineStmt = $pdo->prepare('SELECT * FROM api_customer WHERE customerID = :id LIMIT 1');
        $onlineStmt->execute(['id' => $onlineCustomerId]);
        $online = $onlineStmt->fetch();

        if (!is_array($walkIn) || !is_array($online)) {
            throw new RuntimeException('Customer merge target was not found.');
        }

        if ((string) ($walkIn['customer_type'] ?? '') !== 'Walk-in') {
            throw new RuntimeException('Merge target must be a walk-in customer.');
        }
        if ((string) ($online['customer_type'] ?? '') !== 'Registered') {
            throw new RuntimeException('Merge source must be a registered customer.');
        }
        if ((string) ($walkIn['status'] ?? '') !== 'Active' || (string) ($online['status'] ?? '') !== 'Active') {
            throw new RuntimeException('Only active accounts can be merged.');
        }

        $mergeProfile = $pdo->prepare(
            'UPDATE customer walkin
             JOIN customer online ON online.customerID = :online_id
             SET walkin.first_name = COALESCE(NULLIF(TRIM(online.first_name), ""), walkin.first_name),
                 walkin.last_name = COALESCE(NULLIF(TRIM(online.last_name), ""), walkin.last_name),
                 walkin.middle_name = COALESCE(NULLIF(TRIM(online.middle_name), ""), walkin.middle_name),
                 walkin.customer_type = "Registered",
                 walkin.password_hash = COALESCE(NULLIF(TRIM(online.password_hash), ""), walkin.password_hash),
                 walkin.email_address = COALESCE(NULLIF(TRIM(online.email_address), ""), walkin.email_address),
                 walkin.contact_number = COALESCE(NULLIF(TRIM(online.contact_number), ""), walkin.contact_number),
                 walkin.street_address = COALESCE(NULLIF(TRIM(online.street_address), ""), walkin.street_address),
                 walkin.barangay = COALESCE(NULLIF(TRIM(online.barangay), ""), walkin.barangay),
                 walkin.city_municipality = COALESCE(NULLIF(TRIM(online.city_municipality), ""), walkin.city_municipality),
                 walkin.province = COALESCE(NULLIF(TRIM(online.province), ""), walkin.province),
                 walkin.zip_code = COALESCE(NULLIF(TRIM(online.zip_code), ""), walkin.zip_code),
                 walkin.current_credit = walkin.current_credit + COALESCE(online.current_credit, 0),
                 walkin.status = "Active",
                 walkin.deleted_at = NULL
             WHERE walkin.customerID = :walkin_id'
        );
        $mergeProfile->execute([
            'walkin_id' => $walkInCustomerId,
            'online_id' => $onlineCustomerId,
        ]);

        $transferOrders = $pdo->prepare('UPDATE sale SET customerID = :walkin_id WHERE customerID = :online_id');
        $transferOrders->execute([
            'walkin_id' => $walkInCustomerId,
            'online_id' => $onlineCustomerId,
        ]);

        $softDeleteOnline = $pdo->prepare(
            'UPDATE customer
             SET status = "Merged",
                 deleted_at = NOW()
             WHERE customerID = :online_id'
        );
        $softDeleteOnline->execute(['online_id' => $onlineCustomerId]);

        $fetchMerged = $pdo->prepare('SELECT * FROM api_customer WHERE customerID = :id LIMIT 1');
        $fetchMerged->execute(['id' => $walkInCustomerId]);
        $merged = $fetchMerged->fetch();

        if (!is_array($merged)) {
            throw new RuntimeException('Failed to load merged customer record.');
        }

        if ($ownsTransaction) {
            $pdo->commit();
        }
        return $merged;
    } catch (Throwable $exception) {
        if ($ownsTransaction) {
            rollbackIfInTransaction($pdo);
        }
        throw $exception;
    }
}

function getEmployeeByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM api_employee WHERE email_address = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function getEmployeeByPublicId(PDO $pdo, string $publicId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM api_employee WHERE public_id = :public_id LIMIT 1');
    $stmt->execute(['public_id' => $publicId]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function rollbackIfInTransaction(PDO $pdo): void
{
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

function firstEmployeeId(PDO $pdo): ?int
{
    $row = $pdo->query('SELECT employeeID FROM api_employee ORDER BY employeeID ASC LIMIT 1')->fetch();
    if (!is_array($row)) {
        return null;
    }
    return (int) ($row['employeeID'] ?? 0);
}

function firstEmployeePublicId(PDO $pdo): ?string
{
    $row = $pdo->query('SELECT public_id FROM api_employee ORDER BY employeeID ASC LIMIT 1')->fetch();
    if (!is_array($row)) {
        return null;
    }
    $publicId = asString($row['public_id'] ?? '');
    return $publicId !== '' ? $publicId : null;
}

function customerHasVerifiedPurchase(PDO $pdo, int $customerId, int $productId): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1
         FROM api_sale s
         JOIN api_sale_item si ON si.saleID = s.saleID
         WHERE s.customerID = :customerID
           AND si.productID = :productID
           AND s.sale_status IN ("Delivered", "Completed")
         LIMIT 1'
    );
    $stmt->execute([
        'customerID' => $customerId,
        'productID' => $productId,
    ]);
    return is_array($stmt->fetch());
}

function returnedQuantityForSaleItem(PDO $pdo, int $saleItemId): int
{
    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(ri.return_quantity), 0) AS qty
         FROM api_return_item ri
         JOIN api_return_transaction rt ON rt.returnID = ri.returnID
         WHERE ri.sale_itemID = :sale_itemID
           AND rt.return_progress IN ("Requested", "In Process", "Approved", "Finalized")'
    );
    $stmt->execute(['sale_itemID' => $saleItemId]);
    $row = $stmt->fetch();
    return (int) ($row['qty'] ?? 0);
}

function catalogReviewStats(array $env, MongoDB\Driver\Manager $manager, string $dbName, string $productPublicId): array
{
    $reviews = mongoFindMany(
        $manager,
        $dbName,
        mongoCollectionName($env, 'product_reviews'),
        ['product_public_id' => $productPublicId],
        ['projection' => ['rating' => 1]]
    );

    $count = 0;
    $sum = 0;
    foreach ($reviews as $review) {
        $rating = (int) ($review['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            continue;
        }
        $count++;
        $sum += $rating;
    }

    return [
        'total_reviews' => $count,
        'rating_sum' => $sum,
        'average_rating' => $count > 0 ? round($sum / $count, 2) : 0,
    ];
}

