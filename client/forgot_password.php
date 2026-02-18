<!doctype html>
<html>
<head><meta charset="utf-8"><title>Forgot Password</title></head>
<body>
  <h2>Forgot Password</h2>

  <form method="POST" action="../server/auth/forgot_password_request.php">
    <input name="email" placeholder="Enter your email" required><br><br>
    <button type="submit">Send Reset Link</button>
  </form>

  <p><a href="/client/login.php">Back to Login</a></p>
</body>
</html>
