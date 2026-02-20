<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Ensure this file is in C:\xampp1\htdocs\api\
require 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? '';
    $input_password = $data['password'] ?? '';

    try {
        // Removed 'AND employee_role = Admin' so Ricardo (Store Manager) can log in
      $stmt = $pdo->prepare("SELECT employeeID, first_name, last_name, password_hash, employee_role, employee_status FROM employee WHERE email_address = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($input_password, $user['password_hash'])) {
    echo json_encode([
        "success" => true, 
        "message" => "Login successful",
        "role" => $user['employee_role'],
        "employeeID" => $user['employeeID'],
        // This line (Line 33) was failing because the keys didn't exist
        "name" => $user['first_name'] . " " . $user['last_name']
            ]);
        } else {
            // This triggers if the email is wrong, password is wrong, or password_hash is NULL
            echo json_encode(["success" => false, "message" => "Invalid email or password"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error occurred"]);
    }
}
?>