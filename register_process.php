<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name             = trim($_POST['name']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role             = $_POST['role']; // user or business

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = "⚠️ Passwords do not match.";
        header("Location: register.php");
        exit;
    }

    // heck if email already exists
    $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
    if (!$check) {
    die("Prepare failed: " . $conn->error);
}
    $check->bind_param("s", $email);
    $check->execute();
    $check ->store_result(); 

    if ($check_result->num_rows > 0) {
        $_SESSION['register_error'] = "⚠️ This email is already registered.";
        header("Location: login.php");
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        $_SESSION['register_success'] = "✅ Registration successful! You can now log in.";
        header("Location: register.php");
    } else {
        $_SESSION['register_error'] = "❌ Something went wrong. Please try again.";
        header("Location: register.php");
    }

    $stmt->close();
      $check->close();
}
?>
