<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "techzone_new_inventory");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connection failed"]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && isset($data['status'])) {
    $id = $conn->real_escape_string($data['id']);
    $status = $conn->real_escape_string($data['status']);

    $sql = "UPDATE return_item 
            SET return_status = '$status' 
            WHERE return_itemID = '$id'";

    if ($conn->query($sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
}

$conn->close();
?>