<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$productID = (int)($_GET['productID'] ?? 0);
if ($productID <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'productID is required']);
    exit;
}

$pdo = db();

// Average + count (approved only)
$stmtStats = $pdo->prepare("
    SELECT 
      COUNT(*) AS review_count,
      COALESCE(AVG(rating), 0) AS avg_rating
    FROM product_review
    WHERE productID = ? AND review_status = 'Approved'
");
$stmtStats->execute([$productID]);
$stats = $stmtStats->fetch();

// Reviews (approved only)
$stmt = $pdo->prepare("
    SELECT 
      pr.reviewID,
      pr.public_id,
      pr.rating,
      pr.review_text,
      pr.created_at,
      c.public_id AS customer_public_id,
      c.first_name,
      c.last_name
    FROM product_review pr
    JOIN customer c ON c.customerID = pr.customerID
    WHERE pr.productID = ? AND pr.review_status = 'Approved'
    ORDER BY pr.reviewID DESC
");
$stmt->execute([$productID]);

echo json_encode([
    'success' => true,
    'data' => [
        'stats' => $stats,
        'reviews' => $stmt->fetchAll()
    ]
]);
