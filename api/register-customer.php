<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

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

// Mapping frontend names to MySQL column names
$first_name = $data['firstName'] ?? '';
$middle_name = $data['middleName'] ?? null;
$last_name = $data['lastName'] ?? '';
$email = $data['email'] ?? '';
$contact = $data['contactNumber'] ?? '';
$street = $data['streetAddress'] ?? '';
$barangay = $data['barangay'] ?? 'Default Barangay'; // Required field
$city = $data['city'] ?? ''; 
$province = $data['province'] ?? '';
$zip = $data['zipCode'] ?? '';
$password = $data['password'] ?? '';

if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($city)) {
    echo json_encode(["success" => false, "message" => "Required fields are missing."]);
    exit;
}

// Check for existing email
$check = $conn->prepare("SELECT customerID FROM customer WHERE email_address = ?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already registered."]);
    exit;
}

$public_id = "CUST-" . strtoupper(bin2hex(random_bytes(4)));
$hash = password_hash($password, PASSWORD_DEFAULT);
$type = 'Online'; // ENUM match

$sql = "INSERT INTO customer (public_id, first_name, middle_name, last_name, customer_type, password_hash, email_address, contact_number, street_address, barangay, province, city_municipality, zip_code) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssss", $public_id, $first_name, $middle_name, $last_name, $type, $hash, $email, $contact, $street, $barangay, $province, $city, $zip);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Account created!", "user" => ["id" => $public_id, "name" => $first_name]]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
}
?>