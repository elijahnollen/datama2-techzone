<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Login</title></head>
<body>
  <h2>Admin Login</h2>

  <form method="POST" action="/server/auth/admin_login.php">
    <input name="username" placeholder="Username" required><br>
    <input name="password" type="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
  </form>
</body>
</html>
