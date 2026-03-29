<?php
session_start();
include 'db_connection.php'; 

function clean_phone($x) {
    return preg_replace('/\D+/', '', $x);
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $country  = trim($_POST['country']);
    $phone    = clean_phone($_POST['phone_nb']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $userType = $_POST['user_type'];
    $regDate  = date("Y-m-d H:i:s");

    

    if ($name === "" || $email === "" || $password === "" || $confirm === "" || $userType === "") {
        $error = "All fields are required.";
    }
    elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    }
    elseif (strlen($password) < 8 || 
           !preg_match('/[A-Z]/', $password) || 
           !preg_match('/[a-z]/', $password) || 
           !preg_match('/[0-9]/', $password)) {
        $error = "Password must be at least 8 characters, include uppercase, lowercase, and a number.";
    }
    else {

        $check = $conn->prepare("SELECT user_id FROM users WHERE name = ? OR email = ?");
        $check->bind_param("ss", $name, $email);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);


            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, phone_nb, country, registration_date, user_type)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param("sssssss",
                $name, $email, $hashed, $phone, $country, $regDate, $userType
            );

            if ($stmt->execute()) {

                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_type'] = $userType;

                if ($userType === "business") {
                    header("Location: my_business.php");
                } else {
                    header("Location: profile.php");
                }
                exit;
            } else {
                $error = "Database Error: " . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Lebanon Vibes</title>
<link rel="stylesheet" href="main.css"> 
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="logo">🌿 Lebanon Vibes</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-box">

            <h2>Create Your Account</h2>

            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div> 
            <?php endif; ?>

            <form action="" method="POST">

                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? 'user@gmail.com') ?>">
                </div>

                <div class="input-group">
    <label>Phone</label>
    <div class="phone-row">
        <select name="country" id="country" required>
            <option value="">Country</option>
            <option value="Lebanon" data-code="+961" <?= ($_POST['country'] ?? '') == 'Lebanon' ? 'selected' : '' ?>>🇱🇧 +961</option>
            <option value="USA" data-code="+1" <?= ($_POST['country'] ?? '') == 'USA' ? 'selected' : '' ?>>🇺🇸 +1</option>
            <option value="France" data-code="+33" <?= ($_POST['country'] ?? '') == 'France' ? 'selected' : '' ?>>🇫🇷 +33</option>
            <option value="UAE" data-code="+971" <?= ($_POST['country'] ?? '') == 'UAE' ? 'selected' : '' ?>>🇦🇪 +971</option>
        </select>

          <div style="display:flex; gap:5px;">
         <input type="text" id="code" style="width:80px; text-align: center;" disabled class="input-code">

        <input type="text" name="phone_nb" placeholder="Phone number" required
               value="<?= htmlspecialchars($_POST['phone_nb'] ?? '') ?>">
    </div>
</div>
                

    <div class="input-group password-box">
    <label>Password</label>
    <input
        type="password"
        name="password"
        id="password"
        placeholder="At least 8 chars!"
        required
    >
</div>

                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <div class="input-group">
                    <label>User Type</label>
                    <select name="user_type" required>
                        <option value="">Select Type</option>
                        <option value="normal" <?= ($_POST['user_type'] ?? '') == 'normal' ? 'selected' : '' ?>>Normal User</option>
                        <option value="business" <?= ($_POST['user_type'] ?? '') == 'business' ? 'selected' : '' ?>>Business</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Register</button>
            </form>

            
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById("country");
    const codeInput = document.getElementById("code");

    function updateCode() {
        let code = countrySelect.options[countrySelect.selectedIndex].dataset.code;
        codeInput.value = code ? code : "";
    }

    countrySelect.addEventListener("change", updateCode);
    
    updateCode(); 
});
</script>

</body>
</html>