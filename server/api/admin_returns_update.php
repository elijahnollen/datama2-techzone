<?php require_once __DIR__ . '/../server/auth/guards.php'; require_admin(); ?>
<?php
require __DIR__ . "/../config/database.php";

header("Content-Type: application/json");

$pdo = db();
$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
  http_response_code(400);
  echo json_encode(["success"=>false, "error"=>"Invalid JSON"]);
  exit;
}

$returnID = (int)($input['returnID'] ?? 0);
$refund_amount = $input['refund_amount'] ?? null;
$items = $input['items'] ?? [];

if ($returnID <= 0 || !is_array($items)) {
  http_response_code(400);
  echo json_encode(["success"=>false, "error"=>"returnID and items[] required"]);
  exit;
}

$allowed = ['Refunded','Replaced','Store Credit'];

try {
  $pdo->beginTransaction();

  // update header refund amount (nullable)
  if ($refund_amount === '' || $refund_amount === null) $refund_amount = null;
  else $refund_amount = (float)$refund_amount;

  $stmtHdr = $pdo->prepare("UPDATE return_transaction SET refund_amount = ? WHERE returnID = ?");
  $stmtHdr->execute([$refund_amount, $returnID]);

  // update items
  $stmtUpd = $pdo->prepare("
    UPDATE return_item
    SET return_status = ?, notes = ?
    WHERE return_itemID = ? AND returnID = ?
  ");

  foreach ($items as $it) {
    $return_itemID = (int)($it['return_itemID'] ?? 0);
    $status = (string)($it['return_status'] ?? '');
    $notes = $it['notes'] ?? null;

    if ($return_itemID <= 0) continue;
    if (!in_array($status, $allowed, true)) throw new Exception("Invalid status");

    $stmtUpd->execute([$status, ($notes === '' ? null : $notes), $return_itemID, $returnID]);
  }

  $pdo->commit();
  echo json_encode(["success"=>true, "data"=>["returnID"=>$returnID]]);
} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(400);
  echo json_encode(["success"=>false, "error"=>$e->getMessage()]);
}
