<?php
require_once __DIR__ . '/../server/config/app.php';
require_once __DIR__ . '/../server/lib/session.php';
require_once __DIR__ . '/../server/auth/captcha.php';
$captcha = captcha_generate();
?>

$captcha = captcha_generate();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Register</title></head>
<body>
  <h2>Customer Register</h2>

  <form method="POST" action="<?= BASE_URL ?>/server/auth/customer_register.php">
    <input name="first_name" placeholder="First name" required><br>
    <input name="last_name" placeholder="Last name" required><br>
    <input name="email" placeholder="Email (optional)"><br>
    <input name="phone" placeholder="Contact number (optional)"><br>
    <input name="street_address" placeholder="Street address" required><br>
    <input name="barangay" placeholder="Barangay" required><br>
    <input name="city" placeholder="City/Municipality" required><br>
    <input name="province" placeholder="Province"><br>
    <input name="zip_code" placeholder="Zip code (4 digits)"><br>

    <input name="password" type="password" placeholder="Password" required><br>
    <input name="password2" type="password" placeholder="Confirm Password" required><br>

    <p>Captcha: <strong><?= htmlspecialchars($captcha['question']) ?></strong></p>
    <input name="captcha" placeholder="Answer" required><br><br>

    <button type="submit">Register</button>
  </form>
</body>
</html>
