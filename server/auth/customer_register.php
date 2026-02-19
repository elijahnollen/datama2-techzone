<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/captcha.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/client/register.php');
    exit;
}

// --- Sanitize inputs ---
$firstName = trim(strip_tags($_POST['first_name'] ?? ''));
$lastName  = trim(strip_tags($_POST['last_name'] ?? ''));
$email     = trim($_POST['email'] ?? '');
$phone     = trim(strip_tags($_POST['phone'] ?? ''));
$street    = trim(strip_tags($_POST['street_address'] ?? ''));
$barangay  = trim(strip_tags($_POST['barangay'] ?? ''));
$city      = trim(strip_tags($_POST['city'] ?? ''));
$province  = trim(strip_tags($_POST['province'] ?? ''));
$zipCode   = trim(strip_tags($_POST['zip_code'] ?? ''));

$password  = (string)($_POST['password'] ?? '');
$password2 = (string)($_POST['password2'] ?? '');
$captchaAns = (string)($_POST['captcha'] ?? '');

// --- Layer 1: PHP validation ---
$errors = [];

if (!captcha_verify($captchaAns)) {
    $errors[] = "Captcha is incorrect. Please try again.";
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format. Please enter a valid email address.";
}

if (!empty($zipCode) && !preg_match('/^[0-9]{4}$/', $zipCode)) {
    $errors[] = "Invalid Zip Code format. Please use 4 digits.";
}

if (empty($street)) {
    $errors[] = "Street address is required for e-commerce delivery.";
}

if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}

if ($password !== $password2) {
    $errors[] = "Passwords do not match.";
}

if (!empty($errors)) {
    echo "<h2>Registration Failed</h2>";
    foreach ($errors as $error) {
        echo "<p style='color:red;'>• " . htmlspecialchars($error) . "</p>";
    }
    echo "<p><a href='" . BASE_URL . "/client/register.php'>Back</a></p>";
    exit;
}

// --- Layer 2: DB interaction with public ID generation + retry ---
$pdo = db();

$inserted = false;
$attempts = 0;
$maxRetries = 5;

while (!$inserted && $attempts < $maxRetries) {
    try {
        $publicId = 'CS-' . strtoupper(bin2hex(random_bytes(4)));

        $stmt = $pdo->prepare("CALL record_new_customer(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $publicId,
            $firstName,
            $lastName,
            $email,
            $phone,
            $street,
            $barangay,
            $city,
            $province,
            $zipCode
        ]);

        // Set password_hash + mark as Online
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $upd = $pdo->prepare("UPDATE customer SET password_hash = ?, customer_type='Online' WHERE public_id = ? LIMIT 1");
        $upd->execute([$hash, $publicId]);

        echo "<h2>Registration Successful!</h2>";
        echo "Welcome to TechZone. Your Customer ID is: <strong>" . htmlspecialchars($publicId) . "</strong>";
        echo "<p><a href='" . BASE_URL . "/client/login.php'>Go to Login</a></p>";

        $inserted = true;

    } catch (PDOException $e) {
        // MySQL duplicate key (public_id collision) => retry
        if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
            $attempts++;
            continue;
        }

        echo "<h2>Registration Failed</h2>";
        echo "<p style='color:red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><a href='" . BASE_URL . "/client/register.php'>Back</a></p>";
        break;
    }
}
