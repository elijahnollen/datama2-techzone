<?php

declare(strict_types=1);

if ($path === '/catalog/products' && $method === 'GET') {
        $catalogManager = mongoManager($env);
        if (!$catalogManager) {
            sendJson(500, ['ok' => false, 'message' => 'Product catalog service is unavailable. MongoDB connection is required.']);
        }

        $catalog = loadProductCatalog($env);
        $reviewsManager = $catalogManager;
        $dbName = mongoDbName($env);
        $reviewsCollection = mongoCollectionName($env, 'product_reviews');

        $products = [];
        foreach ($catalog as $item) {
            $publicId = asString($item['product_public_id'] ?? '');
            $mysqlProduct = null;
            if ($publicId !== '') {
                $mysqlProduct = mysqlProductByPublicId($pdo, $publicId);
            }
            if ($mysqlProduct === null) {
                $fallbackId = (int) ($item['product_id'] ?? $item['productID'] ?? 0);
                if ($fallbackId > 0) {
                    $mysqlProduct = mysqlProductById($pdo, $fallbackId);
                    if (is_array($mysqlProduct)) {
                        $resolvedPublicId = asString($mysqlProduct['public_id'] ?? $publicId);
                        if ($resolvedPublicId !== '') {
                            $publicId = $resolvedPublicId;
                            $item['product_public_id'] = $resolvedPublicId;
                        }
                    }
                }
            }

            $reviews = [];
            $reviewStats = [
                'average_rating' => (float) ($item['reviews']['average_rating'] ?? 0),
                'total_reviews' => (int) ($item['reviews']['total_reviews'] ?? 0),
                'rating_sum' => (int) ($item['reviews']['rating_sum'] ?? 0),
            ];
            if ($reviewsManager && $publicId !== '') {
                try {
                    $reviewDocs = mongoFindMany($reviewsManager, $dbName, $reviewsCollection, ['product_public_id' => $publicId], ['sort' => ['created_at' => -1], 'limit' => 10]);
                    foreach ($reviewDocs as $review) {
                        $reviews[] = [
                            'user' => asString($review['customer_name'] ?? 'Anonymous'),
                            'rating' => (int) ($review['rating'] ?? 0),
                            'date' => asString($review['created_at']['$date'] ?? $review['created_at'] ?? ''),
                            'comment' => asString($review['comment'] ?? ''),
                        ];
                    }

                    $reviewStats = catalogReviewStats($env, $reviewsManager, $dbName, $publicId);
                } catch (Throwable) {
                    $reviews = [];
                }
            }

            $item['reviews'] = $reviewStats;
            $products[] = normalizeCatalogProduct($item, $mysqlProduct, $reviews);
        }

        sendJson(200, ['ok' => true, 'data' => ['products' => $products]]);
    }

if ($path === '/catalog/reviews' && $method === 'POST') {
        $claims = requireAuth($env, ['customer']);
        $body = requestBody($env);
        $productPublicId = asString($body['product_public_id'] ?? '');
        $rating = (int) ($body['rating'] ?? 0);
        $comment = asString($body['comment'] ?? '');

        $errors = [];
        if ($productPublicId === '') {
            $errors['product_public_id'] = 'Product is required.';
        } elseif (!isValidPublicId($productPublicId)) {
            $errors['product_public_id'] = 'Invalid product reference format.';
        }
        if ($rating < 1 || $rating > 5) {
            $errors['rating'] = 'Rating must be from 1 to 5.';
        }
        if ($comment === '') {
            $errors['comment'] = 'Review comment is required.';
        } else {
            $commentError = validateTextLength($comment, 'Review comment', 5, 1000);
            if ($commentError !== null) {
                $errors['comment'] = $commentError;
            }
        }
        if ($errors !== []) {
            validationFail($errors);
        }

        $customer = getCustomerByPublicId($pdo, (string) $claims['sub']);
        if ($customer === null) {
            sendJson(404, ['ok' => false, 'message' => 'Customer account not found.']);
        }
        $product = mysqlProductByPublicId($pdo, $productPublicId);
        if ($product === null) {
            sendJson(404, ['ok' => false, 'message' => 'Product not found.']);
        }

        if (!customerHasVerifiedPurchase($pdo, (int) $customer['customerID'], (int) $product['productID'])) {
            validationFail([
                'review' => 'You can submit a review only after a delivered/completed purchase of this product.',
            ]);
        }

        $manager = mongoManager($env);
        if (!$manager) {
            sendJson(500, ['ok' => false, 'message' => 'Review service is unavailable.']);
        }
        $dbName = mongoDbName($env);
        $reviewsCollection = mongoCollectionName($env, 'product_reviews');
        $catalogCollection = mongoCollectionName($env, 'product_catalog');

        $existingReview = mongoFindOne($manager, $dbName, $reviewsCollection, [
            'product_public_id' => $productPublicId,
            'customer_public_id' => (string) $claims['sub'],
        ]);
        if (is_array($existingReview)) {
            validationFail([
                'review' => 'You already submitted a review for this product.',
            ]);
        }

        mongoInsertOne($manager, $dbName, $reviewsCollection, [
            'product_public_id' => $productPublicId,
            'customer_public_id' => (string) $claims['sub'],
            'customer_name' => (string) ($claims['name'] ?? 'Customer'),
            'rating' => $rating,
            'comment' => $comment,
            'is_verified_purchase' => true,
            'created_at' => nowUtc(),
        ]);

        $stats = catalogReviewStats($env, $manager, $dbName, $productPublicId);
        mongoUpdateOne($manager, $dbName, $catalogCollection, ['product_public_id' => $productPublicId], [
            '$set' => [
                'reviews' => $stats,
            ],
        ], false);

        appendAuditLog($env, 'PRODUCT_LOG', 'REVIEW_SUBMITTED', [
            'actor_type' => 'CUSTOMER',
            'public_id' => (string) $claims['sub'],
        ], [
            'resource_type' => 'ProductCatalog',
            'resource_public_id' => $productPublicId,
        ]);

        sendJson(201, ['ok' => true, 'message' => 'Review submitted successfully.']);
    }

