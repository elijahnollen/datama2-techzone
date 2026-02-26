<?php

declare(strict_types=1);

if ($path === '/customer/profile' && $method === 'GET') {
        $claims = requireAuth($env, ['customer']);
        $customer = getCustomerByPublicId($pdo, (string) $claims['sub']);
        if ($customer === null) {
            sendJson(404, ['ok' => false, 'message' => 'Customer not found.']);
        }

        sendJson(200, [
            'ok' => true,
            'data' => [
                'profile' => [
                    'public_id' => (string) $customer['public_id'],
                    'first_name' => (string) ($customer['first_name'] ?? ''),
                    'last_name' => (string) ($customer['last_name'] ?? ''),
                    'name' => trim(((string) $customer['first_name']) . ' ' . ((string) $customer['last_name'])),
                    'email' => (string) ($customer['email_address'] ?? ''),
                    'phone' => (string) ($customer['contact_number'] ?? ''),
                    'balance' => (float) ($customer['current_credit'] ?? 0),
                    'address' => [
                        'street' => (string) ($customer['street_address'] ?? ''),
                        'barangay' => (string) ($customer['barangay'] ?? ''),
                        'city' => (string) ($customer['city_municipality'] ?? ''),
                        'province' => (string) ($customer['province'] ?? ''),
                        'zip' => (string) ($customer['zip_code'] ?? ''),
                    ],
                ],
            ],
        ]);
    }

    if ($path === '/customer/profile' && $method === 'PUT') {
        $claims = requireAuth($env, ['customer']);
        $body = requestBody($env);
        $firstName = asString($body['first_name'] ?? '');
        $lastName = asString($body['last_name'] ?? '');
        $email = strtolower(asString($body['email'] ?? $body['email_address'] ?? ''));
        $phone = asString($body['phone'] ?? '');
        $street = asString($body['street_address'] ?? '');
        $barangay = asString($body['barangay'] ?? '');
        $city = asString($body['city_municipality'] ?? '');
        $province = asString($body['province'] ?? '');
        $zip = asString($body['zip_code'] ?? '');
        $phoneNormalized = normalizePhoneNumber($phone);

        $errors = [];
        $firstNameError = validateHumanName($firstName, 'First name');
        if ($firstNameError !== null) {
            $errors['first_name'] = $firstNameError;
        }
        $lastNameError = validateHumanName($lastName, 'Last name');
        if ($lastNameError !== null) {
            $errors['last_name'] = $lastNameError;
        }
        if ($email !== '' && !validateEmail($email)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if ($phone !== '' && $phoneNormalized === null) {
            $errors['phone'] = 'Please enter a valid phone number (example: 09171234567 or 639171234567).';
        }
        if ($email === '' && $phoneNormalized === null) {
            $errors['contact'] = 'Please provide at least an email address or a contact number.';
        }
        if (!validateZipCode($zip)) {
            $errors['zip_code'] = 'Please enter a valid 4-digit zip code.';
        }
        if ($street !== '') {
            $streetError = validateTextLength($street, 'Street address', 3, 100);
            if ($streetError !== null) {
                $errors['street_address'] = $streetError;
            }
        }
        if ($barangay === '') {
            $errors['barangay'] = 'Barangay is required.';
        } else {
            $barangayError = validateTextLength($barangay, 'Barangay', 2, 50);
            if ($barangayError !== null) {
                $errors['barangay'] = $barangayError;
            }
        }
        if ($city === '') {
            $errors['city_municipality'] = 'City/Municipality is required.';
        } else {
            $cityError = validateTextLength($city, 'City/Municipality', 2, 50);
            if ($cityError !== null) {
                $errors['city_municipality'] = $cityError;
            }
        }
        if ($province !== '') {
            $provinceError = validateTextLength($province, 'Province', 2, 50);
            if ($provinceError !== null) {
                $errors['province'] = $provinceError;
            }
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        if ($email !== '') {
            $existingByEmail = getCustomerByEmail($pdo, $email);
            if (
                $existingByEmail !== null &&
                strcasecmp((string) ($existingByEmail['public_id'] ?? ''), (string) $claims['sub']) !== 0
            ) {
                validationFail([
                    'email' => 'This email is already registered to another account.',
                    'merge_account' => 'This email is linked to an existing account. Please log in to continue, or reach out to us directly for assistance!',
                ]);
            }
        }

        if ($phoneNormalized !== null) {
            $existingByContact = getCustomerByContact($pdo, $phoneNormalized);
            if (
                $existingByContact !== null &&
                strcasecmp((string) ($existingByContact['public_id'] ?? ''), (string) $claims['sub']) !== 0
            ) {
                validationFail([
                    'contact_number' => 'This contact number is already registered to another account.',
                    'merge_account' => 'This phone number is linked to an existing account. Please log in to continue, or reach out to us directly for assistance!',
                ]);
            }
        }

        $stmt = $pdo->prepare(
            'CALL update_customer_master(
                :public_id, :first_name, :last_name, :middle_name, :email_address,
                :contact_number, :street_address, :barangay, :city_municipality,
                :province, :zip_code, :status, :deleted_at
            )'
        );
        $stmt->execute([
            'public_id' => (string) $claims['sub'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => null,
            'email_address' => $email,
            'contact_number' => $phoneNormalized,
            'street_address' => $street !== '' ? $street : null,
            'barangay' => $barangay,
            'city_municipality' => $city,
            'province' => $province !== '' ? $province : null,
            'zip_code' => $zip !== '' ? $zip : null,
            'status' => null,
            'deleted_at' => null,
        ]);
        $stmt->closeCursor();

        appendAuditLog($env, 'USER_ACCOUNT_LOG', 'CUSTOMER_PROFILE_UPDATE', [
            'actor_type' => 'CUSTOMER',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'CustomerProfile',
            'resource_public_id' => (string) $claims['sub'],
        ]);

        sendJson(200, ['ok' => true, 'message' => 'Profile updated successfully.']);
    }

    if ($path === '/customer/password' && $method === 'PUT') {
        $claims = requireAuth($env, ['customer']);
        $body = requestBody($env);

        $currentPassword = (string) ($body['current_password'] ?? '');
        $newPassword = (string) ($body['new_password'] ?? '');
        $confirmPassword = (string) ($body['confirm_password'] ?? '');

        $errors = [];
        if ($currentPassword === '') {
            $errors['current_password'] = 'Current password is required.';
        }
        $newPasswordError = validatePassword($newPassword);
        if ($newPasswordError !== null) {
            $errors['new_password'] = $newPasswordError;
        }
        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Please confirm your new password.';
        } elseif ($confirmPassword !== $newPassword) {
            $errors['confirm_password'] = 'New password and confirmation do not match.';
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        $customerStmt = $pdo->prepare(
            'SELECT customerID, password_hash, status, deleted_at
             FROM customer
             WHERE public_id = :public_id
             LIMIT 1'
        );
        $customerStmt->execute(['public_id' => (string) $claims['sub']]);
        $customerRow = $customerStmt->fetch();
        if (!is_array($customerRow)) {
            sendJson(404, ['ok' => false, 'message' => 'Customer not found.']);
        }

        $status = asString($customerRow['status'] ?? 'Active');
        $deletedAt = asString($customerRow['deleted_at'] ?? '');
        if ($status !== 'Active' || $deletedAt !== '') {
            sendJson(403, ['ok' => false, 'message' => 'Your account is inactive.']);
        }

        $existingPasswordHash = asString($customerRow['password_hash'] ?? '');
        if ($existingPasswordHash === '' || !password_verify($currentPassword, $existingPasswordHash)) {
            validationFail(['current_password' => 'Current password is incorrect.']);
        }

        if (password_verify($newPassword, $existingPasswordHash)) {
            validationFail(['new_password' => 'New password must be different from your current password.']);
        }

        $updatePasswordStmt = $pdo->prepare(
            'CALL customer_password_update(:public_id, :password_hash)'
        );
        $updatePasswordStmt->execute([
            'public_id' => (string) $claims['sub'],
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
        $updatePasswordStmt->closeCursor();

        appendAuditLog($env, 'USER_ACCOUNT_LOG', 'CUSTOMER_PASSWORD_UPDATE', [
            'actor_type' => 'CUSTOMER',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'CustomerProfile',
            'resource_public_id' => (string) $claims['sub'],
        ]);

        sendJson(200, ['ok' => true, 'message' => 'Password updated successfully.']);
    }

    if ($path === '/customer/account/deactivate' && $method === 'POST') {
        $claims = requireAuth($env, ['customer']);
        $customerPublicId = (string) $claims['sub'];
        $customer = getCustomerByPublicIdAny($pdo, $customerPublicId);
        if ($customer === null) {
            sendJson(404, ['ok' => false, 'message' => 'Customer not found.']);
        }

        $currentStatus = asString($customer['status'] ?? 'Active');
        $currentDeletedAt = asString($customer['deleted_at'] ?? '');
        if ($currentStatus === 'Deleted_by_User' && $currentDeletedAt !== '') {
            sendJson(200, [
                'ok' => true,
                'message' => 'Account is already deactivated. You can recover it by logging in within 7 days.',
            ]);
        }

        $stmt = $pdo->prepare(
            'CALL update_customer_master(
                :public_id, :first_name, :last_name, :middle_name, :email_address,
                :contact_number, :street_address, :barangay, :city_municipality,
                :province, :zip_code, :status, :deleted_at
            )'
        );
        $stmt->execute([
            'public_id' => $customerPublicId,
            'first_name' => null,
            'last_name' => null,
            'middle_name' => null,
            'email_address' => null,
            'contact_number' => null,
            'street_address' => null,
            'barangay' => null,
            'city_municipality' => null,
            'province' => null,
            'zip_code' => null,
            'status' => 'Deleted_by_User',
            'deleted_at' => nowUtc(),
        ]);
        $stmt->closeCursor();

        appendAuditLog($env, 'USER_ACCOUNT_LOG', 'CUSTOMER_SELF_DEACTIVATE', [
            'actor_type' => 'CUSTOMER',
            'public_id' => $customerPublicId,
        ], [
            'resource_type' => 'CustomerProfile',
            'resource_public_id' => $customerPublicId,
        ], [
            'previous_state' => $currentStatus,
            'new_state' => 'Deleted_by_User',
            'recovery_window_days' => 7,
        ]);

        sendJson(200, [
            'ok' => true,
            'message' => 'Account deactivated. You can recover it by logging in within 7 days.',
        ]);
    }

    if ($path === '/customer/inquiries' && $method === 'POST') {
        $claims = requireAuth($env, ['customer']);
        $manager = mongoManager($env);
        if (!$manager) {
            sendJson(500, ['ok' => false, 'message' => 'Inquiry service is unavailable.']);
        }

        $customer = getCustomerByPublicId($pdo, (string) $claims['sub']);
        if ($customer === null) {
            sendJson(404, ['ok' => false, 'message' => 'Customer not found.']);
        }

        $body = requestBody($env);
        $customerName = asString($body['customer_name'] ?? $body['name'] ?? trim(((string) ($customer['first_name'] ?? '')) . ' ' . ((string) ($customer['last_name'] ?? ''))));
        $customerEmail = strtolower(asString($body['customer_email'] ?? $body['email'] ?? ($customer['email_address'] ?? '')));
        $contactRaw = asString($body['contact_number'] ?? $body['phone'] ?? ($customer['contact_number'] ?? ''));
        $contactNumber = normalizePhoneNumber($contactRaw);
        $subject = asString($body['subject'] ?? '');
        $message = asString($body['message'] ?? '');

        $errors = [];
        if ($customerName === '') {
            $errors['customer_name'] = 'Please provide your name.';
        } else {
            $nameError = validateTextLength($customerName, 'Name', 2, 100);
            if ($nameError !== null) {
                $errors['customer_name'] = $nameError;
            }
        }
        if ($customerEmail === '' || !validateEmail($customerEmail)) {
            $errors['customer_email'] = 'Please provide a valid email address.';
        }
        if ($contactRaw !== '' && $contactNumber === null) {
            $errors['contact_number'] = 'Please provide a valid contact number (example: 09171234567 or 639171234567).';
        }
        if ($subject === '') {
            $errors['subject'] = 'Please provide a subject line.';
        } else {
            $subjectError = validateTextLength($subject, 'Subject', 3, 150);
            if ($subjectError !== null) {
                $errors['subject'] = $subjectError;
            }
        }
        if ($message === '') {
            $errors['message'] = 'Please provide your message.';
        } else {
            $messageError = validateTextLength($message, 'Message', 5, 2000);
            if ($messageError !== null) {
                $errors['message'] = $messageError;
            }
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        $timestamp = mongoTimestamp();
        $document = [
            'customer_id' => (string) $claims['sub'],
            'customer_public_id' => (string) $claims['sub'],
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'contact_number' => $contactNumber ?? '',
            'subject' => $subject,
            'status' => 'Pending',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'last_message_at' => $timestamp,
            'message_count' => 1,
        ];

        try {
            mongoInsertOne($manager, mongoDbName($env), mongoCollectionName($env, 'customer_inquiry'), $document);
        } catch (Throwable) {
            sendJson(500, ['ok' => false, 'message' => 'Unable to save your inquiry right now.']);
        }

        appendAuditLog($env, 'CUSTOMER_INQUIRY_LOG', 'CUSTOMER_INQUIRY_SUBMITTED', [
            'actor_type' => 'CUSTOMER',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'CustomerInquiry',
            'resource_public_id' => (string) $claims['sub'],
        ]);

        sendJson(201, ['ok' => true, 'message' => 'Inquiry submitted successfully.']);
    }

    if (str_starts_with($path, '/customer/cart')) {
        $claims = requireAuth($env, ['customer']);
        $manager = mongoManager($env);
        if (!$manager) {
            sendJson(500, ['ok' => false, 'message' => 'Cart service is unavailable.']);
        }
        $db = mongoDbName($env);
        $cartCollection = mongoCollectionName($env, 'shopping_cart');
        $customerPublicId = strtoupper((string) $claims['sub']);

        if ($path === '/customer/cart' && $method === 'GET') {
            $cartFromDb = mongoFindOne($manager, $db, $cartCollection, ['customer_public_id' => $customerPublicId]);
            $cart = [
                'customer_public_id' => $customerPublicId,
                'items' => [],
                'cart_summary' => ['total_items' => 0, 'subtotal_price' => 0.0],
                'last_updated' => nowUtc(),
            ];
            if (is_array($cartFromDb)) {
                $catalogMap = catalogMapByPublicId(loadProductCatalog($env));
                $rawItems = is_array($cartFromDb['items'] ?? null) ? $cartFromDb['items'] : [];
                $syncedItems = [];
                foreach ($rawItems as $rawItem) {
                    if (!is_array($rawItem)) {
                        continue;
                    }
                    $productPublicId = strtoupper(asString($rawItem['product_public_id'] ?? ''));
                    if ($productPublicId === '' || !isValidPublicId($productPublicId)) {
                        continue;
                    }

                    $catalogItem = $catalogMap[$productPublicId] ?? null;
                    $mysqlProduct = mysqlProductByPublicId($pdo, $productPublicId);
                    if ($mysqlProduct === null) {
                        $fallbackName = asString($rawItem['product_name'] ?? (is_array($catalogItem) ? asString($catalogItem['model_name'] ?? '') : ''));
                        if ($fallbackName !== '') {
                            $mysqlProduct = mysqlProductByName($pdo, $fallbackName);
                        }
                    }

                    $resolvedPrice = 0.0;
                    if (is_array($mysqlProduct)) {
                        $resolvedPrice = (float) ($mysqlProduct['selling_price'] ?? 0);
                    } elseif (is_array($catalogItem)) {
                        $resolvedPrice = (float) ($catalogItem['display_price'] ?? 0);
                    } else {
                        $resolvedPrice = (float) ($rawItem['price_at_addition'] ?? 0);
                    }
                    if ($resolvedPrice < 0) {
                        $resolvedPrice = 0.0;
                    }

                    $resolvedName = asString($rawItem['product_name'] ?? '');
                    if ($resolvedName === '') {
                        if (is_array($mysqlProduct)) {
                            $resolvedName = asString($mysqlProduct['product_name'] ?? '');
                        } elseif (is_array($catalogItem)) {
                            $resolvedName = asString($catalogItem['model_name'] ?? '');
                        }
                    }

                    $resolvedImage = asString($rawItem['image_url'] ?? '');
                    if ($resolvedImage === '' && is_array($catalogItem)) {
                        $resolvedImage = asString($catalogItem['image_url'] ?? '');
                    }

                    $rawItem['product_public_id'] = $productPublicId;
                    $rawItem['product_name'] = $resolvedName;
                    $rawItem['price_at_addition'] = $resolvedPrice;
                    $rawItem['image_url'] = $resolvedImage;
                    $syncedItems[] = $rawItem;
                }

                $cart = upsertCustomerCartDocument(
                    $env,
                    $manager,
                    $customerPublicId,
                    $syncedItems
                );
            }
            sendJson(200, ['ok' => true, 'data' => ['cart' => $cart]]);
        }

        if ($path === '/customer/cart/merge' && $method === 'POST') {
            $body = requestBody($env);
            $rawItems = is_array($body['items'] ?? null) ? $body['items'] : [];
            if ($rawItems === []) {
                sendJson(200, [
                    'ok' => true,
                    'message' => 'No guest cart items to merge.',
                    'data' => [
                        'cart' => [
                            'customer_public_id' => $customerPublicId,
                            'items' => [],
                            'cart_summary' => ['total_items' => 0, 'subtotal_price' => 0],
                            'last_updated' => nowUtc(),
                        ],
                    ],
                ]);
            }

            $incomingItems = [];
            foreach ($rawItems as $rawItem) {
                if (!is_array($rawItem)) {
                    continue;
                }
                $productPublicId = strtoupper(asString($rawItem['product_public_id'] ?? ''));
                $quantity = (int) ($rawItem['quantity'] ?? 0);
                if ($productPublicId === '' || !isValidPublicId($productPublicId)) {
                    continue;
                }
                if ($quantity < 1) {
                    continue;
                }
                $incomingItems[] = [
                    'product_public_id' => $productPublicId,
                    'quantity' => min(50, $quantity),
                    'product_name' => asString($rawItem['product_name'] ?? ''),
                    'price_at_addition' => (float) ($rawItem['price_at_addition'] ?? 0),
                    'image_url' => asString($rawItem['image_url'] ?? ''),
                ];
            }

            if ($incomingItems === []) {
                sendJson(200, [
                    'ok' => true,
                    'message' => 'No valid guest cart items to merge.',
                    'data' => [
                        'cart' => [
                            'customer_public_id' => $customerPublicId,
                            'items' => [],
                            'cart_summary' => ['total_items' => 0, 'subtotal_price' => 0],
                            'last_updated' => nowUtc(),
                        ],
                    ],
                ]);
            }

            try {
                $pdo->beginTransaction();
                $lockedCustomer = lockActiveCustomerRowByPublicId($pdo, $customerPublicId);
                if (!is_array($lockedCustomer)) {
                    throw new RuntimeException('CUSTOMER_LOCK_UNAVAILABLE');
                }

                $mergedCart = mergeCustomerCartItems($env, $pdo, $customerPublicId, $incomingItems);
                $pdo->commit();
            } catch (Throwable $e) {
                rollbackIfInTransaction($pdo);
                if ($e->getMessage() === 'CUSTOMER_LOCK_UNAVAILABLE') {
                    sendJson(401, ['ok' => false, 'message' => 'Session expired. Please log in again.']);
                }
                sendJson(500, ['ok' => false, 'message' => 'Unable to merge guest cart items right now.']);
            }

            sendJson(200, [
                'ok' => true,
                'message' => 'Guest cart merged successfully.',
                'data' => ['cart' => $mergedCart],
            ]);
        }

        if ($path === '/customer/cart/items' && $method === 'POST') {
            $body = requestBody($env);
            $productPublicId = strtoupper(asString($body['product_public_id'] ?? ''));
            $quantity = (int) ($body['quantity'] ?? 1);
            if ($quantity < 1) {
                validationFail(['quantity' => 'Quantity must be at least 1.']);
            }
            if ($quantity > 50) {
                validationFail(['quantity' => 'Maximum quantity per add-to-cart request is 50.']);
            }

            if ($productPublicId === '') {
                validationFail(['product_public_id' => 'Please select a product.']);
            }
            if (!isValidPublicId($productPublicId)) {
                validationFail(['product_public_id' => 'Invalid product reference format.']);
            }

            $catalog = loadProductCatalog($env);
            $catalogMap = catalogMapByPublicId($catalog);
            $product = $catalogMap[$productPublicId] ?? null;
            if ($product === null) {
                sendJson(404, ['ok' => false, 'message' => 'Product not found in catalog.']);
            }
            if (strtoupper(asString($product['availability_status'] ?? 'AVAILABLE')) !== 'AVAILABLE') {
                validationFail(['product_public_id' => 'This product is currently unavailable.']);
            }

            $mysqlProduct = mysqlProductByPublicId($pdo, $productPublicId);
            if ($mysqlProduct !== null) {
                if ((int) ($mysqlProduct['is_active'] ?? 0) !== 1) {
                    validationFail(['product_public_id' => 'This product is inactive and cannot be purchased.']);
                }
                if ((int) ($mysqlProduct['quantity'] ?? 0) <= 0) {
                    validationFail(['product_public_id' => 'This product is out of stock.']);
                }
            }

            try {
                $pdo->beginTransaction();
                $lockedCustomer = lockActiveCustomerRowByPublicId($pdo, $customerPublicId);
                if (!is_array($lockedCustomer)) {
                    throw new RuntimeException('CUSTOMER_LOCK_UNAVAILABLE');
                }

                $existing = mongoFindOne($manager, $db, $cartCollection, ['customer_public_id' => $customerPublicId]);
                $items = normalizeCustomerCartItems(is_array($existing['items'] ?? null) ? $existing['items'] : []);

                $found = false;
                foreach ($items as &$item) {
                    if (strtoupper(asString($item['product_public_id'] ?? '')) === $productPublicId) {
                        $item['quantity'] = (int) ($item['quantity'] ?? 0) + $quantity;
                        $found = true;
                        break;
                    }
                }
                unset($item);

                if (!$found) {
                    $items[] = [
                        'product_public_id' => $productPublicId,
                        'product_name' => asString($product['model_name'] ?? ''),
                        'quantity' => $quantity,
                        'price_at_addition' => (float) ($product['display_price'] ?? 0),
                        'image_url' => asString($product['image_url'] ?? ''),
                        'added_at' => nowUtc(),
                    ];
                }

                upsertCustomerCartDocument($env, $manager, $customerPublicId, $items);
                $pdo->commit();
            } catch (Throwable $e) {
                rollbackIfInTransaction($pdo);
                if ($e->getMessage() === 'CUSTOMER_LOCK_UNAVAILABLE') {
                    sendJson(401, ['ok' => false, 'message' => 'Session expired. Please log in again.']);
                }
                sendJson(500, ['ok' => false, 'message' => 'Unable to update cart right now.']);
            }

            sendJson(200, ['ok' => true, 'message' => 'Cart updated successfully.']);
        }

        if (preg_match('#^/customer/cart/items/([A-Za-z0-9\-]+)$#', $path, $matches) === 1) {
            $productPublicId = strtoupper($matches[1]);
            if (!isValidPublicId($productPublicId)) {
                validationFail(['product_public_id' => 'Invalid product reference format.']);
            }
            $updateQuantity = null;
            if ($method === 'PUT') {
                $body = requestBody($env);
                $updateQuantity = (int) ($body['quantity'] ?? 1);
                if ($updateQuantity <= 0) {
                    validationFail(['quantity' => 'Quantity must be at least 1.']);
                }
                if ($updateQuantity > 50) {
                    validationFail(['quantity' => 'Quantity cannot exceed 50 for a single cart line.']);
                }
            } elseif ($method !== 'DELETE') {
                endpointNotFound();
            }

            try {
                $pdo->beginTransaction();
                $lockedCustomer = lockActiveCustomerRowByPublicId($pdo, $customerPublicId);
                if (!is_array($lockedCustomer)) {
                    throw new RuntimeException('CUSTOMER_LOCK_UNAVAILABLE');
                }

                $existing = mongoFindOne($manager, $db, $cartCollection, ['customer_public_id' => $customerPublicId]) ?? ['items' => []];
                $items = normalizeCustomerCartItems(is_array($existing['items'] ?? null) ? $existing['items'] : []);

                if ($method === 'PUT') {
                    $foundItem = false;
                    foreach ($items as &$item) {
                        if (strtoupper(asString($item['product_public_id'] ?? '')) === $productPublicId) {
                            $item['quantity'] = (int) $updateQuantity;
                            $foundItem = true;
                        }
                    }
                    unset($item);
                    if (!$foundItem) {
                        throw new OutOfBoundsException('CART_ITEM_NOT_FOUND');
                    }
                } else {
                    $before = count($items);
                    $items = array_values(array_filter($items, static fn($item) => strtoupper(asString($item['product_public_id'] ?? '')) !== $productPublicId));
                    if (count($items) === $before) {
                        throw new OutOfBoundsException('CART_ITEM_NOT_FOUND');
                    }
                }

                upsertCustomerCartDocument($env, $manager, $customerPublicId, $items);
                $pdo->commit();
            } catch (OutOfBoundsException) {
                rollbackIfInTransaction($pdo);
                sendJson(404, ['ok' => false, 'message' => 'Cart item not found.']);
            } catch (Throwable $e) {
                rollbackIfInTransaction($pdo);
                if ($e->getMessage() === 'CUSTOMER_LOCK_UNAVAILABLE') {
                    sendJson(401, ['ok' => false, 'message' => 'Session expired. Please log in again.']);
                }
                sendJson(500, ['ok' => false, 'message' => 'Unable to update cart right now.']);
            }

            sendJson(200, ['ok' => true, 'message' => 'Cart updated successfully.']);
        }
    }

    if ($path === '/customer/checkout' && $method === 'POST') {
        $claims = requireAuth($env, ['customer']);
        $manager = mongoManager($env);
        if (!$manager) {
            sendJson(500, ['ok' => false, 'message' => 'Checkout service is unavailable.']);
        }
        $dbName = mongoDbName($env);
        $cartCollection = mongoCollectionName($env, 'shopping_cart');

        $body = requestBody($env);
        $selected = is_array($body['selected_product_public_ids'] ?? null) ? $body['selected_product_public_ids'] : [];
        $fulfillment = asString($body['fulfillment_method'] ?? 'Delivery');
        if (!in_array($fulfillment, ['Delivery', 'Pickup'], true)) {
            $fulfillment = 'Delivery';
        }
        $paymentMethod = asString($body['payment_method'] ?? 'Cash');
        if (!ensureAllowedValue($paymentMethod, ['Cash', 'GCash', 'Card', 'Store Credit'])) {
            $paymentMethod = 'Cash';
        }
        $paymentResolution = strtolower(asString($body['payment_resolution'] ?? ''));
        if (!in_array($paymentResolution, ['completed', 'pending', 'failed'], true)) {
            if ($paymentMethod === 'Cash') {
                $paymentResolution = 'pending';
            } elseif ($paymentMethod === 'Store Credit') {
                $paymentResolution = 'completed';
            } else {
                $paymentResolution = 'completed';
            }
        }

        $selectedProductIds = [];
        foreach ($selected as $selectedValue) {
            $id = asString($selectedValue);
            if ($id === '') {
                continue;
            }
            if (!isValidPublicId($id)) {
                validationFail(['selected_product_public_ids' => 'One or more selected products have an invalid format.']);
            }
            $selectedProductIds[] = strtoupper($id);
        }

        $customer = getCustomerByPublicId($pdo, (string) $claims['sub']);
        if ($customer === null) {
            sendJson(404, ['ok' => false, 'message' => 'Customer account not found.']);
        }

        $shippingName = asString($body['shipping_name'] ?? trim(((string) ($customer['first_name'] ?? '')) . ' ' . ((string) ($customer['last_name'] ?? ''))));
        $shippingEmail = strtolower(asString($body['shipping_email'] ?? $body['email'] ?? ($customer['email_address'] ?? '')));
        $shippingContactRaw = asString($body['shipping_contact_number'] ?? $body['contact_number'] ?? ($customer['contact_number'] ?? ''));
        $shippingContactNormalized = normalizePhoneNumber($shippingContactRaw);
        $shippingStreet = asString($body['shipping_street'] ?? ($customer['street_address'] ?? ''));
        $shippingBarangay = asString($body['shipping_barangay'] ?? ($customer['barangay'] ?? ''));
        $shippingCityMunicipality = asString($body['shipping_city_municipality'] ?? ($customer['city_municipality'] ?? ''));
        $shippingProvince = asString($body['shipping_province'] ?? ($customer['province'] ?? ''));
        $shippingZipCode = asString($body['shipping_zip_code'] ?? ($customer['zip_code'] ?? ''));

        $setDefaultEmail = parseBoolean($body['set_default_email'] ?? null) === true;
        $setDefaultContactNumber = parseBoolean($body['set_default_contact_number'] ?? null) === true;
        $mergeOtpCode = asString($body['merge_otp_code'] ?? '');
        $mergeOtpIssuedAt = (int) ($body['merge_otp_issued_at'] ?? 0);
        $pendingMergeWalkInCustomer = null;
        $mustPersistDefaults = $setDefaultEmail || $setDefaultContactNumber;

        $checkoutErrors = [];
        if ($shippingName === '') {
            $checkoutErrors['shipping_name'] = 'Please provide the recipient full name.';
        }
        if ($shippingEmail === '' || !validateEmail($shippingEmail)) {
            $checkoutErrors['email'] = 'Please provide an email address.';
        }
        if ($shippingContactRaw === '' || $shippingContactNormalized === null) {
            $checkoutErrors['contact_number'] = 'Please provide a valid contact number (example: 09171234567 or 639171234567).';
        }
        if ($fulfillment === 'Delivery') {
            if (trim($shippingStreet) === '') {
                $checkoutErrors['shipping_street'] = 'Please provide a street address for delivery.';
            }
            if (trim($shippingBarangay) === '') {
                $checkoutErrors['shipping_barangay'] = 'Please provide a barangay for delivery.';
            }
            if (trim($shippingCityMunicipality) === '') {
                $checkoutErrors['shipping_city_municipality'] = 'Please provide a city/municipality for delivery.';
            }
            if (trim($shippingProvince) === '') {
                $checkoutErrors['shipping_province'] = 'Please provide a province for delivery.';
            }
            if (trim($shippingZipCode) === '' || !validateZipCode($shippingZipCode)) {
                $checkoutErrors['shipping_zip_code'] = 'Please provide a valid 4-digit zip code for delivery.';
            }
        }
        if ($setDefaultEmail && ($shippingEmail === '' || !validateEmail($shippingEmail))) {
            $checkoutErrors['email'] = 'Please provide a valid email address to save as default.';
        }
        if ($setDefaultContactNumber && $shippingContactNormalized === null) {
            $checkoutErrors['contact_number'] = 'Please provide a valid contact number to save as default (example: 09171234567 or 639171234567).';
        }
        if ($checkoutErrors !== []) {
            validationFail($checkoutErrors);
        }

        $currentCustomerId = (int) ($customer['customerID'] ?? 0);
        $duplicateRegisteredCustomer = null;
        $duplicateWalkInCustomer = null;
        $duplicateConflictField = '';

        if ($setDefaultEmail) {
            $existingByEmail = getCustomerByEmail($pdo, $shippingEmail);
            if (
                $existingByEmail !== null &&
                (int) ($existingByEmail['customerID'] ?? 0) !== $currentCustomerId
            ) {
                if (asString($existingByEmail['customer_type'] ?? '') === 'Registered') {
                    $duplicateRegisteredCustomer = $existingByEmail;
                    $duplicateConflictField = 'email';
                } elseif (asString($existingByEmail['customer_type'] ?? '') === 'Walk-in') {
                    $duplicateWalkInCustomer = $existingByEmail;
                }
            }
        }

        if ($setDefaultContactNumber) {
            $existingByContact = getCustomerByContact($pdo, (string) $shippingContactNormalized);
            if (
                $existingByContact !== null &&
                (int) ($existingByContact['customerID'] ?? 0) !== $currentCustomerId
            ) {
                if (asString($existingByContact['customer_type'] ?? '') === 'Registered') {
                    $duplicateRegisteredCustomer = $existingByContact;
                    if ($duplicateConflictField === '') {
                        $duplicateConflictField = 'phone number';
                    }
                } elseif (asString($existingByContact['customer_type'] ?? '') === 'Walk-in') {
                    if (
                        $duplicateWalkInCustomer !== null &&
                        (int) ($duplicateWalkInCustomer['customerID'] ?? 0) !== (int) ($existingByContact['customerID'] ?? 0)
                    ) {
                        validationFail([
                            'contact_number' => 'Email and contact number map to different walk-in customer records.',
                            'merge_account' => 'Please contact support so we can merge the correct account.',
                        ]);
                    }
                    $duplicateWalkInCustomer = $existingByContact;
                }
            }
        }

        if ($duplicateRegisteredCustomer !== null) {
            $contactTypeLabel = $duplicateConflictField === 'phone number' ? 'phone number' : 'email';
            $contactConflictMessage = sprintf(
                'This %s is linked to an existing account. Please log in to continue, or reach out to us directly for assistance!',
                $contactTypeLabel
            );
            validationFail([
                'contact_conflict' => $contactConflictMessage,
                'merge_account' => $contactConflictMessage,
            ]);
        }

        if ($duplicateWalkInCustomer !== null) {
            $isOtpValid = verifyDemoOtp($env, $mergeOtpCode, $mergeOtpIssuedAt > 0 ? $mergeOtpIssuedAt : null);
            if (!$isOtpValid) {
                $otpErrorMessage = $mergeOtpCode === ''
                    ? 'Please enter the OTP to merge this walk-in account.'
                    : 'OTP is invalid or expired.';
                validationFail([
                    'otp_required' => 'OTP verification is required to merge your walk-in customer record.',
                    'otp_code' => $otpErrorMessage,
                    'otp_window_seconds' => (string) configuredOtpWindowSeconds($env),
                ]);
            }
            $pendingMergeWalkInCustomer = $duplicateWalkInCustomer;
        }

        $cart = mongoFindOne($manager, $dbName, $cartCollection, ['customer_public_id' => (string) $claims['sub']]);
        $currentCartItems = normalizeCustomerCartItems(is_array($cart['items'] ?? null) ? $cart['items'] : []);
        $cart = [
            'customer_public_id' => (string) $claims['sub'],
            'items' => $currentCartItems,
        ];
        $cartItems = $currentCartItems;

        if ($selectedProductIds !== []) {
            $selectedMap = array_fill_keys($selectedProductIds, true);
            $cartItems = array_values(array_filter($cartItems, static fn($it) => isset($selectedMap[strtoupper((string) ($it['product_public_id'] ?? ''))])));
        }

        if ($cartItems === []) {
            validationFail(['checkout' => 'Your cart is empty. Select at least one item to checkout.']);
        }

        $catalogMap = catalogMapByPublicId(loadProductCatalog($env));
        $saleItems = [];
        foreach ($cartItems as $item) {
            $publicId = strtoupper(asString($item['product_public_id'] ?? ''));
            $quantity = (int) ($item['quantity'] ?? 1);
            if ($publicId === '' || !isValidPublicId($publicId)) {
                validationFail(['checkout' => 'A cart item has an invalid product reference.']);
            }
            if ($quantity < 1 || $quantity > 50) {
                validationFail(['checkout' => 'Cart quantity must be between 1 and 50 for each item.']);
            }

            $catalogItem = $catalogMap[$publicId] ?? [
                'product_public_id' => $publicId,
                'model_name' => asString($item['product_name'] ?? 'Unknown Product'),
                'display_price' => (float) ($item['price_at_addition'] ?? 0),
                'is_in_stock' => true,
            ];

            $mysqlProduct = ensureMysqlProduct($pdo, $catalogItem);
            if ((int) ($mysqlProduct['is_active'] ?? 0) !== 1) {
                validationFail(['stock' => 'One or more selected products are inactive.']);
            }

            $price = (float) ($mysqlProduct['selling_price'] ?? $catalogItem['display_price'] ?? 0);
            $saleItems[] = [
                'product' => $mysqlProduct,
                'product_id' => (int) ($mysqlProduct['productID'] ?? 0),
                'product_public_id' => asString($mysqlProduct['public_id'] ?? $publicId),
                'product_name' => asString($mysqlProduct['product_name'] ?? $catalogItem['model_name'] ?? $item['product_name'] ?? 'a selected product'),
                'quantity' => $quantity,
                'price' => $price,
            ];
        }

        $subtotal = 0.0;
        foreach ($saleItems as $saleItem) {
            $subtotal += ((float) ($saleItem['price'] ?? 0)) * ((int) ($saleItem['quantity'] ?? 0));
        }

        try {
            $pdo->beginTransaction();
            $lockingItems = $saleItems;
            usort($lockingItems, static function (array $left, array $right): int {
                return ((int) ($left['product_id'] ?? 0)) <=> ((int) ($right['product_id'] ?? 0));
            });
            foreach ($lockingItems as $lockingItem) {
                $productId = (int) ($lockingItem['product_id'] ?? 0);
                $requestedQty = (int) ($lockingItem['quantity'] ?? 0);
                $lockedProduct = lockProductRowById($pdo, $productId);
                if (!is_array($lockedProduct)) {
                    rollbackIfInTransaction($pdo);
                    validationFail(['stock' => 'One or more selected products are no longer available.']);
                }
                if ((int) ($lockedProduct['is_active'] ?? 0) !== 1) {
                    rollbackIfInTransaction($pdo);
                    validationFail(['stock' => 'One or more selected products are inactive.']);
                }
                if ((int) ($lockedProduct['quantity'] ?? 0) < $requestedQty) {
                    rollbackIfInTransaction($pdo);
                    validationFail([
                        'stock' => 'Insufficient stock for ' . asString($lockingItem['product_name'] ?? 'a selected product') . '.',
                    ]);
                }
            }

            $shippingFee = 0.0;
            $grandTotal = $subtotal + $shippingFee;
            if ($paymentMethod === 'Store Credit') {
                $availableCredit = (float) ($customer['current_credit'] ?? 0);
                if ($availableCredit + 0.0001 < $grandTotal) {
                    rollbackIfInTransaction($pdo);
                    validationFail([
                        'payment_method' => 'Insufficient amount in store credit. Please choose another payment method.',
                    ]);
                }
            }

            $salePublicId = randomPublicId('SL');
            $employeeId = firstEmployeeId($pdo);
            if ($employeeId === null) {
                rollbackIfInTransaction($pdo);
                sendJson(500, [
                    'ok' => false,
                    'message' => 'Order workflow setup is incomplete.',
                    'errors' => ['checkout' => 'No employee record is available to process checkout.'],
                ]);
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
                'customerID' => (int) $customer['customerID'],
                'employeeID' => $employeeId,
                'fulfillment_method' => $fulfillment,
                'shipping_name' => $shippingName !== '' ? $shippingName : null,
                'shipping_street' => $shippingStreet !== '' ? $shippingStreet : null,
                'shipping_barangay' => $shippingBarangay !== '' ? $shippingBarangay : null,
                'shipping_city_municipality' => $shippingCityMunicipality !== '' ? $shippingCityMunicipality : null,
                'shipping_province' => $shippingProvince !== '' ? $shippingProvince : null,
                'shipping_zip_code' => $shippingZipCode !== '' ? $shippingZipCode : null,
            ]);
            $recordSale->closeCursor();

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
                'shipping_name' => $shippingName,
                'shipping_street' => $shippingStreet !== '' ? $shippingStreet : null,
                'shipping_barangay' => $shippingBarangay !== '' ? $shippingBarangay : null,
                'shipping_city_municipality' => $shippingCityMunicipality !== '' ? $shippingCityMunicipality : null,
                'shipping_province' => $shippingProvince !== '' ? $shippingProvince : null,
                'shipping_zip_code' => $shippingZipCode !== '' ? $shippingZipCode : null,
            ]);
            $updateSaleShipping->closeCursor();

            $recordSaleItem = $pdo->prepare(
                'CALL record_sale_item(:product_public_id, :sale_public_id, :quantity, :price, :serialnum)'
            );
            foreach ($saleItems as $entry) {
                $recordSaleItem->execute([
                    'product_public_id' => asString($entry['product_public_id'] ?? asString($entry['product']['public_id'] ?? '')),
                    'sale_public_id' => $salePublicId,
                    'quantity' => $entry['quantity'],
                    'price' => $entry['price'],
                    'serialnum' => null,
                ]);
                $recordSaleItem->closeCursor();
            }

            $paymentPublicId = randomPublicId('PY');
            $paymentStatus = 'Pending';
            $snapshotPaymentStatus = 'Pending';
            if ($paymentResolution === 'completed') {
                $paymentStatus = 'Completed';
                $snapshotPaymentStatus = 'Paid';
            } elseif ($paymentResolution === 'failed') {
                $paymentStatus = 'Failed';
                $snapshotPaymentStatus = 'Failed';
            }
        $recordPayment = $pdo->prepare(
            'CALL record_payment(:amount, :payment_method, :payment_status, :public_id, :sale_public_id, :return_public_id)'
        );
        $recordPayment->execute([
            'amount' => $grandTotal,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'public_id' => $paymentPublicId,
            'sale_public_id' => $salePublicId,
            'return_public_id' => null,
        ]);
            $recordPayment->closeCursor();
        } catch (PDOException $e) {
            rollbackIfInTransaction($pdo);
            $message = trim($e->getMessage());
            if (preg_match('/SQLSTATE\\[[^\\]]+\\]:[^:]*:\\s*(.+)$/', $message, $matches) === 1) {
                $message = trim((string) ($matches[1] ?? ''));
            }
            validationFail(['checkout' => $message !== '' ? $message : 'Unable to place this order right now.']);
        } catch (Throwable) {
            rollbackIfInTransaction($pdo);
            sendJson(500, ['ok' => false, 'message' => 'Unable to place this order right now.']);
        }

        $effectiveCustomerPublicId = (string) ($customer['public_id'] ?? (string) $claims['sub']);
        $mergedFromCustomerPublicId = null;
        if ($pendingMergeWalkInCustomer !== null) {
            try {
                $mergedCustomer = mergeWalkInAndOnlineCustomer(
                    $pdo,
                    (int) ($pendingMergeWalkInCustomer['customerID'] ?? 0),
                    (int) ($customer['customerID'] ?? 0)
                );
            } catch (Throwable $mergeError) {
                rollbackIfInTransaction($pdo);
                validationFail([
                    'merge_account' => 'Unable to merge your walk-in account at this time.',
                ]);
            }

            $mergedFromCustomerPublicId = asString($customer['public_id'] ?? (string) $claims['sub']);
            $customer = $mergedCustomer;
            $effectiveCustomerPublicId = asString($mergedCustomer['public_id'] ?? $effectiveCustomerPublicId);
        }

        if ($mustPersistDefaults) {
            $updateCustomerDefaults = $pdo->prepare(
                'CALL update_customer_master(
                    :public_id, :first_name, :last_name, :middle_name, :email_address,
                    :contact_number, :street_address, :barangay, :city_municipality,
                    :province, :zip_code, :status, :deleted_at
                )'
            );
            $updateCustomerDefaults->execute([
                'public_id' => $effectiveCustomerPublicId,
                'first_name' => asString($customer['first_name'] ?? ''),
                'last_name' => asString($customer['last_name'] ?? ''),
                'middle_name' => asString($customer['middle_name'] ?? '') !== '' ? asString($customer['middle_name'] ?? '') : null,
                'email_address' => $setDefaultEmail ? $shippingEmail : asString($customer['email_address'] ?? ''),
                'contact_number' => $setDefaultContactNumber
                    ? $shippingContactNormalized
                    : normalizePhoneNumber(asString($customer['contact_number'] ?? '')),
                'street_address' => asString($customer['street_address'] ?? '') !== '' ? asString($customer['street_address'] ?? '') : null,
                'barangay' => asString($customer['barangay'] ?? ''),
                'city_municipality' => asString($customer['city_municipality'] ?? ''),
                'province' => asString($customer['province'] ?? '') !== '' ? asString($customer['province'] ?? '') : null,
                'zip_code' => asString($customer['zip_code'] ?? '') !== '' ? asString($customer['zip_code'] ?? '') : null,
                'status' => null,
                'deleted_at' => null,
            ]);
            $updateCustomerDefaults->closeCursor();
        }

        if ($paymentMethod === 'Store Credit' && $paymentStatus === 'Completed') {
            try {
                ensureStoreCreditPurchaseEntry($pdo, $effectiveCustomerPublicId, $salePublicId, $grandTotal);
            } catch (Throwable $creditError) {
                rollbackIfInTransaction($pdo);
                $creditMessage = trim($creditError->getMessage());
                validationFail([
                    'payment_method' => $creditMessage !== ''
                        ? $creditMessage
                        : 'Unable to process store credit payment at this time.',
                ]);
            }
        }

        $pdo->commit();
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

        // remove checked out items from cart
        $checkedOutMap = [];
        foreach ($cartItems as $i) {
            $checkedOutMap[strtoupper(asString($i['product_public_id'] ?? ''))] = true;
        }
        $currentItems = normalizeCustomerCartItems(is_array($cart['items'] ?? null) ? $cart['items'] : []);
        $remainingItems = array_values(array_filter(
            $currentItems,
            static fn($item) => !isset($checkedOutMap[strtoupper(asString($item['product_public_id'] ?? ''))])
        ));

        if ($mergedFromCustomerPublicId !== null && $effectiveCustomerPublicId !== '' && $effectiveCustomerPublicId !== $mergedFromCustomerPublicId) {
            $targetCart = mongoFindOne($manager, $dbName, $cartCollection, ['customer_public_id' => $effectiveCustomerPublicId]);
            $targetItems = normalizeCustomerCartItems(is_array($targetCart['items'] ?? null) ? $targetCart['items'] : []);
            $finalMergedItems = normalizeCustomerCartItems(array_merge($targetItems, $remainingItems));
            upsertCustomerCartDocument($env, $manager, $effectiveCustomerPublicId, $finalMergedItems);

            mongoDeleteOne($manager, $dbName, $cartCollection, ['customer_public_id' => $mergedFromCustomerPublicId]);
        } else {
            upsertCustomerCartDocument($env, $manager, (string) $claims['sub'], $remainingItems);
        }

        $mongoOrderItems = [];
        foreach ($saleItems as $entry) {
            $productPublicId = asString($entry['product']['public_id'] ?? '');
            $catalogItem = $catalogMap[$productPublicId] ?? [];
            $mongoOrderItems[] = [
                'product_public_id' => $productPublicId,
                'product_name' => asString($entry['product']['product_name'] ?? ''),
                'quantity' => (int) $entry['quantity'],
                'price_at_sale' => (float) $entry['price'],
                'image_url' => asString($catalogItem['image_url'] ?? ''),
            ];
        }

        upsertMongoOrderSnapshot(
            $env,
            $salePublicId,
            $effectiveCustomerPublicId,
            $mongoOrderItems,
            [
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
                'payment_method' => $paymentMethod,
                'payment_status' => $snapshotPaymentStatus,
                'transaction_reference' => $paymentPublicId,
            ],
            [
                'full_name' => $shippingName,
                'phone' => $shippingContactNormalized ?? '',
                'email' => $shippingEmail,
                'street_address' => $shippingStreet,
                'barangay' => $shippingBarangay,
                'city_municipality' => $shippingCityMunicipality,
                'province' => $shippingProvince,
                'zip_code' => $shippingZipCode,
            ],
            'Pending',
            'System',
            'Order placed successfully.',
            null,
            null,
            $saleDateForMongo,
            $saleUpdatedAtForMongo
        );

        if ($mergedFromCustomerPublicId !== null && $effectiveCustomerPublicId !== '' && $effectiveCustomerPublicId !== $mergedFromCustomerPublicId) {
            try {
                mongoUpdateMany(
                    $manager,
                    $dbName,
                    mongoCollectionName($env, 'orders'),
                    ['customer_public_id' => $mergedFromCustomerPublicId],
                    ['$set' => ['customer_public_id' => $effectiveCustomerPublicId, 'updated_at' => nowUtc()]]
                );
            } catch (Throwable) {
                // non-blocking snapshot migration
            }
        }

        appendAuditLog($env, 'ORDER_LOG', 'CHECKOUT', [
            'actor_type' => 'CUSTOMER',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'Sale',
            'resource_public_id' => $salePublicId,
        ]);

        $sessionToken = null;
        if ($mergedFromCustomerPublicId !== null && $effectiveCustomerPublicId !== '') {
            $mergedIdentity = asString($customer['email_address'] ?? '');
            if ($mergedIdentity === '') {
                $mergedIdentity = asString($customer['contact_number'] ?? '');
            }
            $sessionToken = createToken([
                'sub' => $effectiveCustomerPublicId,
                'role' => 'customer',
                'name' => trim(asString($customer['first_name'] ?? '') . ' ' . asString($customer['last_name'] ?? '')),
                'email' => $mergedIdentity,
            ], tokenSecret($env), 60 * 60 * 24);
        }

        $responseData = [
            'order_public_id' => $salePublicId,
            'subtotal' => round($subtotal, 2),
            'shipping_fee' => $shippingFee,
            'total' => round($grandTotal, 2),
        ];
        if ($sessionToken !== null) {
            $responseData['session_token'] = $sessionToken;
            $responseData['merged_account_public_id'] = $effectiveCustomerPublicId;
        }

        sendJson(201, [
            'ok' => true,
            'message' => 'Checkout completed successfully.',
            'data' => $responseData,
        ]);
    }

      if ($path === '/customer/orders' && $method === 'GET') {
          $claims = requireAuth($env, ['customer']);
          $customer = getCustomerByPublicId($pdo, (string) $claims['sub']);
          if ($customer === null) {
              sendJson(404, ['ok' => false, 'message' => 'Customer not found.']);
          }
        $stmt = $pdo->prepare(
            'SELECT
                s.saleID,
                s.public_id,
                s.sale_date,
                s.updated_at,
                s.total_amount,
                s.fulfillment_method,
                s.sale_status,
                s.tracking_number,
                s.courier_name,
                s.shipping_street,
                s.shipping_barangay,
                s.shipping_city_municipality,
                s.shipping_province,
                s.shipping_zip_code,
                (
                    SELECT pay.payment_method
                    FROM api_payment pay
                    WHERE pay.saleID = s.saleID
                    ORDER BY pay.paymentID DESC
                    LIMIT 1
                ) AS payment_method,
                (
                    SELECT pay.payment_status
                    FROM api_payment pay
                    WHERE pay.saleID = s.saleID
                    ORDER BY pay.paymentID DESC
                    LIMIT 1
                ) AS payment_status,
                (
                    SELECT COALESCE(NULLIF(pay.public_id, ""), s.public_id)
                    FROM api_payment pay
                    WHERE pay.saleID = s.saleID
                    ORDER BY pay.paymentID DESC
                    LIMIT 1
                ) AS payment_reference
             FROM api_sale s
             WHERE s.customerID = :customerID
             ORDER BY s.sale_date DESC'
        );
        $stmt->execute(['customerID' => (int) $customer['customerID']]);
        $sales = $stmt->fetchAll();

        $itemStmt = $pdo->prepare(
            'SELECT si.sale_itemID, si.quantity_sold, si.price_at_sale, p.public_id AS product_public_id, p.product_name
             FROM api_sale_item si
             JOIN api_product p ON p.productID = si.productID
             WHERE si.saleID = :saleID'
        );
        $returnItemStmt = $pdo->prepare(
            'SELECT
                rt.public_id AS return_public_id,
                rt.return_progress,
                rt.return_method,
                rt.date_created,
                ri.return_quantity,
                ri.reason,
                ri.notes,
                p.public_id AS product_public_id
             FROM api_return_transaction rt
             JOIN api_return_item ri ON ri.returnID = rt.returnID
             JOIN api_sale_item si ON si.sale_itemID = ri.sale_itemID
             JOIN api_product p ON p.productID = si.productID
             WHERE rt.saleID = :saleID
             ORDER BY rt.date_created DESC, rt.returnID DESC'
        );

        $catalogMap = catalogMapByPublicId(loadProductCatalog($env));
        $ordersManager = mongoManager($env);
        $ordersDb = mongoDbName($env);
        $ordersCollection = mongoCollectionName($env, 'orders');
        $reviewsCollection = mongoCollectionName($env, 'product_reviews');
        $reviewedProductMap = [];
        if ($ordersManager) {
            try {
                $customerReviewDocs = mongoFindMany($ordersManager, $ordersDb, $reviewsCollection, [
                    'customer_public_id' => (string) $claims['sub'],
                ], [
                    'projection' => ['product_public_id' => 1],
                    'limit' => 5000,
                ]);
                foreach ($customerReviewDocs as $customerReviewDoc) {
                    $reviewedProductPublicId = asString($customerReviewDoc['product_public_id'] ?? '');
                    if ($reviewedProductPublicId !== '') {
                        $reviewedProductMap[$reviewedProductPublicId] = true;
                    }
                }
            } catch (Throwable) {
                $reviewedProductMap = [];
            }
        }
        $customerFullName = trim(((string) ($customer['first_name'] ?? '')) . ' ' . ((string) ($customer['last_name'] ?? '')));
        $orders = [];
        foreach ($sales as $sale) {
            $itemStmt->execute(['saleID' => (int) $sale['saleID']]);
            $items = $itemStmt->fetchAll();
            $returnItemStmt->execute(['saleID' => (int) $sale['saleID']]);
            $returnRows = $returnItemStmt->fetchAll();
            $normalizedStatus = normalizeSaleStatusForOutput(
                (string) ($sale['sale_status'] ?? 'Pending'),
                (string) ($sale['fulfillment_method'] ?? 'Delivery')
            );
            $returnByProduct = [];
            foreach ($returnRows as $returnRow) {
                $returnedProductPublicId = asString($returnRow['product_public_id'] ?? '');
                if ($returnedProductPublicId === '' || isset($returnByProduct[$returnedProductPublicId])) {
                    continue;
                }
                $returnByProduct[$returnedProductPublicId] = [
                    'id' => asString($returnRow['return_public_id'] ?? ''),
                    'status' => asString($returnRow['return_progress'] ?? 'Requested'),
                    'return_method' => asString($returnRow['return_method'] ?? ''),
                    'quantity' => (int) ($returnRow['return_quantity'] ?? 0),
                    'reason' => asString($returnRow['reason'] ?? ''),
                    'description' => asString($returnRow['notes'] ?? ''),
                    'requested_at' => asString($returnRow['date_created'] ?? ''),
                ];
            }

            $street = asString($sale['shipping_street'] ?? '');
            $barangay = asString($sale['shipping_barangay'] ?? '');
            $city = asString($sale['shipping_city_municipality'] ?? '');
            $province = asString($sale['shipping_province'] ?? '');
            $zip = asString($sale['shipping_zip_code'] ?? '');
            $addressParts = array_values(array_filter([$street, $barangay, $city, $province, $zip], static fn($part) => $part !== ''));
            $trackingNumber = asString($sale['tracking_number'] ?? '');
            $courierName = asString($sale['courier_name'] ?? '');
            $fulfillmentMethod = (string) ($sale['fulfillment_method'] ?? 'Delivery');
            $saleDate = (string) ($sale['sale_date'] ?? '');
            $updatedAt = (string) ($sale['updated_at'] ?? '');
            $salePublicId = (string) ($sale['public_id'] ?? '');

            $mongoOrderItems = [];
            $orderItems = [];
            $allItemsReviewed = count($items) > 0;
            $subtotal = 0.0;
            foreach ($items as $it) {
                $productPublicId = (string) ($it['product_public_id'] ?? '');
                $quantity = (int) ($it['quantity_sold'] ?? 0);
                $priceAtSale = (float) ($it['price_at_sale'] ?? 0);
                $catalogItem = $catalogMap[$productPublicId] ?? [];
                $imageUrl = asString($catalogItem['image_url'] ?? '');
                $productName = (string) ($it['product_name'] ?? '');

                $subtotal += $quantity * $priceAtSale;
                $isItemReviewed = isset($reviewedProductMap[$productPublicId]);
                if (!$isItemReviewed) {
                    $allItemsReviewed = false;
                }

                $mongoOrderItems[] = [
                    'product_public_id' => $productPublicId,
                    'product_name' => $productName,
                    'quantity' => $quantity,
                    'price_at_sale' => $priceAtSale,
                    'image_url' => $imageUrl,
                ];

                $orderItems[] = [
                    'public_id' => $productPublicId,
                    'product_public_id' => $productPublicId,
                    'name' => $productName,
                    'product_name' => $productName,
                    'qty' => $quantity,
                    'quantity' => $quantity,
                    'price' => $priceAtSale,
                    'price_at_sale' => $priceAtSale,
                    'image_url' => $imageUrl,
                    'is_reviewed' => $isItemReviewed,
                    'return_request' => $returnByProduct[$productPublicId] ?? null,
                ];
            }

            $orderDoc = null;
            if ($ordersManager && $salePublicId !== '') {
                try {
                    $orderDoc = mongoFindOne($ordersManager, $ordersDb, $ordersCollection, [
                        'order_public_id' => $salePublicId,
                    ]);
                } catch (Throwable) {
                    $orderDoc = null;
                }

                $needsSnapshot = !is_array($orderDoc)
                    || !is_array($orderDoc['items'] ?? null)
                    || !is_array($orderDoc['payment'] ?? null)
                    || !is_array($orderDoc['shipping_address'] ?? null);

                if ($needsSnapshot) {
                    $snapshotPaymentMethod = asString($sale['payment_method'] ?? '');
                    $snapshotPaymentStatus = asString($sale['payment_status'] ?? '');
                    if ($snapshotPaymentStatus === '') {
                        $snapshotPaymentStatus = strcasecmp($snapshotPaymentMethod, 'Cash') === 0 ? 'Pending' : 'Paid';
                    }
                    upsertMongoOrderSnapshot(
                        $env,
                        $salePublicId,
                        (string) $claims['sub'],
                        $mongoOrderItems,
                        [
                            'subtotal' => $subtotal,
                            'grand_total' => (float) ($sale['total_amount'] ?? 0),
                            'payment_method' => $snapshotPaymentMethod,
                            'payment_status' => $snapshotPaymentStatus,
                            'transaction_reference' => asString($sale['payment_reference'] ?? ''),
                        ],
                        [
                            'full_name' => $customerFullName,
                            'phone' => asString($customer['contact_number'] ?? ''),
                            'email' => asString($customer['email_address'] ?? ''),
                            'street_address' => $street,
                            'barangay' => $barangay,
                            'city_municipality' => $city,
                            'province' => $province,
                            'zip_code' => $zip,
                        ],
                        $normalizedStatus,
                        'System',
                        'Order synchronized from sale record.',
                        $trackingNumber !== '' ? $trackingNumber : null,
                        $courierName !== '' ? $courierName : null,
                        $saleDate,
                        $updatedAt
                    );

                    try {
                        $orderDoc = mongoFindOne($ordersManager, $ordersDb, $ordersCollection, [
                            'order_public_id' => $salePublicId,
                        ]);
                    } catch (Throwable) {
                        $orderDoc = null;
                    }
                }
            }

            $paymentMethodValue = asString($sale['payment_method'] ?? '');
            $paymentStatusValue = asString($sale['payment_status'] ?? '');
            $isCashOnDelivery = in_array(strtolower(trim($paymentMethodValue)), ['cash', 'cash on delivery', 'cod'], true);
            if (
                in_array(strtoupper($normalizedStatus), ['COMPLETED', 'FINALIZED'], true)
                && $isCashOnDelivery
                && strcasecmp($paymentStatusValue, 'Completed') !== 0
            ) {
                $paymentStatusValue = 'Completed';
            }

            if ($ordersManager && is_array($orderDoc)) {
                $mongoOrderStatus = asString($orderDoc['order_status'] ?? '');
                $mongoStatusHistory = is_array($orderDoc['status_history'] ?? null) ? (array) $orderDoc['status_history'] : [];
                if (
                    $normalizedStatus !== ''
                    && ($mongoOrderStatus === ''
                        || strcasecmp($mongoOrderStatus, $normalizedStatus) !== 0
                        || $mongoStatusHistory === [])
                ) {
                    upsertMongoOrderStatus(
                        $env,
                        $salePublicId,
                        (string) $claims['sub'],
                        $normalizedStatus,
                        'System',
                        'Order synchronized from sale record.',
                        $trackingNumber !== '' ? $trackingNumber : null,
                        $courierName !== '' ? $courierName : null,
                        false,
                        $updatedAt
                    );
                }

                $mongoPaymentStatus = normalizeOrderPaymentStatus(asString($orderDoc['payment']['payment_status'] ?? ''));
                $normalizedPaymentStatus = normalizeOrderPaymentStatus($paymentStatusValue);
                $mongoPaymentHistory = is_array($orderDoc['payment_history_status'] ?? null)
                    ? (array) $orderDoc['payment_history_status']
                    : [];
                if (
                    $normalizedPaymentStatus !== ''
                    && ($mongoPaymentStatus === ''
                        || strcasecmp($mongoPaymentStatus, $normalizedPaymentStatus) !== 0
                        || $mongoPaymentHistory === [])
                ) {
                    upsertMongoOrderPaymentStatus(
                        $env,
                        $salePublicId,
                        (string) $claims['sub'],
                        $normalizedPaymentStatus,
                        'System',
                        'Payment status synchronized from sale record.',
                        $paymentMethodValue,
                        asString($sale['payment_reference'] ?? '')
                    );
                }
            }

            $orders[] = [
                'id' => $salePublicId,
                'date' => $saleDate,
                'status' => strtoupper($normalizedStatus),
                'total' => (float) $sale['total_amount'],
                'shippingFee' => 0.0,
                'paymentMethod' => $paymentMethodValue,
                'paymentStatus' => normalizeOrderPaymentStatus($paymentStatusValue),
                'paymentReference' => asString($sale['payment_reference'] ?? ''),
                'address' => implode(', ', $addressParts),
                'tracking_number' => $trackingNumber,
                'courier_name' => $courierName,
                'status_message' => orderStatusMessage($normalizedStatus, $trackingNumber, $courierName, $fulfillmentMethod),
                'isReviewed' => $allItemsReviewed,
                'timeline' => buildOrderTimeline(
                    is_array($orderDoc) ? $orderDoc : null,
                    $normalizedStatus,
                    $saleDate,
                    $updatedAt,
                    $trackingNumber,
                    $courierName,
                    $fulfillmentMethod
                ),
                'fulfillment' => $fulfillmentMethod,
                'items' => $orderItems,
            ];
        }

        sendJson(200, ['ok' => true, 'data' => ['orders' => $orders]]);
    }

    if (preg_match('#^/customer/orders/([A-Za-z0-9\-]+)/received$#', $path, $matches) === 1 && $method === 'PUT') {
        $claims = requireAuth($env, ['customer']);
        $orderPublicId = $matches[1];
        if (!isValidPublicId($orderPublicId)) {
            validationFail(['order' => 'Invalid order reference format.']);
        }

        $customer = getCustomerByPublicId($pdo, (string) $claims['sub']);
        if ($customer === null) {
            sendJson(404, ['ok' => false, 'message' => 'Customer not found.']);
        }

        $saleStmt = $pdo->prepare(
            'SELECT s.saleID, s.sale_status, s.fulfillment_method, s.tracking_number,
                    pay.paymentID AS payment_id, pay.payment_method, pay.payment_status
             FROM api_sale s
             LEFT JOIN (
                SELECT p.paymentID, p.saleID, p.payment_method, p.payment_status
                FROM api_payment p
                JOIN (
                    SELECT saleID, MAX(paymentID) AS latest_payment_id
                    FROM api_payment
                    WHERE saleID IS NOT NULL
                    GROUP BY saleID
                ) latest ON latest.latest_payment_id = p.paymentID
             ) pay ON pay.saleID = s.saleID
             WHERE s.public_id = :public_id AND s.customerID = :customerID
             LIMIT 1'
        );
        $saleStmt->execute([
            'public_id' => $orderPublicId,
            'customerID' => (int) $customer['customerID'],
        ]);
        $sale = $saleStmt->fetch();
        if (!is_array($sale)) {
            sendJson(404, ['ok' => false, 'message' => 'Order not found for this account.']);
        }

        $currentStatus = asString($sale['sale_status'] ?? '');
        $paymentMethod = asString($sale['payment_method'] ?? '');
        $paymentStatus = asString($sale['payment_status'] ?? '');
        $paymentId = (int) ($sale['payment_id'] ?? 0);
        $isCashOnDelivery = in_array(strtolower(trim($paymentMethod)), ['cash', 'cash on delivery', 'cod'], true);
        $codPaymentSynced = false;

        if (in_array($currentStatus, ['Completed', 'Cancelled'], true)) {
            $alreadyFinalizedMessage = $codPaymentSynced
                ? 'Order already finalized. Cash payment marked as completed.'
                : 'Order already finalized.';
            sendJson(200, ['ok' => true, 'message' => $alreadyFinalizedMessage]);
        }
        $employeePublicId = firstEmployeePublicId($pdo);
        if ($employeePublicId === null) {
            sendJson(500, ['ok' => false, 'message' => 'No employee records found for workflow assignment.']);
        }
        try {
            $update = $pdo->prepare(
                'CALL order_status_update_customer(
                    :sale_public_id,
                    :sale_status,
                    :tracking_number,
                    :courier_name,
                    :employee_public_id
                )'
            );
            $update->execute([
                'sale_public_id' => $orderPublicId,
                'sale_status' => 'Completed',
                'tracking_number' => null,
                'courier_name' => null,
                'employee_public_id' => $employeePublicId,
            ]);
            $update->closeCursor();
        } catch (PDOException $e) {
            $message = trim($e->getMessage());
            if (preg_match('/SQLSTATE\\[[^\\]]+\\]:[^:]*:\\s*(.+)$/', $message, $matches) === 1) {
                $message = trim((string) ($matches[1] ?? ''));
            }
            validationFail(['status' => $message !== '' ? $message : 'Unable to update order status.']);
        }

        if ($paymentId > 0) {
            $paymentAfter = $pdo->prepare(
                'SELECT payment_status
                 FROM payment
                 WHERE paymentID = :payment_id
                 LIMIT 1'
            );
            $paymentAfter->execute(['payment_id' => $paymentId]);
            $paymentAfterRow = $paymentAfter->fetch();
            if (is_array($paymentAfterRow)) {
                $latestPaymentStatus = asString($paymentAfterRow['payment_status'] ?? '');
                $codPaymentSynced = $isCashOnDelivery
                    && strcasecmp($paymentStatus, 'Completed') !== 0
                    && strcasecmp($latestPaymentStatus, 'Completed') === 0;
            }
        }
        $statusTimestamp = '';
        $statusTimestampStmt = $pdo->prepare(
            'SELECT updated_at
             FROM api_sale
             WHERE public_id = :public_id
             LIMIT 1'
        );
        $statusTimestampStmt->execute(['public_id' => $orderPublicId]);
        $statusTimestampRow = $statusTimestampStmt->fetch();
        if (is_array($statusTimestampRow)) {
            $statusTimestamp = asString($statusTimestampRow['updated_at'] ?? '');
        }

        appendAuditLog($env, 'ORDER_LOG', 'ORDER_RECEIVED_CONFIRM', [
            'actor_type' => 'CUSTOMER',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'Sale',
            'resource_public_id' => $orderPublicId,
        ], array_filter([
            'previous_state' => $currentStatus,
            'new_state' => 'Completed',
            'payment_status_synced' => $codPaymentSynced ? 'Completed' : null,
        ], static fn($value) => $value !== null));

        upsertMongoOrderStatus(
            $env,
            $orderPublicId,
            (string) $claims['sub'],
            'Completed',
            (string) $claims['sub'],
            'Customer confirmed order received.',
            asString($sale['tracking_number'] ?? ''),
            null,
            false,
            $statusTimestamp
        );
        if ($codPaymentSynced) {
            upsertMongoOrderPaymentStatus(
                $env,
                $orderPublicId,
                (string) $claims['sub'],
                'Completed',
                (string) $claims['sub'],
                'Cash on delivery payment marked completed after customer received order.',
                $paymentMethod,
                asString($sale['payment_reference'] ?? '')
            );
        }

        sendJson(200, ['ok' => true, 'message' => 'Order marked as received.']);
    }

    if (preg_match('#^/customer/orders/([A-Za-z0-9\-]+)/cancel$#', $path, $matches) === 1 && $method === 'PUT') {
        $claims = requireAuth($env, ['customer']);
        $orderPublicId = $matches[1];
        if (!isValidPublicId($orderPublicId)) {
            validationFail(['order' => 'Invalid order reference format.']);
        }

        $customer = getCustomerByPublicId($pdo, (string) $claims['sub']);
        if ($customer === null) {
            sendJson(404, ['ok' => false, 'message' => 'Customer not found.']);
        }

        $saleStmt = $pdo->prepare(
            'SELECT saleID, sale_status, fulfillment_method, tracking_number, courier_name
             FROM api_sale
             WHERE public_id = :public_id AND customerID = :customerID
             LIMIT 1'
        );
        $saleStmt->execute([
            'public_id' => $orderPublicId,
            'customerID' => (int) $customer['customerID'],
        ]);
        $sale = $saleStmt->fetch();
        if (!is_array($sale)) {
            sendJson(404, ['ok' => false, 'message' => 'Order not found for this account.']);
        }

        $currentStatus = asString($sale['sale_status'] ?? '');
        if ($currentStatus === 'Cancelled') {
            sendJson(200, ['ok' => true, 'message' => 'Order is already cancelled.']);
        }

        $employeePublicId = firstEmployeePublicId($pdo);
        if ($employeePublicId === null) {
            sendJson(500, ['ok' => false, 'message' => 'No employee records found for workflow assignment.']);
        }

        $statusTimestamp = '';
        try {
            $pdo->beginTransaction();

            $update = $pdo->prepare(
                'CALL order_status_update_customer(
                    :sale_public_id,
                    :sale_status,
                    :tracking_number,
                    :courier_name,
                    :employee_public_id
                )'
            );
            $update->execute([
                'sale_public_id' => $orderPublicId,
                'sale_status' => 'Cancelled',
                'tracking_number' => asString($sale['tracking_number'] ?? '') !== '' ? asString($sale['tracking_number'] ?? '') : null,
                'courier_name' => asString($sale['courier_name'] ?? '') !== '' ? asString($sale['courier_name'] ?? '') : null,
                'employee_public_id' => $employeePublicId,
            ]);
            $update->closeCursor();

            $statusTimestampStmt = $pdo->prepare(
                'SELECT updated_at
                 FROM api_sale
                 WHERE public_id = :public_id
                 LIMIT 1'
            );
            $statusTimestampStmt->execute(['public_id' => $orderPublicId]);
            $statusTimestampRow = $statusTimestampStmt->fetch();
            if (is_array($statusTimestampRow)) {
                $statusTimestamp = asString($statusTimestampRow['updated_at'] ?? '');
            }

            $pdo->commit();
        } catch (Throwable $e) {
            rollbackIfInTransaction($pdo);
            if ($e instanceof PDOException) {
                $message = trim($e->getMessage());
                validationFail(['status' => $message !== '' ? $message : 'Unable to cancel order.']);
            }
            $message = trim($e->getMessage());
            validationFail(['status' => $message !== '' ? $message : 'Unable to cancel order.']);
        }

        $cancellationEffects = [
            'inventory_restocked' => false,
            'inventory_transaction_type' => '',
            'payment_cancelled' => false,
            'store_credit_refunded' => false,
            'payment_method' => '',
            'payment_public_id' => '',
            'refund_amount' => 0.0,
        ];
        $inventoryCheck = $pdo->prepare(
            'SELECT 1
             FROM inventory_transaction it
             JOIN sale s ON s.saleID = it.referenceID
             WHERE s.public_id = :public_id
               AND it.transaction_type = "Cancelled Sale"
             LIMIT 1'
        );
        $inventoryCheck->execute(['public_id' => $orderPublicId]);
        if ($inventoryCheck->fetchColumn() !== false) {
            $cancellationEffects['inventory_restocked'] = true;
            $cancellationEffects['inventory_transaction_type'] = 'Cancelled Sale';
        }

        $paymentInfo = $pdo->prepare(
            'SELECT p.public_id, p.payment_method, p.payment_status
             FROM payment p
             JOIN sale s ON s.saleID = p.saleID
             WHERE s.public_id = :public_id
             ORDER BY p.paymentID DESC
             LIMIT 1'
        );
        $paymentInfo->execute(['public_id' => $orderPublicId]);
        $paymentRow = $paymentInfo->fetch();
        if (is_array($paymentRow)) {
            $cancellationEffects['payment_method'] = asString($paymentRow['payment_method'] ?? '');
            $cancellationEffects['payment_public_id'] = asString($paymentRow['public_id'] ?? '');
            $cancellationEffects['payment_cancelled'] = strcasecmp(asString($paymentRow['payment_status'] ?? ''), 'Cancelled') === 0;
        }

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
        $creditCheck->execute(['public_id' => $orderPublicId]);
        $creditRow = $creditCheck->fetch();
        if (is_array($creditRow)) {
            $cancellationEffects['store_credit_refunded'] = true;
            $cancellationEffects['refund_amount'] = round((float) ($creditRow['amount'] ?? 0), 2);
        }

        appendAuditLog($env, 'ORDER_LOG', 'ORDER_CANCELLED_BY_CUSTOMER', [
            'actor_type' => 'CUSTOMER',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'Sale',
            'resource_public_id' => $orderPublicId,
        ], array_filter([
            'previous_state' => $currentStatus,
            'new_state' => 'Cancelled',
            'inventory_restocked' => $cancellationEffects['inventory_restocked'] ? true : null,
            'inventory_transaction_type' => asString($cancellationEffects['inventory_transaction_type'] ?? '') !== ''
                ? asString($cancellationEffects['inventory_transaction_type'] ?? '')
                : null,
            'payment_status_synced' => $cancellationEffects['payment_cancelled'] ? 'Cancelled' : null,
            'store_credit_refunded' => $cancellationEffects['store_credit_refunded'] ? true : null,
            'refunded_amount' => (float) ($cancellationEffects['refund_amount'] ?? 0) > 0
                ? round((float) ($cancellationEffects['refund_amount'] ?? 0), 2)
                : null,
        ], static fn($value) => $value !== null));

        upsertMongoOrderStatus(
            $env,
            $orderPublicId,
            (string) $claims['sub'],
            'Cancelled',
            (string) $claims['sub'],
            'Customer cancelled the order.',
            asString($sale['tracking_number'] ?? ''),
            asString($sale['courier_name'] ?? ''),
            false,
            $statusTimestamp
        );
        if ($cancellationEffects['payment_cancelled']) {
            upsertMongoOrderPaymentStatus(
                $env,
                $orderPublicId,
                (string) $claims['sub'],
                'Cancelled',
                (string) $claims['sub'],
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

        sendJson(200, ['ok' => true, 'message' => 'Order cancelled successfully.']);
    }

if ($path === '/customer/returns' && $method === 'POST') {
        $claims = requireAuth($env, ['customer']);
        $body = requestBody($env);
        $orderPublicId = asString($body['order_public_id'] ?? '');
        $productPublicId = asString($body['product_public_id'] ?? '');
        $reason = asString($body['reason'] ?? 'Defective');
        $description = asString($body['description'] ?? '');
        $quantity = (int) ($body['quantity'] ?? 1);
        $returnMethod = asString($body['return_method'] ?? 'Drop-off');
        $refundMethod = asString($body['refund_method'] ?? 'Original Payment Method');

        $errors = [];
        if ($orderPublicId === '') {
            $errors['order_public_id'] = 'Order is required.';
        } elseif (!isValidPublicId($orderPublicId)) {
            $errors['order_public_id'] = 'Invalid order reference format.';
        }
        if ($productPublicId === '') {
            $errors['product_public_id'] = 'Product is required.';
        } elseif (!isValidPublicId($productPublicId)) {
            $errors['product_public_id'] = 'Invalid product reference format.';
        }
        if (!in_array($reason, ['Defective', 'Change of Mind'], true)) {
            $errors['reason'] = 'Reason must be Defective or Change of Mind.';
        }
        if ($quantity < 1 || $quantity > 50) {
            $errors['quantity'] = 'Return quantity must be between 1 and 50.';
        }
        if ($description === '') {
            $errors['description'] = 'Please provide return details.';
        } else {
            $descriptionError = validateTextLength($description, 'Return details', 5, 1000);
            if ($descriptionError !== null) {
                $errors['description'] = $descriptionError;
            }
        }
        if (!ensureAllowedValue($returnMethod, ['Drop-off', 'Courier'])) {
            $errors['return_method'] = 'Return method must be Drop-off or Courier.';
        }
        if (!ensureAllowedValue($refundMethod, ['Store Credit', 'Original Payment Method'])) {
            $errors['refund_method'] = 'Refund method must be Store Credit or Original Payment Method.';
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        $customer = getCustomerByPublicId($pdo, (string) $claims['sub']);
        if ($customer === null) {
            sendJson(404, ['ok' => false, 'message' => 'Customer not found.']);
        }

        $saleStmt = $pdo->prepare('SELECT saleID, sale_status FROM api_sale WHERE public_id = :public_id AND customerID = :customerID LIMIT 1');
        $saleStmt->execute(['public_id' => $orderPublicId, 'customerID' => (int) $customer['customerID']]);
        $sale = $saleStmt->fetch();
        if (!is_array($sale)) {
            sendJson(404, ['ok' => false, 'message' => 'Order not found for this account.']);
        }
        $saleStatus = asString($sale['sale_status'] ?? '');
        if (!in_array($saleStatus, ['Shipped', 'Delivered', 'Completed'], true)) {
            validationFail(['order' => 'Returns are available only for shipped/delivered/completed orders.']);
        }

        $product = mysqlProductByPublicId($pdo, $productPublicId);
        if ($product === null) {
            sendJson(404, ['ok' => false, 'message' => 'Product not found.']);
        }

        $saleItemStmt = $pdo->prepare('SELECT sale_itemID, quantity_sold, price_at_sale FROM api_sale_item WHERE saleID = :saleID AND productID = :productID LIMIT 1');
        $saleItemStmt->execute(['saleID' => (int) $sale['saleID'], 'productID' => (int) $product['productID']]);
        $saleItem = $saleItemStmt->fetch();
        if (!is_array($saleItem)) {
            sendJson(404, ['ok' => false, 'message' => 'This product is not part of the selected order.']);
        }

        $existingReturnStmt = $pdo->prepare(
            'SELECT rt.public_id, rt.return_progress
             FROM api_return_item ri
             JOIN api_return_transaction rt ON rt.returnID = ri.returnID
             WHERE ri.sale_itemID = :sale_item_id
             ORDER BY rt.date_created DESC, rt.returnID DESC
             LIMIT 1'
        );
        $existingReturnStmt->execute(['sale_item_id' => (int) $saleItem['sale_itemID']]);
        $existingReturn = $existingReturnStmt->fetch();
        if (is_array($existingReturn)) {
            validationFail([
                'order' => 'A return request already exists for this item. Please check return details.',
            ]);
        }

        if ($quantity > (int) $saleItem['quantity_sold']) {
            validationFail(['quantity' => 'Return quantity cannot exceed purchased quantity.']);
        }

        $alreadyReturned = returnedQuantityForSaleItem($pdo, (int) $saleItem['sale_itemID']);
        if (($alreadyReturned + $quantity) > (int) $saleItem['quantity_sold']) {
            validationFail([
                'quantity' => 'Return quantity exceeds remaining eligible quantity for this order item.',
            ]);
        }

        $employeePublicId = firstEmployeePublicId($pdo);
        if ($employeePublicId === null) {
            sendJson(500, ['ok' => false, 'message' => 'No employee records found for workflow assignment.']);
        }

        $pdo->beginTransaction();

        $returnPublicId = randomPublicId('RT');
        $insertReturn = $pdo->prepare(
            'CALL return_transaction_add(
                :public_id, :sale_public_id, :employee_public_id, :refund_amount, :return_method
            )'
        );
        $insertReturn->execute([
            'public_id' => $returnPublicId,
            'sale_public_id' => $orderPublicId,
            'employee_public_id' => $employeePublicId,
            'refund_amount' => ((float) $saleItem['price_at_sale']) * $quantity,
            'return_method' => $returnMethod,
        ]);
        $insertReturn->closeCursor();

        $insertReturnItem = $pdo->prepare(
            'CALL record_return_item_secure(
                :return_public_id, :sale_public_id, :product_public_id, :return_quantity,
                :reason, :return_status, :serialnum, :notes
            )'
        );
        $initialReturnStatus = $refundMethod === 'Store Credit' ? 'Store Credit' : 'Pending';
        $insertReturnItem->execute([
            'return_public_id' => $returnPublicId,
            'sale_public_id' => $orderPublicId,
            'product_public_id' => $productPublicId,
            'return_quantity' => $quantity,
            'reason' => $reason,
            'return_status' => $initialReturnStatus,
            'serialnum' => null,
            'notes' => $description,
        ]);
        $insertReturnItem->closeCursor();

        $pdo->commit();

        $shipmentMethodLabel = $returnMethod === 'Courier' ? 'Courier Pick-up' : 'Drop-off';
        upsertMongoReturnRequest(
            $env,
            $returnPublicId,
            (string) $claims['sub'],
            $orderPublicId,
            [
                'product_public_id' => $productPublicId,
                'product_name' => asString($product['product_name'] ?? ''),
                'unit_price' => (float) ($saleItem['price_at_sale'] ?? 0),
            ],
            $reason,
            $description,
            $shipmentMethodLabel,
            $refundMethod,
            'Pending Review',
            null,
            (string) $claims['sub'],
            'Customer submitted return request.'
        );

        appendAuditLog($env, 'RETURN_LOG', 'RETURN_REQUESTED', [
            'actor_type' => 'CUSTOMER',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'ReturnTransaction',
            'resource_public_id' => $returnPublicId,
        ]);

        sendJson(201, [
            'ok' => true,
            'message' => 'Return request submitted.',
            'data' => [
                'return_public_id' => $returnPublicId,
            ],
        ]);
    }

