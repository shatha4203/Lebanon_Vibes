 <?php
session_start();
 include "db_connection.php";

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

   
    if ($username === '') $errors[] = 'Username is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($phone === '') $errors[] = 'Phone number is required.';

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    }

 if (empty($errors)) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE `email`= ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($exists) {
        $errors[] = 'Email already registered. Please use another email.';
    } else {
        // proceed to insert user (the rest of your registration logic)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $user_type = 'business_owner';

        $stmt = $conn->prepare("INSERT INTO `users`( `name`, `email`, `password`, `phone_nb`, `registration_date`, `user_type`)
         VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("sssss", $username, $email, $password_hash, $phone, $user_type);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_type'] = $user_type;
            $_SESSION['username'] = $username;

            header("Location: add_business.php");
            exit;
        } else {
            $errors[] = 'Registration failed: ' . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    }
 }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register Business Owner</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f5f7fa;
        margin: 0;
        padding: 40px;
    }
    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }
    form {
        background: white;
        padding: 25px;
        border-radius: 10px;
        max-width: 450px;
        margin: 0 auto;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    label {
        display: block;
        margin-top: 12px;
        font-weight: bold;
        color: #444;
    }
    input {
        width: 100%;
        padding: 8px;
        margin-top: 4px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 15px;
    }
    button {
        margin-top: 20px;
        padding: 10px 15px;
        border: none;
        background: #007bff;
        color: white;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
    }
    button:hover {
        background: #0056b3;
    }
    .error {
        color: #dc3545;
        background: #f8d7da;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    .success {
        color: #155724;
        background: #d4edda;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
</style>
</head>
<body>

<h1>Register as Business Owner</h1>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $e): ?>
        <div class="error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="post" action="">
    <label>Username*</label>
    <input name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>

    <label>Email*</label>
    <input name="email" type="email" value="<?= htmlspecialchars($email ?? '') ?>" required>

    <label>Password*</label>
    <input name="password" type="password" placeholder="At least 6 chars, include A-Z and 0-9" required>

    <label>Phone*</label>
    <input name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" required>

    <button type="submit">Register</button>
</form>

</body>
</html>