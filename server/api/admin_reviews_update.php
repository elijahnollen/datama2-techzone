<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$pdo = db();

$reviewID = (int)($_POST['reviewID'] ?? 0);
$status   = (string)($_POST['review_status'] ?? '');

$allowed = ['Approved','Rejected','Hidden','Pending'];

if ($reviewID <= 0) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'error'=>'reviewID is required']);
    exit;
}
if (!in_array($status, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'error'=>'Invalid review_status']);
    exit;
}

$stmt = $pdo->prepare("UPDATE product_review SET review_status = ?, updated_at = NOW() WHERE reviewID = ?");
$stmt->execute([$status, $reviewID]);

echo json_encode(['success'=>true, 'message'=>'Review updated']);
