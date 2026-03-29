<?php 
session_start();
include 'db_con; 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Lebanon Vibes</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<div class="form-container">
    <img src="cedar.svg" alt="Cedar" width="60">
    <h2>Create Your Account</h2>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $errors = [];

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password];

    $phone = trim($_POST["phone"]);
    $type = $_POST["user_type"];
    $date = date('Y-m-d H:i:s');

    // ----------- VALIDATION -------------
    if ($username === '') $errors[] = 'Username is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) 
        $errors[] = 'Valid email is required.';
    if (strlen($password_raw) < 6) 
        $errors[] = 'Password must be at least 6 characters.';
    if ($phone === '') 
        $errors[] = 'Phone number is required.';

    // password rule: A-Z + 0-9 only (6 to 8 chars)
    if (!preg_match('/^[A-Z0-9]{6,8}$/', $password_raw)) {
        $errors[] = "Password must be 6 to 8 characters, only uppercase letters (A-Z) and digits (0-9).";
    }

    // ------------------------------------
    // Continue only if no validation errors
    if (empty($errors)) {

        // Check if email exists
        $checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email=?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        // Check if username exists
        $checkName = $conn->prepare("SELECT user_id FROM users WHERE name=?");
        $checkName->bind_param("s", $username);
        $checkName->execute();
        $checkName->store_result();

        if ($checkEmail->num_rows > 0) {
            $errors[] = "Email already registered.";

        } elseif ($checkName->num_rows > 0) {
            $errors[] = "Username already taken.";

        } else {

            // Insert new account
            $password_hash = password_hash($password_raw, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO users (name,email,password,phone_nb,user_type,registration_date) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $email, $password_hash, $phone, $type, $date);

            if ($stmt->execute()) {

                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['name'] = $username;
                $_SESSION['user_type'] = $type;

                if ($type === 'business') {
                    header("Location:my_business.php");
                } else {
                    header("Location:login_process.php");
                }
                exit();

            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
        }
    }
}
?>

<!-- Show Errors -->
<?php
if (!empty($errors)) {
    echo "<div style='color:red; margin-bottom:10px;'>";
    foreach ($errors as $er) echo "- $er<br>";
    echo "</div>";
}
?>

<!-- ======================
    NEW FORM (Final)
======================= -->
<form method="post">

    <label>Username</label>
    <input name="username" 
           value="<?= htmlspecialchars($username ?? '') ?>" 
           required>

    <label>Email</label>
    <input name="email" 
           type="email" 
           value="<?= htmlspecialchars($email ?? '') ?>" 
           required>

    <label>Password</label>
    <input name="password" 
           type="password" 
           placeholder="At least 6 chars, include A-Z and 0-9" 
           required>

    <label>Phone</label>
    <input name="phone" 
           value="<?= htmlspecialchars($phone ?? '') ?>" 
           required>

    <label>User Type</label>
    <select name="user_type" required>
        <option value="normal">Normal user</option>
        <option value="business">Business owner</option>
    </select>

    <button type="submit">Register</button>
</form>

</div>
</body>
</html>