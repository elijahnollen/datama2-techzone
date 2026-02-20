<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techzone_new_inventory';

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Please provide both email and password"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT public_id, first_name, password_hash, is_active FROM customer WHERE email_address = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if ($user['is_active'] == 0) {
            echo json_encode(["success" => false, "message" => "Account is deactivated. Please contact support."]);
            exit;
        }

        if (password_verify($password, $user['password_hash'])) {
            echo json_encode([
                "success" => true,
                "message" => "Welcome back, " . $user['first_name'],
                "user" => [
                    "id" => $user['public_id'],
                    "name" => $user['first_name']
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Incorrect password"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No account found with that email address"]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error"]);
}

$conn->close();
?>