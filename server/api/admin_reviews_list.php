<?php
require_once __DIR__ . '/../auth/guards.php';
require_admin();
<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$pdo = db();
$status = $_GET['status'] ?? ''; // optional filter

$params = [];
$where = "";
if ($status !== '') {
    $where = "WHERE pr.review_status = ?";
    $params[] = $status;
}

$stmt = $pdo->prepare("
    SELECT 
      pr.reviewID, pr.public_id, pr.rating, pr.review_text, pr.review_status, pr.created_at, pr.updated_at,
      p.productID, p.product_name,
      c.customerID, c.public_id AS customer_public_id, c.first_name, c.last_name
    FROM product_review pr
    JOIN product p ON p.productID = pr.productID
    JOIN customer c ON c.customerID = pr.customerID
    $where
    ORDER BY pr.reviewID DESC
");
$stmt->execute($params);

echo json_encode(['success'=>true, 'data'=>$stmt->fetchAll()]);
