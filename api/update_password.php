<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require 'config.php'; // Side-by-side in the api folder

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? '';
    $new_password = $data['password'] ?? '';

    if (empty($email) || empty($new_password)) {
        echo json_encode(["success" => false, "message" => "Missing data."]);
        exit;
    }

    try {
        // 1. Hash the new password using BCRYPT
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // 2. Update the password_hash for the specific email
        $stmt = $pdo->prepare("UPDATE employee SET password_hash = ? WHERE email_address = ?");
        $result = $stmt->execute([$hashed_password, $email]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "success" => true,
                "message" => "Password updated successfully!"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Email not found or password is the same as before."
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}
?>