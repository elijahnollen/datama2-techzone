<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "techzone_new_inventory");

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

$sql = "SELECT 
            ri.return_itemID AS id, 
            ri.sale_itemID AS orderId,
            CONCAT(c.first_name, ' ', c.last_name) AS customer, 
            p.product_name AS product, 
            ri.reason,
            (si.price_at_sale * ri.return_quantity) AS amount, 
            ri.return_status AS status,
            CURRENT_DATE AS date
        FROM return_item ri
        LEFT JOIN sale_item si ON ri.sale_itemID = si.sale_itemID
        LEFT JOIN product p ON si.productID = p.productID
        LEFT JOIN sale s ON si.saleID = s.saleID
        LEFT JOIN customer c ON s.customerID = c.customerID";

$result = $conn->query($sql);

if ($result) {
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
} else {
    echo json_encode(["error" => $conn->error]);
}

$conn->close();
?>