<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Registration</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="register-container">
    <h2>User Registration</h2>

    <form action="register_process.php" method="POST">
      <input type="hidden" name="user_type" value="user">

      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>

      <button type="submit">Register as User</button>
    </form>
  </div>
</body>

</html>
