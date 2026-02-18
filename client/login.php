<?php require_once __DIR__ . '/../server/config/app.php'; ?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login</title></head>
<body>
  <h2>Customer Login</h2>

  <form method="POST" action="<?= BASE_URL ?>/server/auth/customer_login.php">
    <input name="email" placeholder="Email" required><br>
    <input name="password" type="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
    <p><a href="/client/forgot_password.php">Forgot password?</a></p>
  </form>

  <p><a href="/client/register.php">Register</a></p>
</body>
</html>
