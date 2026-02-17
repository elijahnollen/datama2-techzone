<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/app.php';

header('Content-Type: application/json');

$pdo = db();
$customerID = PROTOTYPE_CUSTOMER_ID;

$productID = (int)($_POST['productID'] ?? 0);
$rating    = (int)($_POST['rating'] ?? 0);
$review    = trim((string)($_POST['review_text'] ?? ''));

if ($productID <= 0) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'error'=>'productID is required']);
    exit;
}
if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'error'=>'rating must be 1 to 5']);
    exit;
}
if (strlen($review) > 1000) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'error'=>'review_text too long (max 1000 chars)']);
    exit;
}

try {
    // Ensure product is active
    $stmtP = $pdo->prepare("SELECT productID FROM product WHERE productID = ? AND is_active = 1 LIMIT 1");
    $stmtP->execute([$productID]);
    if (!$stmtP->fetch()) {
        throw new Exception("Product not found or inactive.");
    }

    // Generate public id like your other modules
    $publicId = 'RV-' . strtoupper(bin2hex(random_bytes(4)));

    // Insert or update (1 review per customer per product)
    // If exists, update it and set Pending again (so admin can re-moderate)
    $stmt = $pdo->prepare("
        INSERT INTO product_review (public_id, rating, review_text, review_status, productID, customerID)
        VALUES (?, ?, ?, 'Pending', ?, ?)
        ON DUPLICATE KEY UPDATE
          rating = VALUES(rating),
          review_text = VALUES(review_text),
          review_status = 'Pending',
          updated_at = NOW()
    ");

    $stmt->execute([$publicId, $rating, ($review === '' ? null : $review), $productID, $customerID]);

    echo json_encode(['success'=>true, 'message'=>'Review submitted (Pending moderation).']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}
