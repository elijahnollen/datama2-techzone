<?php
require '../config/db.php';

if($_SERVER['REQUEST_METHOD']=="POST"){
    $stmt = $pdo->prepare(
        "INSERT INTO reviews(productID,rating,comment)
         VALUES(?,?,?)"
    );
    $stmt->execute([
        $_POST['productID'],
        $_POST['rating'],
        $_POST['comment']
    ]);
}

if($_SERVER['REQUEST_METHOD']=="GET"){
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE productID=?");
    $stmt->execute([$_GET['productID']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
