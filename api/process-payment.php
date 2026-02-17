<?php
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "techzone_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database Connection Failed"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$customerID = $input['customerID'] ?? 0;
$employeeID = $input['employeeID'] ?? 1; 
$amount     = $input['amount'] ?? 0;
$method     = $input['method'] ?? 'Credit Card';
$cardNumber = $input['cardNumber'] ?? '';

$lastFour = substr($cardNumber, -4);

if ($lastFour === '4242') {
    $txnRef = "TZ-PYMT-" . strtoupper(bin2hex(random_bytes(4)));

    $stmt = $conn->prepare("CALL process_simulated_payment(?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidsss", $customerID, $employeeID, $amount, $method, $txnRef, $lastFour);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "transaction_id" => $txnRef,
            "message" => "Payment successful and audited."
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Internal SQL Error"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid Test Card. Use 4242."]);
}

$conn->close();