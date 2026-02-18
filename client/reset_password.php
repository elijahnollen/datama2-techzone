<?php
$token = $_GET['token'] ?? '';
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Reset Password</title></head>
<body>
  <h2>Reset Password</h2>

  <?php if ($token === ''): ?>
    <p style="color:red;">Missing token.</p>
    <p><a href="/client/forgot_password.php">Try again</a></p>
  <?php else: ?>
    <form method="POST" action="../server/auth/reset_password_submit.php">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <input name="new_password" type="password" placeholder="New password" required><br>
      <input name="new_password2" type="password" placeholder="Confirm new password" required><br><br>
      <button type="submit">Update Password</button>
    </form>
  <?php endif; ?>

</body>
</html>
