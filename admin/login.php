<?php 
require_once __DIR__ . '/../server/config/app.php'; ?>
<?php
require_once __DIR__ . '/../server/auth/guards.php';
require_admin();
?>

<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Login</title></head>
<body>
  <h2>Admin Login</h2>

  <form method="POST" action="<?= BASE_URL ?>/server/auth/admin_login.php">
    <input name="username" placeholder="Username" required><br>
    <input name="password" type="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
  </form>
</body>
</html>
