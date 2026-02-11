<?php
require '../config/db.php';

if($_SERVER['REQUEST_METHOD']=="POST"){
   $pdo->prepare("CALL record_new_supplier(?,?,?)")
       ->execute([
         $_POST['name'],
         $_POST['email'],
         $_POST['phone']
       ]);
}

if($_SERVER['REQUEST_METHOD']=="DELETE"){
   $pdo->prepare("CALL deactivate_supplier(?)")
       ->execute([$_GET['id']]);
}
