<?php
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "techzone_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit;
}

// Kinukuha ang data mula sa Forensic View na ginawa natin
$sql = "SELECT * FROM vw_admin_payment_dashboard";
$result = $conn->query($sql);

$logs = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

echo json_encode($logs);
$conn->close();