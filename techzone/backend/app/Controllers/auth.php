<?php

declare(strict_types=1);

if ($path === '/auth/customer/register/precheck' && $method === 'POST') {
        $body = requestBody($env);
        $email = strtolower(asString($body['email'] ?? ''));
        $phone = asString($body['phone'] ?? $body['contact_number'] ?? '');
        $phoneNormalized = normalizePhoneNumber($phone);

        $errors = [];
        if ($email !== '' && !validateEmail($email)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if ($phone !== '' && $phoneNormalized === null) {
            $errors['phone'] = 'Please enter a valid phone number (example: 09171234567 or 639171234567).';
        }
        if ($email === '' && $phoneNormalized === null) {
            $errors['contact'] = 'Please provide either email or contact number.';
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        try {
            $precheck = $pdo->prepare('CALL customer_precheck(:email, :contact)');
            $precheck->execute([
                'email' => $email !== '' ? $email : null,
                'contact' => $phoneNormalized,
            ]);
            $precheck->closeCursor();
        } catch (PDOException $e) {
            sendJson(409, [
                'ok' => false,
                'message' => 'Already an existing customer.',
                'errors' => ['register' => 'Already an existing customer.'],
            ]);
        }

        sendJson(200, ['ok' => true, 'message' => 'Contact information is available.']);
    }

if ($path === '/auth/customer/register' && $method === 'POST') {
        $body = requestBody($env);

        $firstName = asString($body['first_name'] ?? '');
        $middleName = asString($body['middle_name'] ?? '');
        $lastName = asString($body['last_name'] ?? '');
        $email = strtolower(asString($body['email'] ?? ''));
        $password = (string) ($body['password'] ?? '');
        $phone = asString($body['phone'] ?? '');
        $street = asString($body['street_address'] ?? '');
        $barangay = asString($body['barangay'] ?? '');
        $city = asString($body['city_municipality'] ?? '');
        $province = asString($body['province'] ?? '');
        $zip = asString($body['zip_code'] ?? '');
        $otpCode = asString($body['otp_code'] ?? '');
        $otpIssuedAt = (int) ($body['otp_issued_at'] ?? 0);
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
        if ($middleName !== '') {
            $middleNameError = validateHumanName($middleName, 'Middle name', 2, 50);
            if ($middleNameError !== null) {
                $errors['middle_name'] = $middleNameError;
            }
        }
        if ($email !== '' && !validateEmail($email)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        $passwordError = validatePassword($password);
        if ($passwordError !== null) {
            $errors['password'] = $passwordError;
        }
        if ($phone !== '' && $phoneNormalized === null) {
            $errors['phone'] = 'Please enter a valid phone number (example: 09171234567 or 639171234567).';
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
        if ($email === '' && $phoneNormalized === null) {
            $errors['contact'] = 'Please provide at least an email address or a contact number.';
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        if ($otpCode === '') {
            validationFail([
                'otp_code' => 'OTP verification is required.',
            ]);
        }

        if (!verifyDemoOtp($env, $otpCode, $otpIssuedAt > 0 ? $otpIssuedAt : null)) {
            validationFail([
                'otp_code' => 'OTP is invalid or expired.',
            ]);
        }

        $publicId = randomPublicId('CS');
        $customer = null;
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                'CALL customer_create(
                    :public_id, :first_name, :middle_name, :last_name, :customer_type, :password_hash, :merge_otp_verified,
                    :email_address, :contact_number, :street_address, :barangay,
                    :province, :city_municipality, :zip_code, :status
                )'
            );
            $stmt->execute([
                'public_id' => $publicId,
                'first_name' => $firstName,
                'middle_name' => $middleName !== '' ? $middleName : null,
                'last_name' => $lastName,
                'customer_type' => 'Registered',
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'merge_otp_verified' => 1,
                'email_address' => $email !== '' ? $email : null,
                'contact_number' => $phoneNormalized,
                'street_address' => $street !== '' ? $street : null,
                'barangay' => $barangay,
                'province' => $province !== '' ? $province : null,
                'city_municipality' => $city,
                'zip_code' => $zip !== '' ? $zip : null,
                'status' => 'Active',
            ]);
            $stmt->closeCursor();

            if ($email !== '') {
                $customer = getCustomerByEmail($pdo, $email);
            }
            if ($customer === null && $phoneNormalized !== null) {
                $customer = getCustomerByContact($pdo, $phoneNormalized);
            }
            if ($customer === null) {
                throw new RuntimeException('Account registration completed, but profile lookup failed.');
            }

            $pdo->commit();
        } catch (PDOException $e) {
            rollbackIfInTransaction($pdo);
            $message = trim($e->getMessage());
            if (preg_match('/SQLSTATE\[[^\]]+\]:[^:]*:\s*(.+)$/', $message, $matches) === 1) {
                $message = trim((string) ($matches[1] ?? ''));
            }
            if (stripos($message, 'Validation Error:') === 0) {
                $message = trim((string) substr($message, strlen('Validation Error:')));
            }
            if ($message === '') {
                $message = 'Unable to create customer account.';
            }
            validationFail(['register' => $message]);
        } catch (Throwable $e) {
            rollbackIfInTransaction($pdo);
            sendJson(500, [
                'ok' => false,
                'message' => 'Unable to create customer account.',
                'errors' => ['register' => isDebugMode($env) ? $e->getMessage() : 'Please try again.'],
            ]);
        }

        if (!is_array($customer)) {
            sendJson(500, [
                'ok' => false,
                'message' => 'Account registration completed, but profile lookup failed.',
                'errors' => ['register' => 'Please try logging in.'],
            ]);
        }

        $customerPublicId = asString($customer['public_id'] ?? $publicId);
        $fullName = trim(
            asString($customer['first_name'] ?? $firstName) . ' ' . asString($customer['last_name'] ?? $lastName)
        );
        $resolvedEmail = asString($customer['email_address'] ?? '');
        $resolvedContact = asString($customer['contact_number'] ?? '');
        $identityValue = $resolvedEmail !== '' ? $resolvedEmail : $resolvedContact;

        $token = createToken([
            'sub' => $customerPublicId,
            'role' => 'customer',
            'name' => $fullName,
            'email' => $identityValue,
        ], tokenSecret($env), 60 * 60 * 24);

        sendJson(201, [
            'ok' => true,
            'message' => 'Account created successfully.',
            'data' => [
                'token' => $token,
                'user' => [
                    'public_id' => $customerPublicId,
                    'name' => $fullName,
                    'email' => $identityValue,
                    'role' => 'customer',
                ],
            ],
        ]);
    }

    if ($path === '/auth/customer/login' && $method === 'POST') {
        $body = requestBody($env);
        $loginMethod = strtolower(asString($body['login_method'] ?? ''));
        $identifier = asString($body['identifier'] ?? '');
        $email = strtolower(asString($body['email'] ?? ''));
        $contactRaw = asString($body['contact_number'] ?? '');
        $password = (string) ($body['password'] ?? '');
        $reactivateRequested = parseBoolean($body['reactivate'] ?? null) === true;

        if ($identifier !== '') {
            if ($loginMethod === 'contact_number') {
                $contactRaw = $identifier;
            } else {
                $email = strtolower($identifier);
            }
        }
        if ($loginMethod === '') {
            $loginMethod = $contactRaw !== '' && $email === '' ? 'contact_number' : 'email';
        }
        if (!in_array($loginMethod, ['email', 'contact_number'], true)) {
            validationFail(['login_method' => 'Login method must be email or contact_number.']);
        }
        if ($password === '') {
            validationFail(['password' => 'Please provide your password.']);
        }

        $loginLabel = $loginMethod === 'contact_number' ? 'contact number' : 'email';
        $rateIdentifier = '';
        $candidates = [];
        if ($loginMethod === 'email') {
            if (!validateEmail($email)) {
                validationFail(['email' => 'Please provide a valid email address.']);
            }
            $rateIdentifier = $email;
            $candidates = getCustomersByEmailAny($pdo, $email);
        } else {
            $contactNormalized = normalizePhoneNumber($contactRaw);
            if ($contactNormalized === null) {
                validationFail(['contact_number' => 'Please provide a valid contact number (example: 09171234567 or 639171234567).']);
            }
            $rateIdentifier = $contactNormalized;
            $candidates = getCustomersByContactAny($pdo, $contactNormalized);
        }

        enforceAuthRateLimit($env, 'customer_login', $rateIdentifier !== '' ? $rateIdentifier : clientIp());

        if ($candidates === []) {
            sendJson(401, [
                'ok' => false,
                'message' => 'Login failed.',
                'errors' => ['login' => 'Invalid ' . $loginLabel . ' or password.'],
            ]);
        }

        $customer = null;
        $walkInOnlyMatch = null;
        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            $candidatePasswordHash = asString($candidate['password_hash'] ?? '');
            if ($candidatePasswordHash === '') {
                if (
                    $walkInOnlyMatch === null
                    && asString($candidate['customer_type'] ?? '') === 'Walk-in'
                    && asString($candidate['status'] ?? '') === 'Active'
                    && asString($candidate['deleted_at'] ?? '') === ''
                ) {
                    $walkInOnlyMatch = $candidate;
                }
                continue;
            }
            if (password_verify($password, $candidatePasswordHash)) {
                $customer = $candidate;
                break;
            }
        }

        if ($customer === null && is_array($walkInOnlyMatch)) {
            sendJson(403, [
                'ok' => false,
                'message' => 'Account is not registered for online login yet.',
                'errors' => [
                    'login' => 'This customer record is still walk-in only. Complete registration to set a password.',
                ],
            ]);
        }

        if ($customer === null) {
            sendJson(401, [
                'ok' => false,
                'message' => 'Login failed.',
                'errors' => ['login' => 'Invalid ' . $loginLabel . ' or password.'],
            ]);
        }

        $customerId = (int) ($customer['customerID'] ?? 0);
        $customerStatus = asString($customer['status'] ?? 'Active');
        $deletedAt = asString($customer['deleted_at'] ?? '');

        if ($deletedAt !== '') {
            if ($customerStatus === 'Merged') {
                sendJson(403, [
                    'ok' => false,
                    'message' => 'This duplicate account was merged into your main profile to keep your order history together. Please log in using your active account.',
                    'errors' => [
                        'login' => 'This duplicate account was merged into your main profile to keep your order history together. Please log in using your active account.',
                    ],
                ]);
            }

            if ($customerStatus === 'Deleted_by_User') {
                $deletedTimestamp = strtotime($deletedAt);
                $recoveryWindowSeconds = 7 * 24 * 60 * 60;
                if ($deletedTimestamp === false || (time() - $deletedTimestamp) > $recoveryWindowSeconds) {
                    sendJson(403, [
                        'ok' => false,
                        'message' => 'This account can no longer be reactivated because the recovery window has expired.',
                        'errors' => [
                            'login' => 'This account can no longer be reactivated because the recovery window has expired.',
                        ],
                    ]);
                }

                if (!$reactivateRequested) {
                    sendJson(403, [
                        'ok' => false,
                        'message' => 'Your account is deactivated.',
                        'errors' => [
                            'reactivation_required' => 'Your account is deactivated. Do you want to set it active again?',
                        ],
                    ]);
                }

                try {
                    $reactivate = $pdo->prepare('CALL customer_reactivate(:public_id)');
                    $reactivate->execute(['public_id' => (string) ($customer['public_id'] ?? '')]);
                    $reactivate->closeCursor();
                } catch (PDOException $e) {
                    $message = trim($e->getMessage());
                    if (preg_match('/SQLSTATE\\[[^\\]]+\\]:[^:]*:\\s*(.+)$/', $message, $matches) === 1) {
                        $message = trim((string) ($matches[1] ?? ''));
                    }
                    sendJson(403, [
                        'ok' => false,
                        'message' => $message !== '' ? $message : 'Account reactivation failed.',
                        'errors' => [
                            'login' => $message !== '' ? $message : 'Account reactivation failed.',
                        ],
                    ]);
                }

                $reactivated = getCustomerByPublicIdAny($pdo, asString($customer['public_id'] ?? ''));
                if (!is_array($reactivated)) {
                    sendJson(500, [
                        'ok' => false,
                        'message' => 'Account reactivation succeeded but profile lookup failed.',
                    ]);
                }
                $customer = $reactivated;
                $customerStatus = asString($customer['status'] ?? 'Active');
                $deletedAt = asString($customer['deleted_at'] ?? '');
            }
        }

        if ($customerStatus !== 'Active' || $deletedAt !== '') {
            sendJson(403, [
                'ok' => false,
                'message' => 'Your account is inactive.',
                'errors' => ['login' => 'Please contact support for account assistance.'],
            ]);
        }

        $fullName = trim(((string) ($customer['first_name'] ?? '')) . ' ' . ((string) ($customer['last_name'] ?? '')));
        $identityValue = asString($customer['email_address'] ?? '');
        if ($identityValue === '') {
            $identityValue = asString($customer['contact_number'] ?? '');
        }
        $token = createToken([
            'sub' => (string) $customer['public_id'],
            'role' => 'customer',
            'name' => $fullName,
            'email' => $identityValue,
        ], tokenSecret($env), 60 * 60 * 24);

        sendJson(200, [
            'ok' => true,
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'user' => [
                    'public_id' => (string) $customer['public_id'],
                    'name' => $fullName,
                    'email' => $identityValue,
                    'role' => 'customer',
                ],
            ],
        ]);
    }

    if ($path === '/auth/admin/login' && $method === 'POST') {
        $body = requestBody($env);
        $email = strtolower(asString($body['email'] ?? ''));
        $password = (string) ($body['password'] ?? '');
        enforceAuthRateLimit($env, 'admin_login', $email !== '' ? $email : clientIp());

        if (!validateEmail($email) || $password === '') {
            validationFail(['login' => 'Please provide admin email and password.']);
        }

        $employee = getEmployeeByEmail($pdo, $email);
        if ($employee === null || !password_verify($password, (string) ($employee['password_hash'] ?? ''))) {
            sendJson(401, [
                'ok' => false,
                'message' => 'Login failed.',
                'errors' => ['login' => 'Invalid admin credentials.'],
            ]);
        }

        if (strtolower((string) ($employee['employee_status'] ?? 'inactive')) !== 'active') {
            sendJson(403, [
                'ok' => false,
                'message' => 'Your admin account is inactive.',
                'errors' => ['login' => 'Please contact your system owner.'],
            ]);
        }

        $fullName = trim(((string) ($employee['first_name'] ?? '')) . ' ' . ((string) ($employee['last_name'] ?? '')));
        $employeeRole = strtolower(asString($employee['employee_role'] ?? ''));
        $authRole = $employeeRole === 'admin' ? 'admin' : 'employee';
        $token = createToken([
            'sub' => (string) $employee['public_id'],
            'role' => $authRole,
            'name' => $fullName,
            'email' => (string) $employee['email_address'],
        ], tokenSecret($env), 60 * 60 * 12);

        sendJson(200, [
            'ok' => true,
            'message' => 'Admin login successful.',
            'data' => [
                'token' => $token,
                'user' => [
                    'public_id' => (string) $employee['public_id'],
                    'name' => $fullName,
                    'email' => (string) $employee['email_address'],
                    'role' => $authRole,
                ],
            ],
        ]);
    }

    if ($path === '/auth/me' && $method === 'GET') {
        $claims = requireAuth($env, ['customer', 'admin', 'employee']);
        sendJson(200, [
            'ok' => true,
            'data' => [
                'user' => [
                    'public_id' => (string) ($claims['sub'] ?? ''),
                    'name' => (string) ($claims['name'] ?? ''),
                    'email' => (string) ($claims['email'] ?? ''),
                    'role' => (string) ($claims['role'] ?? ''),
                ],
            ],
        ]);
    }

