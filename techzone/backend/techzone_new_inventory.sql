/* Techzone ERP + e-commerce schema. Email normalized to lowercase; customer/employee phone normalized to 63XXXXXXXXXX; supplier phone normalized to 0288881111 or 09170001111. */

DROP DATABASE IF EXISTS techzone_new_inventory;

CREATE DATABASE techzone_new_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE techzone_new_inventory;


CREATE TABLE customer (
                          customerID INT AUTO_INCREMENT PRIMARY KEY,
                          public_id VARCHAR(20) UNIQUE NOT NULL,
                          first_name VARCHAR(50) NOT NULL,
                          last_name VARCHAR(50) NOT NULL,
                          middle_name VARCHAR(50),
                          customer_type ENUM('Walk-in', 'Registered') NOT NULL DEFAULT 'Walk-in',
                          password_hash VARCHAR(255),
                          status ENUM('Active', 'Merged', 'Deleted_by_User') NOT NULL DEFAULT 'Active',
                          deleted_at DATETIME NULL DEFAULT NULL,
                          email_address VARCHAR(100),
                          current_credit DECIMAL(10,2) NOT NULL DEFAULT 0,
                          contact_number VARCHAR(12),
                          street_address VARCHAR(45),
                          barangay VARCHAR(45) NOT NULL,
                          province VARCHAR(45),
                          city_municipality VARCHAR(45) NOT NULL,
                          zip_code VARCHAR(4),
                          created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                          CONSTRAINT chk_at_least_one_contact CHECK (email_address IS NOT NULL OR contact_number IS NOT NULL),
                          CONSTRAINT chk_customer_status CHECK (status IN ('Active', 'Merged', 'Deleted_by_User')),
                          CONSTRAINT chk_customer_credit_nonnegative CHECK (current_credit >= 0),
                          CONSTRAINT chk_customer_first_name_nonempty CHECK (TRIM(first_name) <> ''),
                          CONSTRAINT chk_customer_last_name_nonempty CHECK (TRIM(last_name) <> ''),
                          CONSTRAINT chk_customer_barangay_nonempty CHECK (TRIM(barangay) <> ''),
                          CONSTRAINT chk_customer_city_nonempty CHECK (TRIM(city_municipality) <> ''),
                          CONSTRAINT chk_customer_email_nonempty CHECK (email_address IS NULL OR email_address <> ''),
                          CONSTRAINT chk_customer_contact_nonempty CHECK (contact_number IS NULL OR contact_number <> ''),
                          CONSTRAINT chk_customer_email_format CHECK (email_address IS NULL OR email_address REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$'),
                          CONSTRAINT chk_customer_contact_format CHECK (contact_number IS NULL OR contact_number REGEXP '^63[0-9]{10}$'),
                          CONSTRAINT chk_customer_zip_format CHECK (zip_code IS NULL OR zip_code REGEXP '^[0-9]{4}$'),
                          UNIQUE KEY uq_customer_email_deleted_at (email_address, deleted_at),
                          UNIQUE KEY uq_customer_contact_deleted_at (contact_number, deleted_at),
                          INDEX idx_customer_email_address (email_address),
                          INDEX idx_customer_contact_number (contact_number),
                          INDEX idx_customer_status (status),
                          INDEX idx_customer_fullname (first_name, last_name),
                          INDEX idx_customer_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE product (
                         productID INT AUTO_INCREMENT PRIMARY KEY,
                         public_id VARCHAR(20) UNIQUE NOT NULL,
                         product_name VARCHAR(100) NOT NULL,
                         quantity INT UNSIGNED NOT NULL,
                         selling_price DECIMAL(10,2) NOT NULL,
                         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         deleted_at DATETIME NULL DEFAULT NULL,
                         is_active BOOLEAN NOT NULL DEFAULT 1,
                         CONSTRAINT chk_product_price_nonnegative CHECK (selling_price >= 0),
                         CONSTRAINT chk_product_name_nonempty CHECK (product_name <> ''),
                         INDEX idx_product_name (product_name),
                         INDEX idx_product_active (is_active),
                         INDEX idx_product_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE supplier (
                          supplierID INT AUTO_INCREMENT PRIMARY KEY,
                          public_id VARCHAR(20) UNIQUE NOT NULL,
                          supplier_name VARCHAR(100) NOT NULL,
                          contact_first_name VARCHAR(50) NOT NULL,
                          contact_last_name VARCHAR(50) NOT NULL,
                          contact_number VARCHAR(12),
                          email_address VARCHAR(100),
                          street_address VARCHAR(45),
                          barangay VARCHAR(45) NOT NULL,
                          province VARCHAR(45),
                          city_municipality VARCHAR(45) NOT NULL,
                          zip_code VARCHAR(4),
                          created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          deleted_at DATETIME NULL DEFAULT NULL,
                          is_active BOOLEAN NOT NULL DEFAULT 1,

                          CONSTRAINT chk_at_least_one_contact_supplier CHECK (email_address IS NOT NULL OR contact_number IS NOT NULL),
                          CONSTRAINT chk_supplier_contact_first_name_nonempty CHECK (TRIM(contact_first_name) <> ''),
                          CONSTRAINT chk_supplier_contact_last_name_nonempty CHECK (TRIM(contact_last_name) <> ''),
                          CONSTRAINT chk_supplier_name_nonempty CHECK (TRIM(supplier_name) <> ''),
                          CONSTRAINT chk_supplier_barangay_nonempty CHECK (TRIM(barangay) <> ''),
                          CONSTRAINT chk_supplier_city_nonempty CHECK (TRIM(city_municipality) <> ''),
                          CONSTRAINT chk_supplier_email_nonempty CHECK (email_address IS NULL OR email_address <> ''),
                          CONSTRAINT chk_supplier_contact_nonempty CHECK (contact_number IS NULL OR TRIM(contact_number) <> ''),
                          CONSTRAINT chk_supplier_email_format CHECK (email_address IS NULL OR email_address REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$'),
                          CONSTRAINT chk_supplier_contact_format CHECK (contact_number IS NULL OR contact_number REGEXP '^(02[0-9]{8}|09[0-9]{9})$'),
                          CONSTRAINT chk_supplier_zip_format CHECK (zip_code IS NULL OR zip_code REGEXP '^[0-9]{4}$'),
                          UNIQUE KEY uq_supplier_email_deleted_at (email_address, deleted_at),
                          UNIQUE KEY uq_supplier_contact_deleted_at (contact_number, deleted_at),
                          INDEX idx_supplier_name (supplier_name),
                          INDEX idx_supplier_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE product_supplier (
                                  supplierID INT NOT NULL,
                                  productID INT NOT NULL,
                                  supplier_product_name VARCHAR(100) NOT NULL,
                                  wholesale_cost DECIMAL(10,2) NOT NULL,
                                  deleted_at DATETIME NULL DEFAULT NULL,
                                  is_active BOOLEAN NOT NULL DEFAULT 1,
                                  PRIMARY KEY (supplierID, productID),
                                  CONSTRAINT chk_supplier_cost_nonnegative CHECK (wholesale_cost >= 0),
                                  INDEX idx_product_supplier_deleted_at (deleted_at),
                                  FOREIGN KEY (supplierID) REFERENCES supplier(supplierID) ON UPDATE CASCADE ON DELETE CASCADE,
                                  FOREIGN KEY (productID) REFERENCES product(productID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE employee (
                          employeeID INT AUTO_INCREMENT PRIMARY KEY,
                          public_id VARCHAR(20) UNIQUE NOT NULL,
                          first_name VARCHAR(50) NOT NULL,
                          last_name VARCHAR(50) NOT NULL,
                          employee_role VARCHAR(50) NOT NULL,
                          employee_status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
                          email_address VARCHAR(100) NOT NULL,
                          password_hash VARCHAR(255) NOT NULL,
                          contact_number VARCHAR(12),
                          created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          deleted_at DATETIME NULL DEFAULT NULL,
                          CONSTRAINT chk_employee_first_name_nonempty CHECK (TRIM(first_name) <> ''),
                          CONSTRAINT chk_employee_last_name_nonempty CHECK (TRIM(last_name) <> ''),
                          CONSTRAINT chk_employee_email_nonempty CHECK (email_address <> ''),
                          CONSTRAINT chk_employee_email_format CHECK (email_address REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$'),
                          CONSTRAINT chk_employee_role_nonempty CHECK (employee_role <> ''),
                          CONSTRAINT chk_employee_contact_format CHECK (contact_number IS NULL OR contact_number REGEXP '^63[0-9]{10}$'),
                          UNIQUE KEY uq_employee_email_deleted_at (email_address, deleted_at),
                          INDEX idx_employee_status (employee_status),
                          INDEX idx_employee_role (employee_role),
                          INDEX idx_employee_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE sale (
                      saleID INT AUTO_INCREMENT PRIMARY KEY,
                      public_id VARCHAR(20) UNIQUE NOT NULL,
                      sale_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                      customerID INT NOT NULL,
                      employeeID INT,
                      shipping_name VARCHAR(100),
                      shipping_street VARCHAR(100),
                      shipping_barangay VARCHAR(50),
                      shipping_city_municipality VARCHAR(50),
                      shipping_province VARCHAR(50),
                      shipping_zip_code VARCHAR(10),
                      fulfillment_method ENUM('Pickup', 'Delivery', 'Walk-in') NOT NULL,
                      sale_status ENUM('Pending', 'Processing', 'Ready for Pickup', 'Shipped', 'Delivered', 'Completed', 'Cancelled') DEFAULT 'Pending' NOT NULL,
                      tracking_number VARCHAR(50),
                      courier_name VARCHAR(50),
                      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      deleted_at DATETIME NULL DEFAULT NULL,
                      CONSTRAINT chk_sale_total_nonnegative CHECK (total_amount >= 0),
                      CONSTRAINT chk_sale_shipping_required CHECK (
                          fulfillment_method <> 'Delivery'
                          OR (
                              shipping_name IS NOT NULL AND TRIM(shipping_name) <> ''
                              AND shipping_street IS NOT NULL AND TRIM(shipping_street) <> ''
                              AND shipping_barangay IS NOT NULL AND TRIM(shipping_barangay) <> ''
                              AND shipping_city_municipality IS NOT NULL AND TRIM(shipping_city_municipality) <> ''
                              AND shipping_province IS NOT NULL AND TRIM(shipping_province) <> ''
                              AND shipping_zip_code IS NOT NULL AND TRIM(shipping_zip_code) <> ''
                          )
                      ),
                      CONSTRAINT chk_sale_pickup_no_tracking CHECK (
                          fulfillment_method <> 'Pickup'
                          OR (
                              (tracking_number IS NULL OR TRIM(tracking_number) = '')
                              AND (courier_name IS NULL OR TRIM(courier_name) = '')
                          )
                      ),
                      CONSTRAINT chk_sale_tracking_required CHECK (
                          sale_status NOT IN ('Shipped', 'Delivered')
                          OR (
                              tracking_number IS NOT NULL AND TRIM(tracking_number) <> ''
                              AND courier_name IS NOT NULL AND TRIM(courier_name) <> ''
                          )
                      ),
                      CONSTRAINT chk_sale_shipping_zip_format CHECK (shipping_zip_code IS NULL OR shipping_zip_code REGEXP '^[0-9]{4}$'),
                      INDEX idx_sale_date (sale_date),
                      INDEX idx_sale_customer (customerID),
                      INDEX idx_sale_employee (employeeID),
                      INDEX idx_sale_status (sale_status),
                      INDEX idx_sale_deleted_at (deleted_at),
                      FOREIGN KEY (customerID) REFERENCES customer(customerID) ON UPDATE CASCADE ON DELETE RESTRICT,
                      FOREIGN KEY (employeeID) REFERENCES employee(employeeID) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE sale_item (
                           sale_itemID INT AUTO_INCREMENT PRIMARY KEY,
                           quantity_sold INT UNSIGNED NOT NULL,
                           price_at_sale DECIMAL(10,2) NOT NULL,
                           serial_number VARCHAR(30),
                           deleted_at DATETIME NULL DEFAULT NULL,
                           saleID INT NOT NULL,
                           productID INT NOT NULL,
                           CONSTRAINT chk_sale_item_qty_positive CHECK (quantity_sold > 0),
                           CONSTRAINT chk_sale_item_price_nonnegative CHECK (price_at_sale >= 0),
                           CONSTRAINT chk_sale_item_serial_nonempty CHECK (serial_number IS NULL OR TRIM(serial_number) <> ''),
                           INDEX idx_sale_item_sale (saleID),
                           INDEX idx_sale_item_product (productID),
                           INDEX idx_sale_item_deleted_at (deleted_at),
                           FOREIGN KEY (saleID) REFERENCES sale(saleID) ON UPDATE CASCADE ON DELETE CASCADE,
                           FOREIGN KEY (productID) REFERENCES product(productID) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE inventory_transaction (
                                       transID INT AUTO_INCREMENT PRIMARY KEY,
                                       transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                       quantity_change INT NOT NULL,
                                       transaction_type ENUM('Sale', 'Return','Replacement','Restock','Cancelled Sale') NOT NULL,
                                       referenceID INT,
                                       productID INT NOT NULL,
                                       employeeID INT NOT NULL,
                                       deleted_at DATETIME NULL DEFAULT NULL,
                                       CONSTRAINT chk_inventory_qty_nonzero CHECK (quantity_change <> 0),
                                       INDEX idx_inventory_employee (employeeID),
                                       INDEX idx_inventory_date (transaction_date),
                                       INDEX idx_inventory_product_type (productID, transaction_type),
                                       INDEX idx_inventory_deleted_at (deleted_at),
                                       FOREIGN KEY (productID) REFERENCES product(productID) ON UPDATE CASCADE ON DELETE RESTRICT,
                                       FOREIGN KEY (employeeID) REFERENCES employee(employeeID) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE credit_history (
                                credit_transactionID INT AUTO_INCREMENT PRIMARY KEY,
                                customerID INT NOT NULL,
                                transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                                amount DECIMAL(10,2) NOT NULL,
                                transaction_type ENUM('REFUND', 'PURCHASE', 'ADJUSTMENT') NOT NULL,
                                reference_id INT,
                                balance_snapshot DECIMAL(10,2),
                                deleted_at DATETIME NULL DEFAULT NULL,
                          CONSTRAINT chk_credit_amount_nonzero CHECK (amount <> 0),
                                CONSTRAINT chk_credit_balance_nonnegative CHECK (balance_snapshot IS NULL OR balance_snapshot >= 0),
                                INDEX idx_credit_customer (customerID),
                                INDEX idx_credit_date (transaction_date),
                                INDEX idx_credit_deleted_at (deleted_at),
                                FOREIGN KEY (customerID) REFERENCES customer(customerID) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;




CREATE TABLE return_transaction (
                                    returnID INT AUTO_INCREMENT PRIMARY KEY,
                                    public_id VARCHAR(20) UNIQUE NOT NULL,
                                    date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
                                    refund_amount DECIMAL(10,2) DEFAULT 0.00,
                                    employeeID INT NOT NULL,
                                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                    return_progress ENUM('Requested', 'In Process', 'Approved', 'Rejected', 'Finalized') DEFAULT 'Requested' NOT NULL,
                                    tracking_no VARCHAR(45),
                                    return_method ENUM('Drop-off', 'Courier'),
                                    saleID INT NOT NULL,
                                    deleted_at DATETIME NULL DEFAULT NULL,
                                    CONSTRAINT chk_return_refund_nonnegative CHECK (refund_amount >= 0),
                                    INDEX idx_return_sale (saleID),
                                    INDEX idx_return_employee (employeeID),
                                    INDEX idx_return_progress (return_progress),
                                    INDEX idx_return_date (date_created),
                                    INDEX idx_return_deleted_at (deleted_at),
                                    FOREIGN KEY (saleID) REFERENCES sale(saleID) ON UPDATE CASCADE ON DELETE RESTRICT,
                                    FOREIGN KEY (employeeID) REFERENCES employee(employeeID) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE payment (
                         paymentID INT AUTO_INCREMENT PRIMARY KEY,
                         public_id VARCHAR(20) UNIQUE NOT NULL,
                         payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                         updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
                         payment_method ENUM('Cash', 'GCash', 'Card', 'Store Credit') NOT NULL,
                         payment_status ENUM('Completed', 'Pending', 'Failed', 'Refunded', 'Cancelled') NOT NULL,
                         saleID INT NOT NULL,
                         deleted_at DATETIME NULL DEFAULT NULL,
                         CONSTRAINT chk_payment_public_id_format CHECK (public_id REGEXP '^PY-'),
                         INDEX idx_payment_sale (saleID),
                         INDEX idx_payment_date (payment_date),
                         INDEX idx_payment_status (payment_status),
                         INDEX idx_payment_deleted_at (deleted_at),
                         FOREIGN KEY (saleID) REFERENCES sale(saleID) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE refund_payment (
                                refund_paymentID INT AUTO_INCREMENT PRIMARY KEY,
                                public_id VARCHAR(20) UNIQUE NOT NULL,
                                refund_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
                                payment_method ENUM('Cash', 'GCash', 'Card', 'Store Credit') NOT NULL,
                                payment_status ENUM('Pending', 'Failed', 'Refunded') NOT NULL DEFAULT 'Refunded',
                                returnID INT NOT NULL UNIQUE,
                                deleted_at DATETIME NULL DEFAULT NULL,
                                INDEX idx_refund_return (returnID),
                                INDEX idx_refund_deleted_at (deleted_at),
                                FOREIGN KEY (returnID) REFERENCES return_transaction(returnID) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE return_item (
                             return_itemID INT AUTO_INCREMENT PRIMARY KEY,
                             return_quantity INT UNSIGNED NOT NULL CHECK (return_quantity > 0),
                             reason ENUM ('Defective', 'Change of Mind') NOT NULL,
                             return_status ENUM ('Refunded', 'Replaced', 'Store Credit', 'Pending') NOT NULL DEFAULT 'Pending',
                             notes TEXT,
                             sale_itemID INT NOT NULL,
                             returnID INT NOT NULL,
                             deleted_at DATETIME NULL DEFAULT NULL,
                             CONSTRAINT chk_return_item_notes_nonempty CHECK (notes IS NULL OR TRIM(notes) <> ''),
                             INDEX idx_return_item_sale_item (sale_itemID),
                             INDEX idx_return_item_return (returnID),
                             INDEX idx_return_item_deleted_at (deleted_at),
                             FOREIGN KEY (sale_itemID) REFERENCES sale_item(sale_itemID) ON UPDATE CASCADE ON DELETE RESTRICT,
                             FOREIGN KEY (returnID) REFERENCES return_transaction(returnID) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE damaged_goods (
                               damaged_recordID INT AUTO_INCREMENT PRIMARY KEY,
                               damaged_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               damaged_quantity INT UNSIGNED NOT NULL CHECK (damaged_quantity > 0),
                               damaged_source ENUM ('Return', 'Storage', 'Transport') NOT NULL,
                               notes TEXT,
                               productID INT NOT NULL,
                               employeeID INT NOT NULL,
                               return_itemID INT,
                               deleted_at DATETIME NULL DEFAULT NULL,
                               CONSTRAINT chk_damaged_notes_nonempty CHECK (notes IS NULL OR TRIM(notes) <> ''),
                               INDEX idx_damaged_product (productID),
                               INDEX idx_damaged_employee (employeeID),
                               INDEX idx_damaged_return_item (return_itemID),
                               INDEX idx_damaged_deleted_at (deleted_at),
                               FOREIGN KEY (productID) REFERENCES product (productID) ON UPDATE CASCADE ON DELETE RESTRICT,
                               FOREIGN KEY (employeeID) REFERENCES employee (employeeID) ON UPDATE CASCADE ON DELETE RESTRICT,
                               FOREIGN KEY (return_itemID) REFERENCES return_item(return_itemID) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

SET SQL_SAFE_UPDATES = 0;

INSERT INTO techzone_new_inventory.customer (
    public_id, first_name, last_name, email_address, contact_number, barangay, city_municipality)
SELECT
    CONCAT('CS-', UPPER(LEFT(UUID(), 8))),
    SUBSTRING_INDEX(`Customer Name`, ' ', 1),
    TRIM(SUBSTR(`Customer Name`, LOCATE(' ', `Customer Name`))),
    CASE
        WHEN TRIM(COALESCE(`Email Address`, '')) = '' THEN NULL
        WHEN LOWER(TRIM(`Email Address`)) REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$' THEN LOWER(TRIM(`Email Address`))
        ELSE NULL
    END,
    CASE
        WHEN REGEXP_REPLACE(TRIM(COALESCE(`Phone Number`, '')), '[^0-9]', '') REGEXP '^0[0-9]{10}$'
            THEN CONCAT('63', SUBSTRING(REGEXP_REPLACE(TRIM(COALESCE(`Phone Number`, '')), '[^0-9]', ''), 2))
        WHEN REGEXP_REPLACE(TRIM(COALESCE(`Phone Number`, '')), '[^0-9]', '') REGEXP '^63[0-9]{10}$'
            THEN REGEXP_REPLACE(TRIM(COALESCE(`Phone Number`, '')), '[^0-9]', '')
        ELSE NULL
    END,
    "UNKNOWN",
    CASE
        WHEN TRIM(COALESCE(`Address`, '')) = '' THEN 'UNKNOWN'
        ELSE TRIM(`Address`)
    END
FROM techzone_old_inventory.customers;


UPDATE techzone_new_inventory.customer
SET province = city_municipality, city_municipality = 'UNKNOWN'
WHERE city_municipality IN ('Ilocos', 'Cavite', 'Batangas', 'Bulacan', 'Cebu', 'Albay', 'Nueva Ecija', 'Laguna');

UPDATE techzone_new_inventory.customer
SET city_municipality = 'Manila City'
WHERE city_municipality IN ('Tondo', 'Manila');

INSERT INTO techzone_new_inventory.product (public_id, product_name, quantity, selling_price)
SELECT
    CONCAT('PR-', UPPER(LEFT(UUID(), 8))),
    sales.Item_Sold,
    IFNULL(stock.total_stock, 0),
    0
FROM (SELECT Item_Sold FROM techzone_old_inventory.sales_log GROUP BY Item_Sold) AS sales
         LEFT JOIN (
    SELECT Item, SUM(`Current Stock`) AS total_stock FROM techzone_old_inventory.return_and_stock_log GROUP BY Item
) AS stock ON sales.Item_Sold = stock.Item;

INSERT INTO techzone_new_inventory.supplier
(
    public_id,
    contact_first_name,
    contact_last_name,
    supplier_name,
    contact_number,
    email_address,
    barangay,
    city_municipality
)
SELECT
    CONCAT('SP-', UPPER(LEFT(UUID(), 8))) AS public_id,
    'N/A' AS contact_first_name,
    'N/A' AS contact_last_name,
    dedup.best_supplier_name AS supplier_name,
    dedup.contact_number,
    dedup.email_address,
    'N/A' AS barangay,
    'N/A' AS city_municipality
FROM (
         SELECT
             MAX(normalized.contact_number) AS contact_number,
             MAX(normalized.email_address) AS email_address,
             SUBSTRING_INDEX(
                     GROUP_CONCAT(
                             normalized.supplier_name
                                 ORDER BY CHAR_LENGTH(normalized.supplier_name) DESC, normalized.supplier_name ASC
                SEPARATOR '||'
                     ),
                     '||',
                     1
             ) AS best_supplier_name
          FROM (
                   SELECT
                      CASE
                          WHEN normalized_base.local_contact REGEXP '^02[0-9]{8}$'
                              THEN normalized_base.local_contact
                          WHEN normalized_base.local_contact REGEXP '^09[0-9]{9}$'
                              THEN normalized_base.local_contact
                          ELSE NULL
                          END AS contact_number,
                      CASE
                          WHEN normalized_base.local_contact REGEXP '^(02[0-9]{8}|09[0-9]{9})$' THEN NULL
                          ELSE CONCAT(LOWER(REPLACE(TRIM(normalized_base.supplier_name_raw), ' ', '_')), '@techzone.local')
                          END AS email_address,
                      CASE
                          WHEN UPPER(TRIM(normalized_base.supplier_name_raw)) LIKE 'ASUS%' THEN 'ASUS PHILIPPINES'
                          WHEN UPPER(TRIM(normalized_base.supplier_name_raw)) LIKE 'AMD%'  THEN 'AMD PHILIPPINES'
                          ELSE UPPER(TRIM(normalized_base.supplier_name_raw))
                          END AS supplier_name
                   FROM (
                        SELECT
                            base.supplier_name_raw,
                            CASE
                                WHEN base.normalized_contact REGEXP '^63[0-9]{10}$'
                                    THEN CONCAT('0', SUBSTRING(base.normalized_contact, 3))
                                ELSE base.normalized_contact
                                END AS local_contact
                        FROM (
                            SELECT
                                sales.Supplier_Name AS supplier_name_raw,
                                REGEXP_REPLACE(TRIM(sales.Supplier_Contact), '[^0-9]', '') AS normalized_contact
                            FROM techzone_old_inventory.sales_log AS sales
                            WHERE TRIM(COALESCE(sales.Supplier_Name, '')) <> ''
                        ) AS base
                   ) AS normalized_base
              ) AS normalized
         WHERE normalized.contact_number IS NOT NULL OR normalized.email_address IS NOT NULL
         GROUP BY COALESCE(normalized.contact_number, normalized.email_address)
     ) AS dedup
         LEFT JOIN techzone_new_inventory.supplier AS existing
                   ON (dedup.contact_number IS NOT NULL AND existing.contact_number = dedup.contact_number)
                      OR (dedup.contact_number IS NULL AND existing.email_address = dedup.email_address)
WHERE existing.supplierID IS NULL;

INSERT INTO techzone_new_inventory.product_supplier
(supplierID, productID, supplier_product_name, wholesale_cost)
SELECT
    s.supplierID,
    p.productID,
    sales.Item_Sold,
    MAX(CAST(REPLACE(sales.wholesale_cost, ',', '') AS DECIMAL(10,2)))
FROM techzone_old_inventory.sales_log AS sales
         JOIN techzone_new_inventory.supplier AS s
              ON s.supplier_name =
                 CASE
                     WHEN sales.Supplier_Name LIKE 'ASUS%' THEN 'ASUS PHILIPPINES'
                     WHEN sales.Supplier_Name LIKE 'AMD%'  THEN 'AMD PHILIPPINES'
                     ELSE UPPER(TRIM(sales.Supplier_Name))
                     END
         JOIN techzone_new_inventory.product AS p
              ON p.product_name = sales.Item_Sold
GROUP BY s.supplierID, p.productID;

INSERT INTO techzone_new_inventory.employee (public_id, first_name, last_name, employee_role, email_address, password_hash)
VALUES
    (CONCAT('EM-', UPPER(LEFT(UUID(), 8))), 'Old', 'Logs', 'System', 'system@techzone.com', '$2y$10$ifTBGSF.MrWvXjJpo1FWqeZ/10LNc02eb/okGGf6.qWliePyj.PGq'),
    (CONCAT('EM-', UPPER(LEFT(UUID(), 8))), 'Ricardo', 'Dalisay', 'Store Manager', 'ricardo.d@techzone.com', '$2y$10$ifTBGSF.MrWvXjJpo1FWqeZ/10LNc02eb/okGGf6.qWliePyj.PGq'),
    (CONCAT('EM-', UPPER(LEFT(UUID(), 8))), 'Cardo', 'Dalisay', 'Senior Sales', 'cardo.d@techzone.com', '$2y$10$ifTBGSF.MrWvXjJpo1FWqeZ/10LNc02eb/okGGf6.qWliePyj.PGq'),
    (CONCAT('EM-', UPPER(LEFT(UUID(), 8))), 'Alyana', 'Arevalo', 'Lead Technician', 'alyana.a@techzone.com', '$2y$10$ifTBGSF.MrWvXjJpo1FWqeZ/10LNc02eb/okGGf6.qWliePyj.PGq'),
    (CONCAT('EM-', UPPER(LEFT(UUID(), 8))), 'Bubbles', 'Paraiso', 'Inventory Clerk', 'bubbles.p@techzone.com', '$2y$10$ifTBGSF.MrWvXjJpo1FWqeZ/10LNc02eb/okGGf6.qWliePyj.PGq'),
    (CONCAT('EM-', UPPER(LEFT(UUID(), 8))), 'Delfin', 'Borja', 'Cashier', 'delfin.b@techzone.com', '$2y$10$ifTBGSF.MrWvXjJpo1FWqeZ/10LNc02eb/okGGf6.qWliePyj.PGq');

INSERT INTO techzone_new_inventory.employee
(public_id, first_name, last_name, employee_role, employee_status, email_address, password_hash, contact_number)
SELECT CONCAT('EM-', UPPER(LEFT(UUID(), 8))), 'TechZone', 'Administrator', 'Admin', 'Active', 'admin@techzone.com',
       '$2y$10$N8cAwDFj5ZUnBbQvDEQ7YOkF7lCJiOWPTA5./4V8Ncl.FlIpSO8SO', NULL
    WHERE NOT EXISTS (
    SELECT 1 FROM techzone_new_inventory.employee WHERE email_address = 'admin@techzone.com'
);

INSERT INTO techzone_new_inventory.employee
(public_id, first_name, last_name, employee_role, employee_status, email_address, password_hash, contact_number)
SELECT CONCAT('EM-', UPPER(LEFT(UUID(), 8))), 'Operations', 'Admin', 'Admin', 'Active', 'ops.admin@techzone.com',
       '$2y$10$89fI5hvwdmCOCefZu/sWcuK/QCpjVDxmL1cOq49UqsprF5jA9vMce', NULL
    WHERE NOT EXISTS (
    SELECT 1 FROM techzone_new_inventory.employee WHERE email_address = 'ops.admin@techzone.com'
);

INSERT INTO techzone_new_inventory.employee
(public_id, first_name, last_name, employee_role, employee_status, email_address, password_hash, contact_number)
SELECT CONCAT('EM-', UPPER(LEFT(UUID(), 8))), 'Inventory', 'Admin', 'Admin', 'Active', 'inventory.admin@techzone.com',
       '$2y$10$zbim6TuSjuYNVx9UQsoY1O/22cmvrBI9kkqfOJmHBDLVz229j59bK', NULL
    WHERE NOT EXISTS (
    SELECT 1 FROM techzone_new_inventory.employee WHERE email_address = 'inventory.admin@techzone.com'
);

INSERT INTO techzone_new_inventory.sale (
    public_id,
    sale_date,
    total_amount,
    customerID,
    employeeID,
    fulfillment_method,
    sale_status,
    created_at,
    updated_at
)
SELECT
    CONCAT('SL-', UPPER(LEFT(UUID(), 8))),
    STR_TO_DATE(Date, '%m/%d/%Y'),
    SUM(CAST(REPLACE(Sold_Price, ',', '') AS DECIMAL(10,2)) * sales.Qty),
    c.customerID,
    1,
    'Walk-in',
    'Completed',
    STR_TO_DATE(Date, '%m/%d/%Y'),
    STR_TO_DATE(Date, '%m/%d/%Y')
FROM techzone_old_inventory.sales_log AS sales
         JOIN techzone_new_inventory.customer AS c ON sales.Customer = CONCAT(c.first_name, ' ', c.last_name)
GROUP BY sales.Date, c.customerID;

INSERT INTO techzone_new_inventory.payment
(payment_date, amount, payment_method, payment_status, public_id, saleID)
SELECT
    s.sale_date,
    s.total_amount,
    'Cash',
    'Completed',
    CONCAT('PY-', UPPER(LEFT(REPLACE(UUID(), '-', ''), 10))),
    s.saleID
FROM techzone_new_inventory.sale AS s
WHERE s.total_amount > 0
  AND NOT EXISTS (
    SELECT 1
    FROM techzone_new_inventory.payment AS p
    WHERE p.saleID = s.saleID
);

INSERT INTO techzone_new_inventory.sale_item (quantity_sold, price_at_sale, saleID, productID)
SELECT sales.Qty, CAST(REPLACE(sales.Sold_Price, ',', '') AS DECIMAL(10,2)), s.saleID, p.productID
FROM techzone_old_inventory.sales_log AS sales
         JOIN techzone_new_inventory.product AS p ON p.product_name = sales.Item_Sold
         JOIN techzone_new_inventory.customer AS c ON sales.Customer = CONCAT(c.first_name, ' ', c.last_name)
         JOIN techzone_new_inventory.sale AS s ON s.customerID = c.customerID AND s.sale_date = STR_TO_DATE(sales.Date, '%m/%d/%Y');

DELIMITER //
CREATE TRIGGER damaged_insert
    AFTER INSERT ON return_item
    FOR EACH ROW
BEGIN
    IF NEW.reason = 'Defective' THEN

        INSERT INTO damaged_goods (
            damaged_date,
            damaged_quantity,
            damaged_source,
            productID,
            return_itemID,
            employeeID
        )
    SELECT
        rturn.date_created,
        NEW.return_quantity,
        'Return',
        sale_item.productID,
        NEW.return_itemID,
        rturn.employeeID
    FROM return_transaction AS rturn
             JOIN sale_item
                  ON sale_item.sale_itemID = NEW.sale_itemID
    WHERE rturn.returnID = NEW.returnID;
END IF;
END; //
DELIMITER ;

INSERT INTO techzone_new_inventory.return_transaction (
    public_id,
    date_created,
    refund_amount,
    employeeID,
    return_progress,
    return_method,
    saleID
)
SELECT
    CONCAT('RT-', UPPER(LEFT(UUID(), 8))),
    STR_TO_DATE(returntrans.Date, '%m/%d/%Y'),
    NULL,
    1,
    'Finalized',
    'Drop-off',
    (
        SELECT s.saleID
        FROM techzone_new_inventory.sale s
                 JOIN techzone_new_inventory.sale_item si ON s.saleID = si.saleID
                 JOIN techzone_new_inventory.product p ON si.productID = p.productID
                 JOIN techzone_new_inventory.customer c ON s.customerID = c.customerID
        WHERE CONCAT(c.first_name, ' ', c.last_name) = returntrans.Customer
          AND p.product_name = returntrans.Item
          AND s.sale_date <= STR_TO_DATE(returntrans.Date, '%m/%d/%Y')
        ORDER BY s.sale_date DESC
        LIMIT 1
    )
FROM techzone_old_inventory.return_and_stock_log AS returntrans
WHERE returntrans.Customer IS NOT NULL
  AND returntrans.Item IS NOT NULL;

INSERT INTO techzone_new_inventory.return_item (
    return_quantity,
    reason,
    return_status,
    sale_itemID,
    returnID
)
SELECT
    rturn.Qty,
    CASE
        WHEN TRIM(rturn.Reason) LIKE '%Mind%' THEN 'Change of Mind'
        ELSE 'Defective'
        END AS reason,
    CASE
        WHEN TRIM(rturn.Status) = 'Replaced' THEN 'Replaced'
        WHEN TRIM(rturn.Status) = 'Refunded' THEN 'Refunded'
        ELSE 'Store Credit'
        END AS return_status,
    si.sale_itemID,
    rt.returnID
FROM techzone_old_inventory.return_and_stock_log AS rturn
         JOIN techzone_new_inventory.product AS p
              ON p.product_name = TRIM(rturn.Item)
         JOIN techzone_new_inventory.customer AS c
              ON CONCAT(c.first_name, ' ', c.last_name) = TRIM(rturn.Customer)
         JOIN techzone_new_inventory.sale AS s
              ON s.customerID = c.customerID
         JOIN techzone_new_inventory.sale_item AS si
              ON s.saleID = si.saleID AND si.productID = p.productID
         JOIN techzone_new_inventory.return_transaction AS rt
              ON rt.saleID = s.saleID AND DATE(rt.date_created) = STR_TO_DATE(rturn.Date, '%m/%d/%Y')
WHERE DATEDIFF(STR_TO_DATE(rturn.Date, '%m/%d/%Y'), DATE(s.sale_date)) BETWEEN 0 AND 7;

DELIMITER //
CREATE TRIGGER log_inventory_return
    AFTER INSERT ON return_item
    FOR EACH ROW
BEGIN

    IF NEW.reason = 'Change of Mind' THEN
      INSERT INTO inventory_transaction (transaction_date, quantity_change, transaction_type, referenceID, productID, employeeID)
    SELECT NOW(), NEW.return_quantity, 'Return', NEW.returnID, si.productID, rt.employeeID
    FROM sale_item AS si
             JOIN return_transaction AS rt
                  ON rt.returnID = NEW.returnID
    WHERE si.sale_itemID = NEW.sale_itemID;
END IF;

IF NEW.return_status = 'Replaced' THEN
      INSERT INTO inventory_transaction (transaction_date, quantity_change, transaction_type, referenceID, productID, employeeID)
SELECT NOW(), -NEW.return_quantity, 'Replacement', NEW.returnID, si.productID, rt.employeeID
FROM sale_item AS si
         JOIN return_transaction AS rt
              ON rt.returnID = NEW.returnID
WHERE si.sale_itemID = NEW.sale_itemID;
END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER validate_return_quantity
    BEFORE INSERT ON return_item
    FOR EACH ROW
BEGIN
    DECLARE v_original_qty INT;

    SELECT quantity_sold INTO v_original_qty
    FROM sale_item
    WHERE sale_itemID = NEW.sale_itemID;

    IF NEW.return_quantity > v_original_qty THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Alert: Cannot return more items than were originally sold.';
END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER product_stock_update
    AFTER INSERT ON inventory_transaction
    FOR EACH ROW
BEGIN
    UPDATE product
    SET quantity = quantity + NEW.quantity_change
    WHERE productID = NEW.productID;
END //
DELIMITER ;

DELIMITER //

CREATE TRIGGER sync_product_supplier_status
    AFTER UPDATE ON supplier
    FOR EACH ROW
BEGIN
    IF OLD.is_active <> NEW.is_active THEN
    UPDATE product_supplier
    SET is_active = NEW.is_active
    WHERE supplierID = NEW.supplierID;
END IF;
END //

DELIMITER ;

DELIMITER //
CREATE TRIGGER prevent_duplicate_supplier
    BEFORE INSERT ON supplier
    FOR EACH ROW
BEGIN
    DECLARE word_new VARCHAR(100);
    DECLARE done INT DEFAULT 0;
    DECLARE cur_word CURSOR FOR
    SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(REGEXP_REPLACE(NEW.supplier_name, '[^a-zA-Z0-9 ]', ' '), ' ', n.n), ' ', -1)) AS word
    FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
          UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8) n
    WHERE n.n <= 1 + (LENGTH(NEW.supplier_name) - LENGTH(REPLACE(NEW.supplier_name, ' ', '')));

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur_word;

    read_loop: LOOP
        FETCH cur_word INTO word_new;
        IF done THEN
            LEAVE read_loop;
END IF;
IF LENGTH(word_new) > 2 THEN
            IF EXISTS (
                SELECT 1
                FROM supplier s
                INNER JOIN (
                    SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
                    UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
                ) nums ON nums.n <= 1 + (LENGTH(s.supplier_name) - LENGTH(REPLACE(s.supplier_name, ' ', '')))
                WHERE
                    SOUNDEX(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(REGEXP_REPLACE(s.supplier_name,'[^a-zA-Z0-9 ]',' '), ' ', nums.n), ' ', -1))) = SOUNDEX(word_new)
                    AND TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(REGEXP_REPLACE(s.supplier_name,'[^a-zA-Z0-9 ]',' '), ' ', nums.n), ' ', -1)) != ''
            ) THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Similar supplier name detected via Soundex. Please verify if the supplier already exists.';
END IF;
END IF;
END LOOP;
CLOSE cur_word;
END //
DELIMITER ;


DELIMITER //
CREATE TRIGGER validate_stock_before_replacement
    BEFORE INSERT ON return_item
    FOR EACH ROW
BEGIN

    IF NEW.return_status = 'Replaced' AND NEW.return_quantity > (
      SELECT p.quantity
      FROM product AS p
      JOIN sale_item AS si
      ON p.productID = si.productID
      WHERE si.sale_itemID = NEW.sale_itemID) THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Error: Insufficient stock for this replacement.';
END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER update_stock_after_sale
    AFTER INSERT ON sale_item
    FOR EACH ROW
BEGIN

    INSERT INTO inventory_transaction (transaction_date, quantity_change, transaction_type, referenceID, productID, employeeID)
    SELECT NOW(), -NEW.quantity_sold, 'Sale', NEW.saleID, NEW.productID, s.employeeID
    FROM sale AS s
    WHERE s.saleID = NEW.saleID;

END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER validate_stock_before_insert
    BEFORE INSERT ON sale_item
    FOR EACH ROW
BEGIN
    IF NEW.quantity_sold > (SELECT quantity FROM product WHERE productID = NEW.productID) THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Error: Insufficient stock for this sale.';
END IF;
END //
DELIMITER ;

DELIMITER //

CREATE PROCEDURE update_return_item_status(
    IN p_return_itemID INT,
    IN p_new_status ENUM('Refunded', 'Replaced', 'Store Credit', 'Pending'),
    IN p_employee_public_id VARCHAR(20)
        )
BEGIN
    DECLARE v_returnID INT;
    DECLARE v_current_progress VARCHAR(20);
    DECLARE v_employeeID INT;
SELECT rt.returnID, rt.return_progress INTO v_returnID, v_current_progress
FROM return_transaction rt
         JOIN return_item ri ON rt.returnID = ri.returnID
WHERE ri.return_itemID = p_return_itemID
    LIMIT 1;

SELECT employeeID INTO v_employeeID
FROM employee
WHERE public_id = p_employee_public_id
    LIMIT 1;

IF v_returnID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Return item record not found.';

    ELSEIF v_employeeID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Authorized employee record not found.';

    ELSEIF v_current_progress IN ('Finalized', 'Rejected') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Cannot update item status. The parent return is already closed.';

ELSE
UPDATE return_item
SET return_status = p_new_status
WHERE return_itemID = p_return_itemID;
END IF;
END //

DELIMITER ;

DELIMITER //
CREATE PROCEDURE update_product_price(
    IN p_product_public_id VARCHAR(20),
    IN p_new_price DECIMAL(10,2)
)
BEGIN
    DECLARE v_productID INT;
    DECLARE v_max_wholesale DECIMAL(10,2);

SELECT productID INTO v_productID FROM product WHERE public_id = p_product_public_id LIMIT 1;

IF v_productID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Product not found.';
END IF;

SELECT MAX(wholesale_cost) INTO v_max_wholesale
FROM product_supplier
WHERE productID = v_productID AND is_active = 1;

IF v_max_wholesale IS NOT NULL AND p_new_price < v_max_wholesale THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Security Alert: New price cannot be lower than the wholesale cost.';
ELSE
UPDATE product SET selling_price = p_new_price WHERE productID = v_productID;
END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE update_return_progress(
    IN p_return_public_id VARCHAR(20),
    IN p_new_progress ENUM('Requested', 'In Process', 'Approved', 'Rejected', 'Finalized'),
    IN p_employee_public_id VARCHAR(20)
        )
BEGIN
    DECLARE v_returnID INT;
    DECLARE v_current_progress VARCHAR(20);
    DECLARE v_employeeID INT;

SELECT returnID, return_progress INTO v_returnID, v_current_progress FROM return_transaction WHERE public_id = p_return_public_id LIMIT 1;
SELECT employeeID INTO v_employeeID FROM employee WHERE public_id = p_employee_public_id LIMIT 1;

IF v_returnID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Return reference not found.';
    ELSEIF v_current_progress = 'Finalized' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: This return has already been finalized and locked.';
ELSE
UPDATE return_transaction
SET return_progress = p_new_progress, employeeID = v_employeeID
WHERE returnID = v_returnID;
END IF;
END //


DELIMITER ;
DELIMITER //
CREATE PROCEDURE restock_product_secure(
    IN p_product_public_id VARCHAR(20),
    IN p_quantity INT,
    IN p_employee_public_id VARCHAR(20),
    IN p_supplier_public_id VARCHAR(20)
)
BEGIN
    DECLARE v_productID INT;
    DECLARE v_employeeID INT;
    DECLARE v_supplierID INT;

SELECT productID INTO v_productID FROM product WHERE public_id = p_product_public_id LIMIT 1;
SELECT employeeID INTO v_employeeID FROM employee WHERE public_id = p_employee_public_id LIMIT 1;
SELECT supplierID INTO v_supplierID FROM supplier WHERE public_id = p_supplier_public_id LIMIT 1;

IF v_productID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Product reference not found.';
    ELSEIF v_employeeID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Employee reference not found.';
    ELSEIF v_supplierID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Supplier reference not found.';
    ELSEIF p_quantity <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Restock quantity must be greater than zero.';
    ELSEIF NOT EXISTS (
        SELECT 1
        FROM product_supplier ps
        JOIN supplier AS s ON ps.supplierID = s.supplierID
        WHERE ps.productID = v_productID
          AND ps.supplierID = v_supplierID
          AND s.is_active = TRUE
          AND ps.is_active = TRUE
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Restock Denied: Supplier is either deactivated or not linked to this product.';

ELSE
        INSERT INTO inventory_transaction (
            transaction_date,
            quantity_change,
            transaction_type,
            productID,
            employeeID
        )
        VALUES (NOW(), p_quantity, 'Restock', v_productID, v_employeeID);
END IF;
END //

DELIMITER ;

DELIMITER //
CREATE PROCEDURE customer_create(
    IN p_public_id VARCHAR(20),
    IN p_first_name VARCHAR(50),
    IN p_middle_name VARCHAR(50),
    IN p_last_name VARCHAR(50),
    IN p_customer_type VARCHAR(20),
    IN p_password_hash VARCHAR(255),
    IN p_merge_otp_verified TINYINT(1),
    IN p_email_address VARCHAR(100),
    IN p_contact_number VARCHAR(12),
    IN p_street_address VARCHAR(45),
    IN p_barangay VARCHAR(45),
    IN p_province VARCHAR(45),
    IN p_city_municipality VARCHAR(45),
    IN p_zip_code VARCHAR(10),
    IN p_status VARCHAR(30)
)
BEGIN
    DECLARE v_customer_type VARCHAR(20);
    DECLARE v_status VARCHAR(30);
    DECLARE v_email VARCHAR(100);
    DECLARE v_contact VARCHAR(12);
    DECLARE v_password_hash VARCHAR(255);
    DECLARE v_merge_otp_verified TINYINT(1) DEFAULT 0;
    DECLARE v_middle_name VARCHAR(50);
    DECLARE v_zip VARCHAR(10);
    DECLARE v_street VARCHAR(45);
    DECLARE v_province VARCHAR(45);
    DECLARE v_email_customer_id INT DEFAULT NULL;
    DECLARE v_contact_customer_id INT DEFAULT NULL;
    DECLARE v_existing_customer_id INT DEFAULT NULL;
    DECLARE v_existing_password_hash VARCHAR(255) DEFAULT NULL;
    DECLARE v_existing_customer_type VARCHAR(20) DEFAULT NULL;

    SET v_customer_type = COALESCE(NULLIF(TRIM(p_customer_type), ''), 'Walk-in');
    SET v_status = COALESCE(NULLIF(TRIM(p_status), ''), 'Active');
    SET v_email = NULLIF(LOWER(TRIM(COALESCE(p_email_address, ''))), '');
    SET v_contact = NULLIF(TRIM(COALESCE(p_contact_number, '')), '');
    SET v_password_hash = NULLIF(TRIM(COALESCE(p_password_hash, '')), '');
    SET v_merge_otp_verified = IFNULL(p_merge_otp_verified, 0);
    SET v_middle_name = NULLIF(TRIM(COALESCE(p_middle_name, '')), '');
    SET v_zip = NULLIF(TRIM(COALESCE(p_zip_code, '')), '');
    SET v_street = NULLIF(TRIM(COALESCE(p_street_address, '')), '');
    SET v_province = NULLIF(TRIM(COALESCE(p_province, '')), '');

    IF v_contact IS NOT NULL THEN
        SET v_contact = NULLIF(REGEXP_REPLACE(v_contact, '[^0-9]', ''), '');
        IF v_contact REGEXP '^0[0-9]{10}$' THEN
            SET v_contact = CONCAT('63', SUBSTRING(v_contact, 2));
        END IF;
    END IF;

    IF p_public_id IS NULL OR TRIM(p_public_id) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Customer public ID is required.';
    ELSEIF p_first_name IS NULL OR TRIM(p_first_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: First name is required.';
    ELSEIF p_last_name IS NULL OR TRIM(p_last_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Last name is required.';
    ELSEIF p_barangay IS NULL OR TRIM(p_barangay) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Barangay is required.';
    ELSEIF p_city_municipality IS NULL OR TRIM(p_city_municipality) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: City/Municipality is required.';
    ELSEIF v_customer_type NOT IN ('Walk-in', 'Registered') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid customer type.';
    ELSEIF v_status NOT IN ('Active', 'Merged', 'Deleted_by_User') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid customer status.';
    ELSEIF v_email IS NULL AND v_contact IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: You must provide either an email address or a contact number.';
    ELSEIF v_email IS NOT NULL AND v_email NOT REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Email address format is invalid.';
    ELSEIF v_contact IS NOT NULL AND v_contact NOT REGEXP '^63[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Contact number must be 12 digits and start with 63.';
    ELSEIF v_zip IS NOT NULL AND v_zip NOT REGEXP '^[0-9]{4}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Zip code must be exactly 4 digits.';
    ELSEIF v_customer_type = 'Registered' AND v_password_hash IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Password hash is required for registered customer accounts.';
ELSE
        IF v_email IS NOT NULL THEN
SELECT customerID INTO v_email_customer_id
FROM customer
WHERE email_address = v_email
  AND deleted_at IS NULL
ORDER BY customerID ASC
    LIMIT 1;
END IF;

        IF v_contact IS NOT NULL THEN
SELECT customerID INTO v_contact_customer_id
FROM customer
WHERE contact_number = v_contact
  AND deleted_at IS NULL
ORDER BY customerID ASC
    LIMIT 1;
END IF;

        IF v_email_customer_id IS NOT NULL AND v_contact_customer_id IS NOT NULL AND v_email_customer_id <> v_contact_customer_id THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Email and contact number belong to different customer records.';
END IF;

        SET v_existing_customer_id = COALESCE(v_email_customer_id, v_contact_customer_id);

        IF v_existing_customer_id IS NOT NULL THEN
SELECT customer_type, NULLIF(TRIM(password_hash), '') INTO v_existing_customer_type, v_existing_password_hash
FROM customer
WHERE customerID = v_existing_customer_id
    LIMIT 1;

IF v_existing_customer_type = 'Registered' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: This customer account is already registered.';
ELSEIF v_existing_password_hash IS NOT NULL THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: This customer account already has login credentials.';
ELSE
IF v_password_hash IS NOT NULL AND v_merge_otp_verified = 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: OTP verification is required to merge this walk-in account.';
END IF;
UPDATE customer
SET first_name = COALESCE(NULLIF(TRIM(p_first_name), ''), first_name),
    middle_name = COALESCE(v_middle_name, middle_name),
    last_name = COALESCE(NULLIF(TRIM(p_last_name), ''), last_name),
    customer_type = CASE
                        WHEN v_password_hash IS NOT NULL THEN 'Registered'
                        ELSE customer_type
        END,
    password_hash = COALESCE(v_password_hash, password_hash),
    status = 'Active',
    deleted_at = NULL,
    email_address = COALESCE(v_email, email_address),
    contact_number = COALESCE(v_contact, contact_number),
    street_address = COALESCE(v_street, street_address),
    barangay = COALESCE(NULLIF(TRIM(p_barangay), ''), barangay),
    province = COALESCE(v_province, province),
    city_municipality = COALESCE(NULLIF(TRIM(p_city_municipality), ''), city_municipality),
    zip_code = COALESCE(v_zip, zip_code)
WHERE customerID = v_existing_customer_id;
END IF;
ELSE
            INSERT INTO customer (
                public_id, first_name, middle_name, last_name, customer_type, password_hash, status, deleted_at,
                email_address, contact_number, street_address, barangay, province, city_municipality, zip_code
            ) VALUES (
                TRIM(p_public_id), TRIM(p_first_name), v_middle_name, TRIM(p_last_name), v_customer_type, v_password_hash, v_status, NULL,
                v_email, v_contact, v_street, TRIM(p_barangay), v_province, TRIM(p_city_municipality), v_zip
            );
END IF;
END IF;
END //

CREATE PROCEDURE employee_add(
    IN p_public_id VARCHAR(20),
    IN p_first_name VARCHAR(50),
    IN p_last_name VARCHAR(50),
    IN p_employee_role VARCHAR(50),
    IN p_employee_status VARCHAR(20),
    IN p_email_address VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_contact_number VARCHAR(15)
)
BEGIN
    DECLARE v_role VARCHAR(50);
    DECLARE v_status VARCHAR(20);
    DECLARE v_email VARCHAR(100);
    DECLARE v_password_hash VARCHAR(255);
    DECLARE v_contact VARCHAR(15);

    SET v_role = COALESCE(NULLIF(TRIM(p_employee_role), ''), 'Staff');
    SET v_status = COALESCE(NULLIF(TRIM(p_employee_status), ''), 'Active');
    SET v_email = NULLIF(LOWER(TRIM(COALESCE(p_email_address, ''))), '');
    SET v_password_hash = NULLIF(TRIM(COALESCE(p_password_hash, '')), '');
    SET v_contact = NULLIF(TRIM(COALESCE(p_contact_number, '')), '');

    IF v_contact IS NOT NULL THEN
        SET v_contact = NULLIF(REGEXP_REPLACE(v_contact, '[^0-9]', ''), '');
        IF v_contact REGEXP '^0[0-9]{10}$' THEN
            SET v_contact = CONCAT('63', SUBSTRING(v_contact, 2));
        END IF;
    END IF;

    IF p_public_id IS NULL OR TRIM(p_public_id) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Employee public ID is required.';
    ELSEIF p_first_name IS NULL OR TRIM(p_first_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Employee first name is required.';
    ELSEIF p_last_name IS NULL OR TRIM(p_last_name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Employee last name is required.';
    ELSEIF v_role = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Employee role is required.';
    ELSEIF v_status NOT IN ('Active', 'Inactive') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid employee status.';
    ELSEIF v_email IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Employee email address is required.';
    ELSEIF v_email NOT REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid email format.';
    ELSEIF v_contact IS NOT NULL AND v_contact NOT REGEXP '^63[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Contact number must be 12 digits and start with 63.';
    ELSEIF UPPER(v_role) = 'ADMIN' AND v_password_hash IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Password hash is required for admin registration.';
    ELSEIF EXISTS (SELECT 1 FROM employee WHERE email_address = v_email) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: This email address is already registered to an employee.';
ELSE
        INSERT INTO employee (
            public_id, first_name, last_name, employee_role, employee_status,
            email_address, password_hash, contact_number
        ) VALUES (
            TRIM(p_public_id), TRIM(p_first_name), TRIM(p_last_name), v_role, v_status,
            v_email, v_password_hash, v_contact
        );
END IF;
END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE record_new_product(
    IN p_public_id VARCHAR(20),
    IN p_name VARCHAR(100),
    IN p_qty INT,
    IN p_price DECIMAL(10,2))
BEGIN
   IF p_name IS NULL OR TRIM(p_name) = '' THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Product name is required.';
   ELSEIF p_qty IS NULL OR p_qty < 0 THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Quantity is required and cannot be negative.';
   ELSEIF p_price IS NULL OR p_price < 0 THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Price is required and cannot be negative.';
   ELSEIF EXISTS (SELECT product_name FROM product WHERE UPPER(TRIM(product_name)) = UPPER(TRIM(p_name))) THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: This product name already exists in the catalog.';
ELSE
      INSERT INTO product (public_id, product_name, quantity, selling_price)
      VALUES (p_public_id, p_name, p_qty, p_price);
END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE record_new_supplier(
    IN p_public_id VARCHAR(20),
    IN p_name VARCHAR(100),
    IN p_first_name VARCHAR(50),
    IN p_last_name VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_phone VARCHAR(12),
    IN p_barangay VARCHAR(45),
    IN p_city VARCHAR(45))
BEGIN
   DECLARE v_email VARCHAR(100);
   DECLARE v_contact VARCHAR(12);
   DECLARE v_digits VARCHAR(20);

   SET v_email = NULLIF(LOWER(TRIM(COALESCE(p_email, ''))), '');
   SET v_contact = NULLIF(TRIM(COALESCE(p_phone, '')), '');
   IF v_contact IS NOT NULL THEN
       IF UPPER(v_contact) IN ('N/A', 'NA') THEN
           SET v_contact = NULL;
       END IF;
   END IF;
   IF v_contact IS NOT NULL THEN
       SET v_digits = NULLIF(REGEXP_REPLACE(v_contact, '[^0-9]', ''), '');
       IF v_digits REGEXP '^63[0-9]{10}$' THEN
           SET v_digits = CONCAT('0', SUBSTRING(v_digits, 3));
       END IF;

       IF v_digits REGEXP '^02[0-9]{8}$' THEN
           SET v_contact = v_digits;
       ELSEIF v_digits REGEXP '^09[0-9]{9}$' THEN
           SET v_contact = v_digits;
       ELSE
           SET v_contact = NULLIF(TRIM(v_contact), '');
       END IF;
   END IF;

   IF p_name IS NULL OR TRIM(p_name) = '' THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Supplier company name is required.';
   ELSEIF p_first_name IS NULL OR TRIM(p_first_name) = '' THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Contact first name is required.';
   ELSEIF p_last_name IS NULL OR TRIM(p_last_name) = '' THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Contact last name is required.';
   ELSEIF p_barangay IS NULL OR TRIM(p_barangay) = '' THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Supplier barangay is required.';
   ELSEIF p_city IS NULL OR TRIM(p_city) = '' THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Supplier city/municipality is required.';
   ELSEIF v_email IS NULL AND v_contact IS NULL THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Supplier must provide email or contact number.';
   ELSEIF v_email IS NOT NULL AND v_email NOT REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$' THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid email format.';
   ELSEIF v_contact IS NOT NULL AND v_contact NOT REGEXP '^(02[0-9]{8}|09[0-9]{9})$' THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Contact number must be in 0288881111 or 09170001111 format.';
   ELSEIF v_email IS NOT NULL AND EXISTS (SELECT 1 FROM supplier WHERE email_address = v_email) THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: This supplier email is already in use.';
   ELSEIF v_contact IS NOT NULL AND EXISTS (SELECT 1 FROM supplier WHERE contact_number = v_contact) THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: This supplier contact number is already in use.';
ELSE
       INSERT INTO supplier (public_id, supplier_name, contact_first_name, contact_last_name, email_address, contact_number, barangay, city_municipality)
       VALUES (p_public_id, UPPER(p_name), p_first_name, p_last_name, v_email, v_contact, p_barangay, p_city);
END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE link_product_supplier(
    IN p_supplierID INT,
    IN p_productID INT,
    IN p_supp_prod_name VARCHAR(100),
    IN p_wholesale DECIMAL(10,2))
BEGIN

INSERT INTO product_supplier(
    supplierID,
    productID,
    supplier_product_name,
    wholesale_cost
) VALUES (
             p_supplierID,
             p_productID,
             p_supp_prod_name,
             p_wholesale
         );
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE get_product_details_by_product_name(
    IN p_product_name VARCHAR(100))

BEGIN

SELECT p.productID, p.product_name, p.selling_price, p.quantity AS stock_on_hand, p.is_active AS product_is_active, s.supplier_name, s.email_address, s.contact_number, ps.wholesale_cost
FROM product AS p
         LEFT JOIN product_supplier AS ps
                   ON p.productID = ps.productID
         LEFT JOIN supplier AS s
                   ON ps.supplierID = s.supplierID
WHERE REPLACE(p.product_name, ' ', '') LIKE CONCAT('%', REPLACE(p_product_name, ' ', ''), '%');

END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE update_product_status(
    IN p_product_public_id VARCHAR(20),
    IN p_is_active BOOLEAN
)
BEGIN
    DECLARE v_productID INT;
    DECLARE v_current_stock INT;

SELECT productID, quantity INTO v_productID, v_current_stock FROM product WHERE public_id = p_product_public_id LIMIT 1;

IF v_productID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Product not found.';
    ELSEIF p_is_active = FALSE AND v_current_stock > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Cannot deactivate a product that still has stock on hand.';
ELSE
UPDATE product SET is_active = p_is_active WHERE productID = v_productID;
END IF;
END //
DELIMITER ;




DELIMITER //
CREATE PROCEDURE update_payment_status(
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
END //
DELIMITER ;


DELIMITER //

CREATE PROCEDURE update_supplier_status(
    IN p_supplier_public_id VARCHAR(20),
    IN p_is_active BOOLEAN
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM supplier WHERE public_id = p_supplier_public_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Supplier reference not found.';
ELSE
UPDATE supplier
SET is_active = p_is_active
WHERE public_id = p_supplier_public_id;

END IF;

END //

DELIMITER ;

DELIMITER //
CREATE FUNCTION get_profit(
    p_wholesale_cost DECIMAL(10,2),
    p_price_at_sale DECIMAL(10,2),
    p_quantity_sold INT)

    RETURNS DECIMAL(10,2)
    DETERMINISTIC
BEGIN

   DECLARE calculated_profit DECIMAL(10,2);
   SET calculated_profit = (p_price_at_sale - p_wholesale_cost) * p_quantity_sold;
RETURN calculated_profit;

END //
DELIMITER ;


DELIMITER //

CREATE PROCEDURE record_return_item_secure(
    IN p_public_return_id VARCHAR(20),
    IN p_public_sale_id VARCHAR(20),
    IN p_public_product_id VARCHAR(20),
    IN p_qty INT,
    IN p_reason ENUM('Defective', 'Change of Mind'),
    IN p_status ENUM('Refunded', 'Replaced', 'Store Credit', 'Pending'),
    IN p_serialnum VARCHAR(30),
    IN p_notes TEXT
        )
BEGIN
    DECLARE v_returnID INT;
    DECLARE v_saleID INT;
    DECLARE v_productID INT;
    DECLARE v_sale_itemID INT;

SELECT returnID INTO v_returnID FROM return_transaction WHERE public_id = p_public_return_id LIMIT 1;

SELECT saleID INTO v_saleID FROM sale WHERE public_id = p_public_sale_id LIMIT 1;

SELECT productID INTO v_productID FROM product WHERE public_id = p_public_product_id LIMIT 1;
SELECT sale_itemID INTO v_sale_itemID
FROM sale_item
WHERE saleID = v_saleID
  AND productID = v_productID
  AND (serial_number = p_serialnum OR p_serialnum IS NULL OR p_serialnum = '')
    LIMIT 1;

IF v_returnID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Return transaction reference not found.';
    ELSEIF v_sale_itemID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Item not found in the original sale record.';
    ELSEIF p_qty <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Return quantity must be greater than zero.';
ELSE

        INSERT INTO return_item (
            return_quantity,
            reason,
            return_status,
            notes,
            returnID,
            sale_itemID
        )
        VALUES (
            p_qty,
            p_reason,
            p_status,
            p_notes,
            v_returnID,
            v_sale_itemID
        );
END IF;
END //

DELIMITER ;

DELIMITER //
CREATE PROCEDURE record_sale(
    IN p_public_id VARCHAR(20),
    IN p_customerID INT,
    IN p_employeeID INT,
    IN p_fulfillment_method ENUM('Pickup', 'Delivery', 'Walk-in'),
    IN p_shipping_name VARCHAR(100),
    IN p_shipping_street VARCHAR(100),
    IN p_shipping_barangay VARCHAR(50),
    IN p_shipping_city_municipality VARCHAR(50),
    IN p_shipping_province VARCHAR(50),
    IN p_shipping_zip_code VARCHAR(10))
BEGIN
   IF p_public_id IS NULL OR TRIM(p_public_id) = '' THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Public Sale ID is required.';
   ELSEIF p_customerID IS NULL THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Customer ID is required.';
   ELSEIF p_fulfillment_method IS NULL THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Fulfillment method is required.';
   ELSEIF NOT EXISTS (SELECT 1 FROM customer WHERE customerID = p_customerID) THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Customer not found.';
   ELSEIF p_fulfillment_method <> 'Walk-in' AND EXISTS (
       SELECT 1
       FROM customer
       WHERE customerID = p_customerID
         AND deleted_at IS NOT NULL
   ) THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Deactivated customers cannot place online orders.';
   ELSEIF p_fulfillment_method = 'Delivery' AND (
       p_shipping_name IS NULL OR TRIM(p_shipping_name) = ''
       OR p_shipping_street IS NULL OR TRIM(p_shipping_street) = ''
       OR p_shipping_barangay IS NULL OR TRIM(p_shipping_barangay) = ''
       OR p_shipping_city_municipality IS NULL OR TRIM(p_shipping_city_municipality) = ''
       OR p_shipping_province IS NULL OR TRIM(p_shipping_province) = ''
       OR p_shipping_zip_code IS NULL OR TRIM(p_shipping_zip_code) = ''
   ) THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Delivery orders require complete shipping details.';
ELSE
       INSERT INTO sale (
           public_id, sale_date, total_amount, customerID, employeeID, fulfillment_method,
           shipping_name, shipping_street, shipping_barangay, shipping_city_municipality,
           shipping_province, shipping_zip_code
       )
       VALUES (
           p_public_id, NOW(), 0, p_customerID, p_employeeID, p_fulfillment_method,
           NULLIF(TRIM(p_shipping_name), ''),
           NULLIF(TRIM(p_shipping_street), ''),
           NULLIF(TRIM(p_shipping_barangay), ''),
           NULLIF(TRIM(p_shipping_city_municipality), ''),
           NULLIF(TRIM(p_shipping_province), ''),
           NULLIF(TRIM(p_shipping_zip_code), '')
       );
END IF;
END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE record_sale_item(
    IN p_product_public_id VARCHAR(20),
    IN p_sale_public_id VARCHAR(20),
    IN p_quantity INT,
    IN p_price DECIMAL(10,2),
    IN p_serialnum VARCHAR(30))
BEGIN
   DECLARE v_productID INT;
   DECLARE v_saleID INT;

SELECT productID INTO v_productID FROM product WHERE public_id = p_product_public_id;
SELECT saleID INTO v_saleID FROM sale WHERE public_id = p_sale_public_id;

IF v_productID IS NULL THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Product reference not found.';
   ELSEIF v_saleID IS NULL THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Sale reference not found.';
   ELSEIF p_quantity IS NULL OR p_quantity <= 0 THEN
       SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Quantity must be greater than zero.';
ELSE
       INSERT INTO sale_item (serial_number, quantity_sold, price_at_sale, saleID, productID)
       VALUES (p_serialnum, p_quantity, p_price, v_saleID, v_productID);

UPDATE sale SET total_amount = total_amount + (p_price * p_quantity) WHERE saleID = v_saleID;
END IF;
END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE record_payment(
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
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE record_refund_payment(
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
        INSERT INTO refund_payment (
            refund_date, amount, payment_method, payment_status, public_id, returnID
        ) VALUES (
            NOW(), p_amount, p_method, p_status, v_refund_public_id, v_returnID
        )
        ON DUPLICATE KEY UPDATE
                             amount = VALUES(amount),
                             payment_method = VALUES(payment_method),
                             payment_status = VALUES(payment_status),
                             public_id = VALUES(public_id),
                             updated_at = NOW();
END IF;
END //
DELIMITER ;

DELIMITER //

CREATE PROCEDURE update_customer_master(
    IN p_public_id VARCHAR(20),
    IN p_first_name VARCHAR(50),
    IN p_last_name VARCHAR(50),
    IN p_middle_name VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_contact VARCHAR(12),
    IN p_street VARCHAR(45),
    IN p_barangay VARCHAR(45),
    IN p_city VARCHAR(45),
    IN p_province VARCHAR(45),
    IN p_zip VARCHAR(4),
    IN p_status VARCHAR(30),
    IN p_deleted_at DATETIME
)
  BEGIN
      DECLARE v_custID INT;
      DECLARE v_status_normalized VARCHAR(30);
      DECLARE v_existing_deleted_at DATETIME;
      DECLARE v_new_deleted_at DATETIME;
      DECLARE v_system_employee_id INT;
      DECLARE v_cancel_count INT DEFAULT 0;

  SELECT customerID, deleted_at INTO v_custID, v_existing_deleted_at
  FROM customer
  WHERE public_id = p_public_id
  LIMIT 1;
  SET v_status_normalized = NULLIF(TRIM(COALESCE(p_status, '')), '');

  IF v_custID IS NULL THEN
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Customer not found.';
  ELSEIF v_status_normalized IS NOT NULL AND v_status_normalized NOT IN ('Active', 'Merged', 'Deleted_by_User') THEN
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Invalid customer status.';
  ELSE
      SET v_new_deleted_at = CASE
                                WHEN p_deleted_at IS NOT NULL THEN p_deleted_at
                                WHEN v_status_normalized IS NULL THEN v_existing_deleted_at
                                WHEN v_status_normalized = 'Active' THEN NULL
                                ELSE COALESCE(v_existing_deleted_at, NOW())
                             END;

  UPDATE customer
  SET
      first_name = COALESCE(NULLIF(TRIM(p_first_name), ''), first_name),
      last_name = COALESCE(NULLIF(TRIM(p_last_name), ''), last_name),
      middle_name = COALESCE(NULLIF(TRIM(p_middle_name), ''), middle_name),
      email_address = COALESCE(NULLIF(TRIM(p_email), ''), email_address),
      contact_number = COALESCE(NULLIF(TRIM(p_contact), ''), contact_number),
      street_address = COALESCE(NULLIF(TRIM(p_street), ''), street_address),
      barangay = COALESCE(NULLIF(TRIM(p_barangay), ''), barangay),
      city_municipality = COALESCE(NULLIF(TRIM(p_city), ''), city_municipality),
      province = COALESCE(NULLIF(TRIM(p_province), ''), province),
      zip_code = COALESCE(NULLIF(TRIM(p_zip), ''), zip_code),
      status = COALESCE(v_status_normalized, status),
      deleted_at = v_new_deleted_at
  WHERE customerID = v_custID;

      IF v_existing_deleted_at IS NULL AND v_new_deleted_at IS NOT NULL THEN
          SELECT employeeID INTO v_system_employee_id
          FROM employee
          WHERE public_id = 'EM-B04CF45C'
          LIMIT 1;

          IF v_system_employee_id IS NULL THEN
              SELECT employeeID INTO v_system_employee_id
              FROM employee
              WHERE employee_status = 'Active'
              ORDER BY employeeID ASC
              LIMIT 1;
          END IF;

          IF v_system_employee_id IS NULL THEN
              SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No active employee found to record sale cancellations.';
          END IF;

          CREATE TEMPORARY TABLE tmp_cancel_sales (saleID INT PRIMARY KEY);

          INSERT INTO tmp_cancel_sales (saleID)
          SELECT saleID
          FROM sale
          WHERE customerID = v_custID
            AND sale_status NOT IN ('Cancelled', 'Completed', 'Shipped', 'Delivered');

          SET v_cancel_count = ROW_COUNT();

          IF v_cancel_count > 0 THEN
              UPDATE sale
              SET sale_status = 'Cancelled',
                  employeeID = v_system_employee_id,
                  updated_at = NOW()
              WHERE saleID IN (SELECT saleID FROM tmp_cancel_sales);

              INSERT INTO inventory_transaction (
                  transaction_date,
                  quantity_change,
                  transaction_type,
                  referenceID,
                  productID,
                  employeeID
              )
              SELECT
                  NOW(),
                  si.quantity_sold,
                  'Cancelled Sale',
                  si.saleID,
                  si.productID,
                  v_system_employee_id
              FROM sale_item si
              JOIN tmp_cancel_sales t ON t.saleID = si.saleID;

              UPDATE payment p
              JOIN tmp_cancel_sales t ON t.saleID = p.saleID
              SET p.payment_status = 'Cancelled',
                  p.updated_at = NOW()
              WHERE p.payment_status <> 'Cancelled';
          END IF;

          DROP TEMPORARY TABLE IF EXISTS tmp_cancel_sales;
      END IF;
  END IF;
  END //
DELIMITER ;

DELIMITER //

CREATE PROCEDURE update_employee_master(
    IN p_public_id VARCHAR(20),
    IN p_first_name VARCHAR(50),
    IN p_last_name VARCHAR(50),
    IN p_role VARCHAR(50),
    IN p_status ENUM('Active', 'Inactive'),
    IN p_email VARCHAR(100),
    IN p_contact VARCHAR(12)
        )
BEGIN
    DECLARE v_email VARCHAR(100);
    DECLARE v_contact VARCHAR(12);

    SET v_email = NULLIF(LOWER(TRIM(COALESCE(p_email, ''))), '');
    SET v_contact = NULLIF(TRIM(COALESCE(p_contact, '')), '');
    IF v_contact IS NOT NULL THEN
        SET v_contact = NULLIF(REGEXP_REPLACE(v_contact, '[^0-9]', ''), '');
        IF v_contact REGEXP '^0[0-9]{10}$' THEN
            SET v_contact = CONCAT('63', SUBSTRING(v_contact, 2));
        END IF;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM employee WHERE public_id = p_public_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Employee record not found.';
    ELSEIF v_email IS NOT NULL AND v_email NOT REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid email format.';
    ELSEIF v_contact IS NOT NULL AND v_contact NOT REGEXP '^63[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Contact number must be 12 digits and start with 63.';
ELSE
UPDATE employee
SET
    first_name = COALESCE(NULLIF(TRIM(p_first_name), ''), first_name),
    last_name = COALESCE(NULLIF(TRIM(p_last_name), ''), last_name),
    employee_role = COALESCE(NULLIF(TRIM(p_role), ''), employee_role),
    employee_status = COALESCE(p_status, employee_status),
    email_address = COALESCE(v_email, email_address),
    contact_number = COALESCE(v_contact, contact_number)
WHERE public_id = p_public_id;
END IF;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE update_supplier_master(
    IN p_public_id VARCHAR(20),
    IN p_supplier_name VARCHAR(100),
    IN p_contact_first VARCHAR(50),
    IN p_contact_last VARCHAR(50),
    IN p_contact_number VARCHAR(12),
    IN p_email VARCHAR(100),
    IN p_street VARCHAR(45),
    IN p_barangay VARCHAR(45),
    IN p_city VARCHAR(45),
    IN p_province VARCHAR(45),
    IN p_zip VARCHAR(4)
)
BEGIN
    DECLARE v_email VARCHAR(100);
    DECLARE v_contact VARCHAR(12);
    DECLARE v_digits VARCHAR(20);

    SET v_email = NULLIF(LOWER(TRIM(COALESCE(p_email, ''))), '');
    SET v_contact = NULLIF(TRIM(COALESCE(p_contact_number, '')), '');
    IF v_contact IS NOT NULL THEN
        IF UPPER(v_contact) IN ('N/A', 'NA') THEN
            SET v_contact = NULL;
        END IF;
    END IF;
    IF v_contact IS NOT NULL THEN
        SET v_digits = NULLIF(REGEXP_REPLACE(v_contact, '[^0-9]', ''), '');
        IF v_digits REGEXP '^63[0-9]{10}$' THEN
            SET v_digits = CONCAT('0', SUBSTRING(v_digits, 3));
        END IF;

        IF v_digits REGEXP '^02[0-9]{8}$' THEN
            SET v_contact = v_digits;
        ELSEIF v_digits REGEXP '^09[0-9]{9}$' THEN
            SET v_contact = v_digits;
        ELSE
            SET v_contact = NULLIF(TRIM(v_contact), '');
        END IF;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM supplier WHERE public_id = p_public_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Supplier record not found.';
    ELSEIF v_email IS NOT NULL AND v_email NOT REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid email format.';
    ELSEIF v_contact IS NOT NULL AND v_contact NOT REGEXP '^(02[0-9]{8}|09[0-9]{9})$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Contact number must be in 0288881111 or 09170001111 format.';
    ELSEIF v_email IS NOT NULL
       AND EXISTS (
           SELECT 1
           FROM supplier
           WHERE email_address = v_email
             AND public_id <> p_public_id
       ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: This supplier email is already in use.';
    ELSEIF v_contact IS NOT NULL
       AND EXISTS (
           SELECT 1
           FROM supplier
           WHERE contact_number = v_contact
             AND public_id <> p_public_id
       ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: This supplier contact number is already in use.';

    ELSEIF (v_email IS NULL)
       AND (v_contact IS NULL)
       AND EXISTS (SELECT 1 FROM supplier WHERE public_id = p_public_id
                   AND email_address IS NULL AND contact_number IS NULL) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Supplier must maintain at least one contact method.';

ELSE

UPDATE supplier
SET

    supplier_name = COALESCE(NULLIF(UPPER(TRIM(p_supplier_name)), ''), supplier_name),
    contact_first_name = COALESCE(NULLIF(TRIM(p_contact_first), ''), contact_first_name),
    contact_last_name = COALESCE(NULLIF(TRIM(p_contact_last), ''), contact_last_name),
    contact_number = COALESCE(v_contact, contact_number),
    email_address = COALESCE(v_email, email_address),
    street_address = COALESCE(NULLIF(TRIM(p_street), ''), street_address),
    barangay = COALESCE(NULLIF(TRIM(p_barangay), ''), barangay),
    city_municipality = COALESCE(NULLIF(TRIM(p_city), ''), city_municipality),
    province = COALESCE(NULLIF(TRIM(p_province), ''), province),
    zip_code = COALESCE(NULLIF(TRIM(p_zip), ''), zip_code)
WHERE public_id = p_public_id;
END IF;
END //

DELIMITER ;


CREATE OR REPLACE VIEW customer_list AS
SELECT
    c.public_id AS customer_public_id,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    c.email_address,
    c.contact_number,
    c.city_municipality,
    c.province
FROM customer c;

CREATE OR REPLACE VIEW sales_summary AS
SELECT
    s.public_id AS sale_public_id,
    s.sale_date,
    c.public_id AS customer_public_id,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    s.total_amount
FROM sale s
         JOIN customer c
              ON s.customerID = c.customerID;

CREATE OR REPLACE VIEW supplier_products AS
SELECT
    s.public_id AS supplier_public_id,
    s.supplier_name,
    p.public_id AS product_public_id,
    p.product_name,
    ps.supplier_product_name,
    ps.wholesale_cost
FROM product_supplier ps
         JOIN supplier s
              ON ps.supplierID = s.supplierID
         JOIN product p
              ON ps.productID = p.productID
WHERE p.is_active = TRUE
  AND s.is_active = TRUE
  AND ps.is_active = TRUE;

CREATE OR REPLACE VIEW product_stock AS
SELECT
    p.public_id AS product_public_id,
    p.product_name,
    p.quantity AS current_stock
FROM product p;

CREATE OR REPLACE VIEW product_supplier_details AS
SELECT
    p.public_id AS product_public_id,
    p.product_name,
    p.quantity,
    p.selling_price,
    p.is_active AS product_is_active,
    s.public_id AS supplier_public_id,
    s.supplier_name,
    s.is_active AS supplier_is_active,
    ps.wholesale_cost,
    ps.is_active AS link_is_active
FROM product AS p
         LEFT JOIN product_supplier AS ps
                   ON p.productID = ps.productID
         LEFT JOIN supplier AS s
                   ON ps.supplierID = s.supplierID;

CREATE OR REPLACE VIEW return_transactions AS
SELECT
    rt.public_id AS return_public_id,
    rt.date_created,
    rt.refund_amount,
    c.public_id AS customer_public_id,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    e.public_id AS employee_public_id,
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name
FROM return_transaction rt
         JOIN sale s ON rt.saleID = s.saleID
         JOIN customer c ON s.customerID = c.customerID
         JOIN employee e ON rt.employeeID = e.employeeID;

CREATE OR REPLACE VIEW return_details AS
SELECT
    rt.public_id AS return_public_id,
    rt.date_created,
    rt.refund_amount,
    c.public_id AS customer_public_id,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    c.email_address AS customer_email,
    c.contact_number AS customer_phone,
    e.public_id AS employee_public_id,
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
    e.employee_role,
    ri.return_itemID,
    ri.return_quantity,
    ri.reason,
    ri.return_status,
    ri.notes,
    p.public_id AS product_public_id,
    p.product_name,
    s.public_id AS original_sale_public_id,
    s.sale_date AS original_sale_date,
    si.sale_itemID,
    si.quantity_sold AS original_quantity,
    si.price_at_sale,
    si.serial_number,
    dg.damaged_recordID,
    dg.damaged_quantity,
    dg.damaged_source

FROM return_transaction rt
         JOIN employee e ON rt.employeeID = e.employeeID
         JOIN sale s ON rt.saleID = s.saleID
         JOIN customer c ON s.customerID = c.customerID
         LEFT JOIN return_item ri ON rt.returnID = ri.returnID
         LEFT JOIN sale_item si ON ri.sale_itemID = si.sale_itemID
         LEFT JOIN product p ON si.productID = p.productID
         LEFT JOIN damaged_goods dg ON ri.return_itemID = dg.return_itemID;

CREATE OR REPLACE VIEW damaged_goods_report AS
SELECT
    dg.damaged_recordID,
    dg.damaged_date,
    dg.damaged_quantity,
    dg.damaged_source,
    dg.notes,
    p.public_id AS product_public_id,
    p.product_name,
    e.public_id AS employee_public_id,
    CONCAT(e.first_name, ' ', e.last_name) AS recorded_by_employee,
    ri.return_itemID,
    ri.return_status
FROM damaged_goods dg
         JOIN product p ON dg.productID = p.productID
         JOIN employee e ON dg.employeeID = e.employeeID
         LEFT JOIN return_item ri ON dg.return_itemID = ri.return_itemID;

CREATE OR REPLACE VIEW sale_details AS
SELECT
    s.public_id AS sale_public_id,
    s.sale_date,
    s.total_amount,
    c.public_id AS customer_public_id,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    e.public_id AS employee_public_id,
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
    si.sale_itemID,
    si.quantity_sold,
    si.price_at_sale,
    si.serial_number,
    p.public_id AS product_public_id,
    p.product_name,
    (si.quantity_sold * si.price_at_sale) AS item_total
FROM sale s
         JOIN customer c ON s.customerID = c.customerID
         JOIN employee e ON s.employeeID = e.employeeID
         JOIN sale_item si ON s.saleID = si.saleID
         JOIN product p ON si.productID = p.productID;

CREATE OR REPLACE VIEW inventory_transactions AS
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
         JOIN employee e ON it.employeeID = e.employeeID;

CREATE OR REPLACE VIEW product_return AS
SELECT
    p.public_id AS product_public_id,
    p.product_name,
    COUNT(DISTINCT ri.return_itemID) AS times_returned,
    SUM(ri.return_quantity) AS total_quantity_returned,
    COUNT(DISTINCT CASE WHEN ri.reason = 'Defective' THEN ri.return_itemID END) AS defective_returns,
    COUNT(DISTINCT CASE WHEN ri.reason = 'Change of Mind' THEN ri.return_itemID END) AS change_of_mind_returns
FROM product p
         LEFT JOIN sale_item si ON p.productID = si.productID
         LEFT JOIN return_item ri ON si.sale_itemID = ri.sale_itemID
GROUP BY
    p.productID,
    p.public_id,
    p.product_name;

CREATE OR REPLACE VIEW customer_return_history AS
SELECT
    c.public_id AS customer_public_id,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    c.email_address,
    COUNT(DISTINCT rt.returnID) AS total_returns,
    SUM(rt.refund_amount) AS total_refunded,
    MAX(rt.date_created) AS last_return_date
FROM customer c
         JOIN sale s ON c.customerID = s.customerID
         JOIN return_transaction rt ON s.saleID = rt.saleID
GROUP BY
    c.customerID,
    c.public_id,
    c.first_name,
    c.last_name,
    c.email_address;

CREATE OR REPLACE VIEW product_profit AS
SELECT
    p.public_id AS product_public_id,
    p.product_name,
    SUM(get_profit(ps.wholesale_cost, si.price_at_sale, si.quantity_sold)) AS total_profit
FROM sale_item si
         JOIN product p ON si.productID = p.productID
         JOIN product_supplier ps ON p.productID = ps.productID
GROUP BY
    p.productID,
    p.public_id,
    p.product_name
ORDER BY p.productID;

CREATE OR REPLACE VIEW overall_sales_profit AS
SELECT
    SUM(si.price_at_sale * si.quantity_sold) AS total_sales,
    SUM(get_profit(ps.wholesale_cost, si.price_at_sale, si.quantity_sold)) AS total_profit,
    SUM(ps.wholesale_cost * si.quantity_sold) AS total_cost
FROM sale_item si
         JOIN product_supplier ps ON si.productID = ps.productID;

UPDATE product p
    JOIN (
    SELECT productID, MIN(price_at_sale) as lowest_price
    FROM sale_item
    GROUP BY productID
    ) AS sub ON p.productID = sub.productID
    SET p.selling_price = sub.lowest_price
WHERE sub.lowest_price > 0;

DELIMITER //

CREATE PROCEDURE check_verified_purchase(
    IN p_customer_public_id VARCHAR(20),
    IN p_product_public_id VARCHAR(20),
    OUT p_is_verified BOOLEAN
)
BEGIN
    SET p_is_verified = FALSE;

    IF EXISTS (
        SELECT 1
        FROM sale s
        JOIN sale_item si ON s.saleID = si.saleID
        JOIN customer c ON s.customerID = c.customerID
        JOIN product p ON si.productID = p.productID
        WHERE c.public_id = p_customer_public_id
          AND p.public_id = p_product_public_id
          AND s.sale_status = 'Completed'
    ) THEN
        SET p_is_verified = TRUE;
END IF;
END //

DELIMITER ;

CREATE OR REPLACE VIEW api_customer_profile AS
SELECT
    public_id,
    first_name,
    last_name,
    email_address,
    contact_number,
    CONCAT(street_address, ', ', barangay, ', ', city_municipality, ', ', province) AS full_address,
    customer_type,
    created_at
FROM customer
WHERE status = 'Active'
  AND deleted_at IS NULL;

CREATE OR REPLACE VIEW api_product_catalog AS
SELECT
    public_id,
    product_name,
    quantity AS stock_level,
    selling_price,
    CASE
        WHEN quantity > 10 THEN 'In Stock'
        WHEN quantity > 0 THEN 'Low Stock'
        ELSE 'Out of Stock'
        END AS availability_status
FROM product
WHERE is_active = 1;

CREATE OR REPLACE VIEW api_order_history AS
SELECT
    s.public_id AS order_id,
    s.sale_date,
    s.total_amount,
    s.sale_status,
    s.fulfillment_method,
    c.public_id AS customer_id,
    COALESCE(s.tracking_number, 'N/A') AS tracking_number
FROM sale s
         JOIN customer c ON s.customerID = c.customerID;

CREATE OR REPLACE VIEW api_staff_directory AS
SELECT
    public_id AS employee_id,
    first_name,
    last_name,
    employee_role,
    email_address,
    employee_status
FROM employee
WHERE employee_status = 'Active';

DELIMITER //

CREATE TRIGGER after_credit_transaction_insert
    AFTER INSERT ON credit_history
    FOR EACH ROW
BEGIN
    UPDATE customer
    SET current_credit = current_credit + NEW.amount
    WHERE customerID = NEW.customerID;
END //

DELIMITER ;


DELIMITER //

CREATE PROCEDURE spend_credit(
    IN p_customer_public_id VARCHAR(20),
    IN p_amount DECIMAL(10,2),
    IN p_sale_public_id VARCHAR(20)
)
BEGIN
    DECLARE v_internal_custID INT;
    DECLARE v_internal_saleID INT;
    DECLARE v_current_balance DECIMAL(10,2);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
ROLLBACK;
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Store Credit transaction failed.';
END;

START TRANSACTION;
SELECT customerID, current_credit INTO v_internal_custID, v_current_balance
FROM customer
WHERE public_id = p_customer_public_id
FOR UPDATE;

SELECT saleID INTO v_internal_saleID
FROM sale
WHERE public_id = p_sale_public_id
LIMIT 1;
IF v_internal_custID IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Customer not found.';
        ELSEIF v_internal_saleID IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Invalid Sale Reference ID.';
        ELSEIF p_amount <= 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Amount must be greater than zero.';
        ELSEIF EXISTS (
            SELECT 1
            FROM credit_history
            WHERE customerID = v_internal_custID
              AND transaction_type = 'PURCHASE'
              AND reference_id = v_internal_saleID
            LIMIT 1
        ) THEN
            -- Idempotent: purchase already recorded for this sale.
            DO 0;
        ELSEIF v_current_balance < p_amount THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Denied: Insufficient store credit.';
ELSE
            INSERT INTO credit_history (customerID, amount, transaction_type, reference_id, balance_snapshot)
            VALUES (v_internal_custID, -p_amount, 'PURCHASE', v_internal_saleID, (v_current_balance - p_amount));
END IF;
COMMIT;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE credit_add(
    IN p_customer_public_id VARCHAR(20),
    IN p_amount DECIMAL(10,2),
    IN p_type ENUM('REFUND', 'PURCHASE', 'ADJUSTMENT'),
    IN p_reference_public_id VARCHAR(20)
        )
BEGIN
    DECLARE v_internal_custID INT;
    DECLARE v_internal_refID INT;
    DECLARE v_new_balance DECIMAL(10,2);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
ROLLBACK;
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Failed to record credit transaction.';
END;

START TRANSACTION;

SELECT customerID, current_credit INTO v_internal_custID, v_new_balance
FROM customer WHERE public_id = p_customer_public_id;

IF p_type = 'REFUND' THEN
SELECT returnID INTO v_internal_refID FROM return_transaction WHERE public_id = p_reference_public_id;
ELSEIF p_type = 'PURCHASE' THEN

SELECT saleID INTO v_internal_refID FROM sale WHERE public_id = p_reference_public_id;
ELSE
            SET v_internal_refID = NULL;
END IF;

        IF v_internal_custID IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Customer Public ID not found.';
        ELSEIF p_type IN ('REFUND', 'PURCHASE') AND v_internal_refID IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Invalid Reference Public ID (Sale or Return not found).';
ELSE
            INSERT INTO credit_history (customerID, amount, transaction_type, reference_id, balance_snapshot)
            VALUES (v_internal_custID, p_amount, p_type, v_internal_refID, (v_new_balance + p_amount));
END IF;
COMMIT;
END //

DELIMITER ;

CREATE OR REPLACE VIEW credit_statement AS
SELECT
    ch.credit_transactionID,
    ch.transaction_date,
    ch.amount,
    ch.transaction_type,
    ch.balance_snapshot,
    c.public_id AS customer_public_id,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    CASE
        WHEN ch.transaction_type = 'PURCHASE' THEN s.public_id
        WHEN ch.transaction_type = 'REFUND' THEN rt.public_id
        ELSE NULL
        END AS reference_public_id,
    CASE
        WHEN ch.transaction_type = 'PURCHASE' THEN CONCAT('Payment for Order #', s.public_id)
        WHEN ch.transaction_type = 'REFUND' THEN CONCAT('Refund from Return #', rt.public_id)
        ELSE 'Manual Adjustment'
        END AS description
FROM credit_history ch
         JOIN customer c ON ch.customerID = c.customerID
         LEFT JOIN sale s ON ch.reference_id = s.saleID AND ch.transaction_type = 'PURCHASE'
         LEFT JOIN return_transaction rt ON ch.reference_id = rt.returnID AND ch.transaction_type = 'REFUND'
ORDER BY ch.transaction_date DESC;

CREATE OR REPLACE VIEW api_customer AS
SELECT
    c.*,
    CASE
        WHEN c.status = 'Active' AND c.deleted_at IS NULL THEN 1
        ELSE 0
        END AS is_active
FROM customer c;
CREATE OR REPLACE VIEW api_product AS SELECT * FROM product;
CREATE OR REPLACE VIEW api_supplier AS
SELECT DISTINCT
    s.supplierID,
    s.public_id,
    s.supplier_name,
    s.contact_first_name,
    s.contact_last_name,
    s.contact_number,
    s.email_address,
    s.street_address,
    s.barangay,
    s.province,
    s.city_municipality,
    s.zip_code,
    s.created_at,
    s.updated_at,
    s.is_active
FROM supplier s;
CREATE OR REPLACE VIEW api_product_supplier AS SELECT * FROM product_supplier;
CREATE OR REPLACE VIEW api_employee AS SELECT * FROM employee;
CREATE OR REPLACE VIEW api_sale AS SELECT * FROM sale;
CREATE OR REPLACE VIEW api_sale_item AS SELECT * FROM sale_item;
CREATE OR REPLACE VIEW api_return_transaction AS SELECT * FROM return_transaction;
CREATE OR REPLACE VIEW api_return_item AS SELECT * FROM return_item;
CREATE OR REPLACE VIEW api_payment AS SELECT * FROM payment;
CREATE OR REPLACE VIEW api_refund_payment AS SELECT * FROM refund_payment;

CREATE OR REPLACE VIEW api_product_supplier_max AS
SELECT productID, MAX(wholesale_cost) AS max_wholesale
FROM product_supplier
WHERE is_active = 1
GROUP BY productID;

DELIMITER //

CREATE PROCEDURE product_public_id_update(
    IN p_product_id INT,
    IN p_public_id VARCHAR(20)
)
BEGIN
UPDATE product
SET public_id = p_public_id
WHERE productID = p_product_id;
END //

CREATE PROCEDURE product_name_update(
    IN p_product_public_id VARCHAR(20),
    IN p_product_name VARCHAR(100)
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM product WHERE public_id = p_product_public_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Product not found.';
ELSE
UPDATE product
SET product_name = p_product_name
WHERE public_id = p_product_public_id;
END IF;
END //

CREATE PROCEDURE product_wholesale_update(
    IN p_product_public_id VARCHAR(20),
    IN p_wholesale_cost DECIMAL(10,2)
)
BEGIN
    DECLARE v_productID INT;
SELECT productID INTO v_productID FROM product WHERE public_id = p_product_public_id LIMIT 1;
IF v_productID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Product not found.';
ELSE
UPDATE product_supplier
SET wholesale_cost = p_wholesale_cost
WHERE productID = v_productID
  AND is_active = 1;
END IF;
END //

CREATE PROCEDURE inventory_adjustment_add(
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
END //

CREATE PROCEDURE sale_shipping_update(
    IN p_sale_public_id VARCHAR(20),
    IN p_shipping_name VARCHAR(100),
    IN p_shipping_street VARCHAR(100),
    IN p_shipping_barangay VARCHAR(50),
    IN p_shipping_city_municipality VARCHAR(50),
    IN p_shipping_province VARCHAR(50),
    IN p_shipping_zip_code VARCHAR(10)
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM sale WHERE public_id = p_sale_public_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Sale not found.';
ELSE
UPDATE sale
SET shipping_name = p_shipping_name,
    shipping_street = p_shipping_street,
    shipping_barangay = p_shipping_barangay,
    shipping_city_municipality = p_shipping_city_municipality,
    shipping_province = p_shipping_province,
    shipping_zip_code = p_shipping_zip_code
WHERE public_id = p_sale_public_id;
END IF;
END //

CREATE PROCEDURE order_status_update(
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

SELECT saleID, sale_status INTO v_saleID, v_current_status FROM sale WHERE public_id = p_sale_public_id LIMIT 1;
SELECT employeeID INTO v_employeeID FROM employee WHERE public_id = p_employee_public_id LIMIT 1;

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
END //

CREATE PROCEDURE return_transaction_add(
    IN p_public_return_id VARCHAR(20),
    IN p_public_sale_id VARCHAR(20),
    IN p_public_employee_id VARCHAR(20),
    IN p_refund_amount DECIMAL(10,2),
    IN p_return_method VARCHAR(20)
)
BEGIN
    DECLARE v_saleID INT;
    DECLARE v_employeeID INT;

SELECT saleID INTO v_saleID FROM sale WHERE public_id = p_public_sale_id LIMIT 1;
SELECT employeeID INTO v_employeeID FROM employee WHERE public_id = p_public_employee_id LIMIT 1;

IF v_saleID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Sale not found.';
    ELSEIF v_employeeID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Employee not found.';
    ELSEIF p_refund_amount < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Refund cannot be negative.';
ELSE
        INSERT INTO return_transaction (
            public_id,
            date_created,
            refund_amount,
            employeeID,
            return_progress,
            return_method,
            saleID
        )
        VALUES (
            p_public_return_id,
            NOW(),
            p_refund_amount,
            v_employeeID,
            'Requested',
            p_return_method,
            v_saleID
        );
END IF;
END //

DELIMITER ;

ALTER TABLE sale AUTO_INCREMENT = 1;
ALTER TABLE customer AUTO_INCREMENT = 1;
ALTER TABLE damaged_goods AUTO_INCREMENT = 1;
ALTER TABLE employee AUTO_INCREMENT = 1;
ALTER TABLE inventory_transaction AUTO_INCREMENT = 1;
ALTER TABLE product AUTO_INCREMENT = 1;
ALTER TABLE return_item AUTO_INCREMENT = 1;
ALTER TABLE return_transaction AUTO_INCREMENT = 1;
ALTER TABLE sale_item AUTO_INCREMENT = 1;
ALTER TABLE supplier AUTO_INCREMENT = 1;


DELIMITER //
CREATE TRIGGER customer_status_sync
    BEFORE UPDATE ON customer
    FOR EACH ROW
BEGIN
    IF NEW.status IN ('Deleted_by_User', 'Merged') AND NEW.deleted_at IS NULL THEN
        SET NEW.deleted_at = CURRENT_TIMESTAMP;
    ELSEIF NEW.status = 'Active' AND NEW.deleted_at IS NULL THEN
        SET NEW.deleted_at = NULL;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER inventory_prevent_negative
    BEFORE INSERT ON inventory_transaction
    FOR EACH ROW
BEGIN
    DECLARE v_current_qty INT DEFAULT 0;

    SELECT quantity INTO v_current_qty
    FROM product
    WHERE productID = NEW.productID
    LIMIT 1;

    IF v_current_qty + NEW.quantity_change < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: Inventory adjustment would result in negative stock.';
    END IF;
END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE customer_soft_delete(
    IN p_public_id VARCHAR(20)
)
BEGIN
    DECLARE v_customerID INT;

    SELECT customerID INTO v_customerID
    FROM customer
    WHERE public_id = p_public_id
    LIMIT 1;

    IF v_customerID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Customer not found.';
    ELSE
        UPDATE customer
        SET status = 'Deleted_by_User',
            deleted_at = CURRENT_TIMESTAMP
        WHERE customerID = v_customerID;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE customer_restore(
    IN p_public_id VARCHAR(20)
)
BEGIN
    DECLARE v_customerID INT;

    SELECT customerID INTO v_customerID
    FROM customer
    WHERE public_id = p_public_id
    LIMIT 1;

    IF v_customerID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Customer not found.';
    ELSE
        UPDATE customer
        SET status = 'Active',
            deleted_at = NULL
        WHERE customerID = v_customerID;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE add_customer(
    IN p_public_id VARCHAR(20),
    IN p_first_name VARCHAR(50),
    IN p_middle_name VARCHAR(50),
    IN p_last_name VARCHAR(50),
    IN p_email_address VARCHAR(100),
    IN p_contact_number VARCHAR(12),
    IN p_street_address VARCHAR(45),
    IN p_barangay VARCHAR(45),
    IN p_province VARCHAR(45),
    IN p_city_municipality VARCHAR(45),
    IN p_zip_code VARCHAR(10)
)
BEGIN
    CALL customer_create(
        p_public_id,
        p_first_name,
        p_middle_name,
        p_last_name,
        'Walk-in',
        NULL,
        0,
        p_email_address,
        p_contact_number,
        p_street_address,
        p_barangay,
        p_province,
        p_city_municipality,
        p_zip_code,
        'Active'
    );
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sale_cancel(
    IN p_sale_public_id VARCHAR(20),
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
    ELSEIF v_current_status IN ('Cancelled', 'Completed', 'Shipped', 'Delivered') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Sale cannot be cancelled in its current status.';
    ELSE
        UPDATE sale
        SET sale_status = 'Cancelled',
            employeeID = v_employeeID
        WHERE saleID = v_saleID;

        INSERT INTO inventory_transaction (transaction_date, quantity_change, transaction_type, referenceID, productID, employeeID)
        SELECT CURRENT_TIMESTAMP, si.quantity_sold, 'Cancelled Sale', v_saleID, si.productID, v_employeeID
        FROM sale_item si
        WHERE si.saleID = v_saleID;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE refund_payment_upsert(
    IN p_amount DECIMAL(10,2),
    IN p_method ENUM('Cash', 'GCash', 'Card', 'Store Credit'),
    IN p_status ENUM('Pending', 'Failed', 'Refunded'),
    IN p_public_id VARCHAR(20),
    IN p_return_public_id VARCHAR(20),
    IN p_refund_date DATETIME
        )
BEGIN
    DECLARE v_returnID INT DEFAULT NULL;
    DECLARE v_refund_public_id VARCHAR(20) DEFAULT NULL;
    DECLARE v_refund_date DATETIME DEFAULT NULL;

    IF p_return_public_id IS NOT NULL THEN
        SELECT returnID INTO v_returnID FROM return_transaction WHERE public_id = p_return_public_id;
    END IF;

    IF p_public_id IS NULL OR TRIM(p_public_id) = '' THEN
        SET v_refund_public_id = CONCAT('RF-', UPPER(LEFT(REPLACE(UUID(), '-', ''), 10)));
    ELSE
        SET v_refund_public_id = p_public_id;
    END IF;

    SET v_refund_date = COALESCE(p_refund_date, NOW());

    IF p_amount IS NULL OR p_amount <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Amount must be greater than zero.';
    ELSEIF v_returnID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid Return Reference.';
    ELSE
        INSERT INTO refund_payment (
            refund_date, amount, payment_method, payment_status, public_id, returnID
        ) VALUES (
            v_refund_date, p_amount, p_method, p_status, v_refund_public_id, v_returnID
        )
        ON DUPLICATE KEY UPDATE
            refund_date = VALUES(refund_date),
            amount = VALUES(amount),
            payment_method = VALUES(payment_method),
            payment_status = VALUES(payment_status),
            public_id = VALUES(public_id),
            updated_at = NOW();
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE FUNCTION normalize_email_address(p_email VARCHAR(100)) RETURNS VARCHAR(100)
DETERMINISTIC
BEGIN
    RETURN NULLIF(LOWER(TRIM(COALESCE(p_email, ''))), '');
END //
DELIMITER ;

DELIMITER //
CREATE FUNCTION normalize_contact_number(p_contact VARCHAR(20)) RETURNS VARCHAR(12)
DETERMINISTIC
BEGIN
    DECLARE v_contact VARCHAR(20);
    SET v_contact = NULLIF(REGEXP_REPLACE(TRIM(COALESCE(p_contact, '')), '[^0-9]', ''), '');
    IF v_contact REGEXP '^0[0-9]{10}$' THEN
        SET v_contact = CONCAT('63', SUBSTRING(v_contact, 2));
    END IF;
    RETURN v_contact;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE customer_precheck(
    IN p_email_address VARCHAR(100),
    IN p_contact_number VARCHAR(12)
)
BEGIN
    DECLARE v_email VARCHAR(100);
    DECLARE v_contact VARCHAR(12);
    DECLARE v_email_customer_id INT DEFAULT NULL;
    DECLARE v_contact_customer_id INT DEFAULT NULL;
    DECLARE v_existing_customer_id INT DEFAULT NULL;
    DECLARE v_existing_customer_type VARCHAR(20) DEFAULT NULL;
    DECLARE v_existing_password_hash VARCHAR(255) DEFAULT NULL;

    SET v_email = normalize_email_address(p_email_address);
    SET v_contact = normalize_contact_number(p_contact_number);

    IF v_email IS NULL AND v_contact IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: You must provide either an email address or a contact number.';
    END IF;

    IF v_email IS NOT NULL THEN
        SELECT customerID INTO v_email_customer_id
        FROM customer
        WHERE email_address = v_email
          AND deleted_at IS NULL
        LIMIT 1;
    END IF;

    IF v_contact IS NOT NULL THEN
        SELECT customerID INTO v_contact_customer_id
        FROM customer
        WHERE contact_number = v_contact
          AND deleted_at IS NULL
        LIMIT 1;
    END IF;

    IF v_email_customer_id IS NOT NULL AND v_contact_customer_id IS NOT NULL AND v_email_customer_id <> v_contact_customer_id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Email and contact number belong to different customer records.';
    END IF;

    SET v_existing_customer_id = COALESCE(v_email_customer_id, v_contact_customer_id);
    IF v_existing_customer_id IS NOT NULL THEN
        SELECT customer_type, NULLIF(TRIM(password_hash), '')
        INTO v_existing_customer_type, v_existing_password_hash
        FROM customer
        WHERE customerID = v_existing_customer_id
        LIMIT 1;

        IF v_existing_customer_type = 'Registered' OR v_existing_password_hash IS NOT NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Customer already exists.';
        END IF;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE customer_reactivate(
    IN p_public_id VARCHAR(20)
)
BEGIN
    DECLARE v_customer_id INT DEFAULT NULL;
    DECLARE v_status VARCHAR(30) DEFAULT NULL;
    DECLARE v_deleted_at DATETIME DEFAULT NULL;
    DECLARE v_email VARCHAR(100) DEFAULT NULL;
    DECLARE v_contact VARCHAR(12) DEFAULT NULL;

    SELECT customerID, status, deleted_at, email_address, contact_number
    INTO v_customer_id, v_status, v_deleted_at, v_email, v_contact
    FROM customer
    WHERE public_id = p_public_id
    LIMIT 1;

    IF v_customer_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Customer not found.';
    ELSEIF v_status = 'Merged' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: This account was merged and cannot be reactivated.';
    ELSEIF v_deleted_at IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Account is already active.';
    ELSEIF TIMESTAMPDIFF(SECOND, v_deleted_at, NOW()) > 604800 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Reactivation window expired.';
    ELSEIF v_email IS NOT NULL AND EXISTS (
        SELECT 1 FROM customer
        WHERE email_address = v_email
          AND deleted_at IS NULL
          AND customerID <> v_customer_id
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Email is in use by another active account.';
    ELSEIF v_contact IS NOT NULL AND EXISTS (
        SELECT 1 FROM customer
        WHERE contact_number = v_contact
          AND deleted_at IS NULL
          AND customerID <> v_customer_id
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Contact number is in use by another active account.';
    ELSE
        UPDATE customer
        SET status = 'Active',
            deleted_at = NULL
        WHERE customerID = v_customer_id;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE customer_password_update(
    IN p_public_id VARCHAR(20),
    IN p_password_hash VARCHAR(255)
)
BEGIN
    DECLARE v_customer_id INT DEFAULT NULL;

    SELECT customerID INTO v_customer_id
    FROM customer
    WHERE public_id = p_public_id
    LIMIT 1;

    IF v_customer_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Customer not found.';
    ELSEIF p_password_hash IS NULL OR TRIM(p_password_hash) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Password hash is required.';
    ELSE
        UPDATE customer
        SET password_hash = p_password_hash,
            customer_type = 'Registered',
            updated_at = NOW()
        WHERE customerID = v_customer_id;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE payment_status_update_by_id(
    IN p_payment_id INT,
    IN p_status ENUM('Completed', 'Pending', 'Failed', 'Refunded', 'Cancelled')
)
BEGIN
    IF p_payment_id IS NULL OR p_payment_id <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Invalid payment reference.';
    ELSEIF NOT EXISTS (SELECT 1 FROM payment WHERE paymentID = p_payment_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Payment reference not found.';
    ELSE
        UPDATE payment
        SET payment_status = p_status,
            updated_at = NOW()
        WHERE paymentID = p_payment_id;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE credit_adjustment_add(
    IN p_customer_public_id VARCHAR(20),
    IN p_amount DECIMAL(10,2),
    IN p_sale_public_id VARCHAR(20)
)
BEGIN
    DECLARE v_customer_id INT DEFAULT NULL;
    DECLARE v_sale_id INT DEFAULT NULL;
    DECLARE v_current_balance DECIMAL(10,2) DEFAULT 0;

    IF p_amount IS NULL OR p_amount <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Amount must be greater than zero.';
    END IF;

    SELECT customerID, current_credit INTO v_customer_id, v_current_balance
    FROM customer WHERE public_id = p_customer_public_id;

    SELECT saleID INTO v_sale_id
    FROM sale WHERE public_id = p_sale_public_id;

    IF v_customer_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Customer not found.';
    ELSEIF v_sale_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Invalid sale reference.';
    ELSE
        INSERT INTO credit_history (customerID, amount, transaction_type, reference_id, balance_snapshot)
        VALUES (v_customer_id, p_amount, 'ADJUSTMENT', v_sale_id, (v_current_balance + p_amount));
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE order_status_update_full(
    IN p_sale_public_id VARCHAR(20),
    IN p_sale_status VARCHAR(30),
    IN p_tracking_number VARCHAR(50),
    IN p_courier_name VARCHAR(50),
    IN p_employee_public_id VARCHAR(20)
)
BEGIN
    DECLARE v_sale_id INT DEFAULT NULL;
    DECLARE v_employee_id INT DEFAULT NULL;
    DECLARE v_customer_id INT DEFAULT NULL;
    DECLARE v_customer_public_id VARCHAR(20) DEFAULT NULL;
    DECLARE v_current_status VARCHAR(30) DEFAULT NULL;
    DECLARE v_fulfillment_method VARCHAR(20) DEFAULT NULL;
    DECLARE v_payment_id INT DEFAULT NULL;
    DECLARE v_payment_method VARCHAR(30) DEFAULT NULL;
    DECLARE v_payment_status VARCHAR(30) DEFAULT NULL;
    DECLARE v_payment_amount DECIMAL(10,2) DEFAULT 0;
    DECLARE v_tracking VARCHAR(50);
    DECLARE v_courier VARCHAR(50);
    DECLARE v_purchase_amount DECIMAL(10,2) DEFAULT NULL;
    DECLARE v_refund_amount DECIMAL(10,2) DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Unable to update order status.';
    END;

    START TRANSACTION;

    SELECT saleID, sale_status, fulfillment_method, customerID
    INTO v_sale_id, v_current_status, v_fulfillment_method, v_customer_id
    FROM sale
    WHERE public_id = p_sale_public_id
    LIMIT 1
    FOR UPDATE;

    SELECT employeeID INTO v_employee_id
    FROM employee
    WHERE public_id = p_employee_public_id
    LIMIT 1;

    IF v_sale_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Sale not found.';
    ELSEIF v_employee_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Employee not found.';
    ELSEIF p_sale_status NOT IN ('Pending', 'Processing', 'Ready for Pickup', 'Shipped', 'Delivered', 'Completed', 'Cancelled') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid sale status.';
    ELSEIF v_current_status = 'Cancelled' AND p_sale_status <> 'Cancelled' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Cannot change status of a cancelled order.';
    ELSEIF v_current_status = 'Completed' AND p_sale_status <> 'Completed' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Cannot change status of a completed order.';
    ELSEIF v_current_status = 'Shipped' AND p_sale_status = 'Pending' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Fraud Alert: Cannot revert a shipped order backward to pending.';
    ELSEIF v_fulfillment_method = 'Pickup' AND p_sale_status = 'Shipped' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Pickup orders cannot be marked as Shipped.';
    ELSEIF v_fulfillment_method = 'Delivery' AND p_sale_status = 'Ready for Pickup' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Delivery orders cannot be marked Ready for Pickup.';
    END IF;

    SET v_tracking = NULLIF(TRIM(p_tracking_number), '');
    SET v_courier = NULLIF(TRIM(p_courier_name), '');
    IF p_sale_status = 'Ready for Pickup' THEN
        SET v_tracking = NULL;
        SET v_courier = NULL;
    END IF;

    UPDATE sale
    SET sale_status = p_sale_status,
        tracking_number = v_tracking,
        courier_name = v_courier,
        employeeID = v_employee_id
    WHERE saleID = v_sale_id;

    SELECT paymentID, payment_method, payment_status, amount
    INTO v_payment_id, v_payment_method, v_payment_status, v_payment_amount
    FROM payment
    WHERE saleID = v_sale_id
    ORDER BY paymentID DESC
    LIMIT 1
    FOR UPDATE;

    IF v_payment_id IS NOT NULL THEN
        IF LOWER(TRIM(v_payment_method)) IN ('cash', 'cash on delivery', 'cod') THEN
            IF p_sale_status IN ('Completed', 'Delivered') AND v_payment_status <> 'Completed' THEN
                UPDATE payment SET payment_status = 'Completed', updated_at = NOW() WHERE paymentID = v_payment_id;
            END IF;
        END IF;

        IF p_sale_status = 'Cancelled' AND v_payment_status <> 'Cancelled' THEN
            UPDATE payment SET payment_status = 'Cancelled', updated_at = NOW() WHERE paymentID = v_payment_id;
        END IF;
    END IF;

    IF p_sale_status = 'Cancelled' AND v_current_status <> 'Cancelled' THEN
        IF NOT EXISTS (
            SELECT 1
            FROM inventory_transaction
            WHERE referenceID = v_sale_id
              AND transaction_type = 'Cancelled Sale'
            LIMIT 1
        ) THEN
            INSERT INTO inventory_transaction (
                transaction_date,
                quantity_change,
                transaction_type,
                referenceID,
                productID,
                employeeID
            )
            SELECT
                NOW(),
                si.quantity_sold,
                'Cancelled Sale',
                si.saleID,
                si.productID,
                v_employee_id
            FROM sale_item si
            WHERE si.saleID = v_sale_id;
        END IF;

        IF v_payment_method = 'Store Credit' AND v_customer_id IS NOT NULL THEN
            SELECT public_id INTO v_customer_public_id
            FROM customer
            WHERE customerID = v_customer_id
            LIMIT 1;

            SELECT amount INTO v_purchase_amount
            FROM credit_history
            WHERE customerID = v_customer_id
              AND transaction_type = 'PURCHASE'
              AND reference_id = v_sale_id
            ORDER BY credit_transactionID DESC
            LIMIT 1;

            IF v_purchase_amount IS NOT NULL AND v_purchase_amount < 0 THEN
                SET v_refund_amount = ABS(v_purchase_amount);
            ELSE
                SET v_refund_amount = NULL;
            END IF;

            IF v_refund_amount IS NULL OR v_refund_amount <= 0 THEN
                SET v_refund_amount = ABS(v_payment_amount);
            END IF;

            IF v_refund_amount > 0 THEN
                IF NOT EXISTS (
                    SELECT 1
                    FROM credit_history
                    WHERE customerID = v_customer_id
                      AND transaction_type = 'ADJUSTMENT'
                      AND reference_id = v_sale_id
                      AND amount > 0
                    LIMIT 1
                ) THEN
                    CALL credit_adjustment_add(v_customer_public_id, v_refund_amount, p_sale_public_id);
                END IF;
            END IF;
        END IF;
    END IF;

    COMMIT;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE order_status_update_customer(
    IN p_sale_public_id VARCHAR(20),
    IN p_sale_status VARCHAR(30),
    IN p_tracking_number VARCHAR(50),
    IN p_courier_name VARCHAR(50),
    IN p_employee_public_id VARCHAR(20)
)
BEGIN
    DECLARE v_current_status VARCHAR(30) DEFAULT NULL;

    SELECT sale_status INTO v_current_status
    FROM sale
    WHERE public_id = p_sale_public_id
    LIMIT 1;

    IF v_current_status IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Sale not found.';
    END IF;

    IF p_sale_status = 'Completed' AND v_current_status NOT IN ('Shipped', 'Delivered') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Only shipped/delivered orders can be marked as received.';
    END IF;

    IF p_sale_status = 'Cancelled' THEN
        IF v_current_status IN ('Completed', 'Delivered', 'Shipped') THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Only processing orders can be cancelled.';
        ELSEIF v_current_status NOT IN ('Pending', 'Processing', 'Ready for Pickup', 'Unpaid') THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Order cannot be cancelled in its current status.';
        END IF;
    END IF;

    CALL order_status_update_full(
        p_sale_public_id,
        p_sale_status,
        p_tracking_number,
        p_courier_name,
        p_employee_public_id
    );
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE return_update_admin(
    IN p_return_public_id VARCHAR(20),
    IN p_item_status VARCHAR(20),
    IN p_progress VARCHAR(20),
    IN p_employee_public_id VARCHAR(20)
)
BEGIN
    DECLARE v_return_id INT DEFAULT NULL;
    DECLARE v_return_item_id INT DEFAULT NULL;
    DECLARE v_current_progress VARCHAR(20) DEFAULT NULL;
    DECLARE v_current_item_status VARCHAR(20) DEFAULT NULL;
    DECLARE v_sale_id INT DEFAULT NULL;
    DECLARE v_sale_public_id VARCHAR(20) DEFAULT NULL;
    DECLARE v_customer_id INT DEFAULT NULL;
    DECLARE v_customer_public_id VARCHAR(20) DEFAULT NULL;
    DECLARE v_price_at_sale DECIMAL(10,2) DEFAULT 0;
    DECLARE v_return_qty INT DEFAULT 0;
    DECLARE v_refund_amount DECIMAL(10,2) DEFAULT 0;
    DECLARE v_payment_method VARCHAR(30) DEFAULT 'Cash';
    DECLARE v_should_credit BOOLEAN DEFAULT FALSE;
    DECLARE v_should_refund BOOLEAN DEFAULT FALSE;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Unable to update return transaction.';
    END;

    START TRANSACTION;

    SELECT
        rt.returnID,
        rt.return_progress,
        ri.return_itemID,
        ri.return_status,
        ri.return_quantity,
        si.price_at_sale,
        rt.saleID
    INTO
        v_return_id,
        v_current_progress,
        v_return_item_id,
        v_current_item_status,
        v_return_qty,
        v_price_at_sale,
        v_sale_id
    FROM return_transaction rt
    JOIN return_item ri ON ri.returnID = rt.returnID
    JOIN sale_item si ON si.sale_itemID = ri.sale_itemID
    WHERE rt.public_id = p_return_public_id
    LIMIT 1
    FOR UPDATE;

    IF v_return_id IS NULL OR v_return_item_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Return transaction not found.';
    END IF;

    IF p_progress NOT IN ('Requested', 'In Process', 'Approved', 'Rejected', 'Finalized') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid return progress state.';
    END IF;
    IF p_item_status NOT IN ('Refunded', 'Replaced', 'Store Credit', 'Pending') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid return item status.';
    END IF;

    IF v_current_progress = 'Requested' AND p_progress NOT IN ('Requested', 'Approved', 'Rejected') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid return transition.';
    ELSEIF v_current_progress = 'Approved' AND p_progress NOT IN ('Approved', 'In Process') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid return transition.';
    ELSEIF v_current_progress = 'In Process' AND p_progress NOT IN ('In Process', 'Finalized', 'Rejected') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid return transition.';
    ELSEIF v_current_progress = 'Rejected' AND p_progress <> 'Rejected' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid return transition.';
    ELSEIF v_current_progress = 'Finalized' AND p_progress <> 'Finalized' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Invalid return transition.';
    END IF;

    IF (p_progress IN ('Approved', 'In Process', 'Rejected')) AND p_item_status NOT IN ('Pending', 'Store Credit') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Return item status must stay Pending or Store Credit before finalization.';
    END IF;
    IF p_progress = 'Finalized' AND p_item_status NOT IN ('Refunded', 'Store Credit', 'Replaced') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Validation Error: Finalized returns require a final item outcome.';
    END IF;

    CALL update_return_item_status(v_return_item_id, p_item_status, p_employee_public_id);
    CALL update_return_progress(p_return_public_id, p_progress, p_employee_public_id);

    IF p_progress = 'Finalized' AND p_item_status = 'Refunded' THEN
        SET v_should_refund = TRUE;
    END IF;
    IF p_item_status = 'Store Credit' AND v_current_progress <> p_progress AND p_progress IN ('Approved', 'Finalized') THEN
        SET v_should_credit = TRUE;
    END IF;

    IF v_should_refund OR v_should_credit THEN
        SET v_refund_amount = v_price_at_sale * v_return_qty;
        IF v_refund_amount < 0 THEN
            SET v_refund_amount = 0;
        END IF;

        SELECT public_id INTO v_sale_public_id FROM sale WHERE saleID = v_sale_id LIMIT 1;
        SELECT customerID INTO v_customer_id FROM sale WHERE saleID = v_sale_id LIMIT 1;
        SELECT public_id INTO v_customer_public_id FROM customer WHERE customerID = v_customer_id LIMIT 1;

        IF v_refund_amount > 0 THEN
            IF p_item_status = 'Store Credit' THEN
                IF NOT EXISTS (
                    SELECT 1 FROM credit_history
                    WHERE customerID = v_customer_id
                      AND transaction_type = 'REFUND'
                      AND reference_id = v_return_id
                    LIMIT 1
                ) THEN
                    CALL credit_add(v_customer_public_id, v_refund_amount, 'REFUND', p_return_public_id);
                END IF;

                CALL record_refund_payment(v_refund_amount, 'Store Credit', 'Refunded', NULL, p_return_public_id);
            ELSE
                SELECT payment_method INTO v_payment_method
                FROM payment
                WHERE saleID = v_sale_id
                ORDER BY paymentID DESC
                LIMIT 1;

                IF v_payment_method IS NULL OR v_payment_method = '' THEN
                    SET v_payment_method = 'Cash';
                END IF;

                CALL record_refund_payment(v_refund_amount, v_payment_method, 'Refunded', NULL, p_return_public_id);
            END IF;
        END IF;
    END IF;

    COMMIT;
END //
DELIMITER ;


CREATE OR REPLACE VIEW low_stock AS
SELECT
    p.public_id AS product_public_id,
    p.product_name,
    p.quantity AS current_stock,
    p.is_active
FROM product p
WHERE p.is_active = TRUE
  AND p.quantity <= 5;

CREATE OR REPLACE VIEW open_orders AS
SELECT
    s.public_id AS sale_public_id,
    s.sale_date,
    s.sale_status,
    s.total_amount,
    c.public_id AS customer_public_id,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name
FROM sale s
         JOIN customer c ON s.customerID = c.customerID
WHERE s.sale_status NOT IN ('Completed', 'Cancelled');

CREATE OR REPLACE VIEW customer_balance AS
SELECT
    c.public_id AS customer_public_id,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    c.email_address,
    c.contact_number,
    c.current_credit,
    c.status,
    c.deleted_at
FROM customer c;




DELIMITER //
CREATE TRIGGER customer_contact_normalize_insert
    BEFORE INSERT ON customer
    FOR EACH ROW
BEGIN
    IF NEW.email_address IS NOT NULL THEN
        SET NEW.email_address = NULLIF(LOWER(TRIM(NEW.email_address)), '');
    END IF;

    IF NEW.contact_number IS NOT NULL THEN
        SET NEW.contact_number = NULLIF(TRIM(NEW.contact_number), '');
    END IF;

    IF NEW.contact_number IS NOT NULL THEN
        SET NEW.contact_number = NULLIF(REGEXP_REPLACE(NEW.contact_number, '[^0-9]', ''), '');
        IF NEW.contact_number REGEXP '^0[0-9]{10}$' THEN
            SET NEW.contact_number = CONCAT('63', SUBSTRING(NEW.contact_number, 2));
        END IF;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER customer_contact_normalize_update
    BEFORE UPDATE ON customer
    FOR EACH ROW
BEGIN
    IF NEW.email_address IS NOT NULL THEN
        SET NEW.email_address = NULLIF(LOWER(TRIM(NEW.email_address)), '');
    END IF;

    IF NEW.contact_number IS NOT NULL THEN
        SET NEW.contact_number = NULLIF(TRIM(NEW.contact_number), '');
    END IF;

    IF NEW.contact_number IS NOT NULL THEN
        SET NEW.contact_number = NULLIF(REGEXP_REPLACE(NEW.contact_number, '[^0-9]', ''), '');
        IF NEW.contact_number REGEXP '^0[0-9]{10}$' THEN
            SET NEW.contact_number = CONCAT('63', SUBSTRING(NEW.contact_number, 2));
        END IF;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER supplier_contact_normalize_insert
    BEFORE INSERT ON supplier
    FOR EACH ROW
BEGIN
    DECLARE v_digits VARCHAR(20);

    IF NEW.email_address IS NOT NULL THEN
        SET NEW.email_address = NULLIF(LOWER(TRIM(NEW.email_address)), '');
    END IF;

    IF NEW.contact_number IS NOT NULL THEN
        SET NEW.contact_number = NULLIF(TRIM(NEW.contact_number), '');
        IF NEW.contact_number IS NOT NULL AND UPPER(NEW.contact_number) IN ('N/A', 'NA') THEN
            SET NEW.contact_number = NULL;
        END IF;
    END IF;

    IF NEW.contact_number IS NOT NULL THEN
        SET v_digits = NULLIF(REGEXP_REPLACE(NEW.contact_number, '[^0-9]', ''), '');
        IF v_digits REGEXP '^63[0-9]{10}$' THEN
            SET v_digits = CONCAT('0', SUBSTRING(v_digits, 3));
        END IF;

        IF v_digits REGEXP '^02[0-9]{8}$' THEN
            SET NEW.contact_number = v_digits;
        ELSEIF v_digits REGEXP '^09[0-9]{9}$' THEN
            SET NEW.contact_number = v_digits;
        ELSE
            SET NEW.contact_number = NULLIF(TRIM(NEW.contact_number), '');
        END IF;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER supplier_contact_normalize_update
    BEFORE UPDATE ON supplier
    FOR EACH ROW
BEGIN
    DECLARE v_digits VARCHAR(20);

    IF NEW.email_address IS NOT NULL THEN
        SET NEW.email_address = NULLIF(LOWER(TRIM(NEW.email_address)), '');
    END IF;

    IF NEW.contact_number IS NOT NULL THEN
        SET NEW.contact_number = NULLIF(TRIM(NEW.contact_number), '');
        IF NEW.contact_number IS NOT NULL AND UPPER(NEW.contact_number) IN ('N/A', 'NA') THEN
            SET NEW.contact_number = NULL;
        END IF;
    END IF;

    IF NEW.contact_number IS NOT NULL THEN
        SET v_digits = NULLIF(REGEXP_REPLACE(NEW.contact_number, '[^0-9]', ''), '');
        IF v_digits REGEXP '^63[0-9]{10}$' THEN
            SET v_digits = CONCAT('0', SUBSTRING(v_digits, 3));
        END IF;

        IF v_digits REGEXP '^02[0-9]{8}$' THEN
            SET NEW.contact_number = v_digits;
        ELSEIF v_digits REGEXP '^09[0-9]{9}$' THEN
            SET NEW.contact_number = v_digits;
        ELSE
            SET NEW.contact_number = NULLIF(TRIM(NEW.contact_number), '');
        END IF;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER employee_contact_normalize_insert
    BEFORE INSERT ON employee
    FOR EACH ROW
BEGIN
    SET NEW.email_address = LOWER(TRIM(NEW.email_address));

    IF NEW.contact_number IS NOT NULL THEN
        SET NEW.contact_number = NULLIF(TRIM(NEW.contact_number), '');
    END IF;

    IF NEW.contact_number IS NOT NULL THEN
        SET NEW.contact_number = NULLIF(REGEXP_REPLACE(NEW.contact_number, '[^0-9]', ''), '');
        IF NEW.contact_number REGEXP '^0[0-9]{10}$' THEN
            SET NEW.contact_number = CONCAT('63', SUBSTRING(NEW.contact_number, 2));
        END IF;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER employee_contact_normalize_update
    BEFORE UPDATE ON employee
    FOR EACH ROW
BEGIN
    SET NEW.email_address = LOWER(TRIM(NEW.email_address));

    IF NEW.contact_number IS NOT NULL THEN
        SET NEW.contact_number = NULLIF(TRIM(NEW.contact_number), '');
    END IF;

    IF NEW.contact_number IS NOT NULL THEN
        SET NEW.contact_number = NULLIF(REGEXP_REPLACE(NEW.contact_number, '[^0-9]', ''), '');
        IF NEW.contact_number REGEXP '^0[0-9]{10}$' THEN
            SET NEW.contact_number = CONCAT('63', SUBSTRING(NEW.contact_number, 2));
        END IF;
    END IF;
END //
DELIMITER ;


