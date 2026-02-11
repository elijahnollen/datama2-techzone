<?php
require '../config/db.php';

if($_SERVER['REQUEST_METHOD']=="POST"){
   $stmt=$pdo->prepare(
     "CALL record_new_product(?,?,?)"
   );
   $stmt->execute([
      $_POST['name'],
      $_POST['qty'],
      $_POST['price']
   ]);
}

if($_SERVER['REQUEST_METHOD']=="DELETE"){
   $pdo->prepare("CALL deactivate_product(?)")
       ->execute([$_GET['id']]);
}
