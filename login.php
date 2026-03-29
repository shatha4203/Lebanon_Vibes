<?php
session_start();
include "db_connection.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $password = $_POST['password'];

    $stmt = $conn->prepare(" SELECT user_id,name,password,user_type FROM users WHERE name = ? ");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];

              
            if ($user['user_type'] === 'admin') {
                header("Location:/LebanonVibes/web_admin.php");
            } elseif ($user['user_type'] === 'business') {
                header("Location:my_business.php");
            } else {
                header("Location:profile.php");
            }
            exit;
        } else {
            $message = "❌ Incorrect password.";
        }
    } else {
        $message = "⚠ Username not found.";
    }
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Lebanon Vibes</title>
    <link rel="stylesheet"  href="main.css">   
</head>
<body>

    <nav class="navbar">
     <a href="index.php" class="logo">🌿 Lebanon Vibes</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="register.php">Register</a>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-box">

            <h2>Welcome Back</h2>
            
            <?php if ($message): ?>
                <div class="error-message">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="name" placeholder="Enter your username" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>
        </div>
    </div>

</body>
</html>