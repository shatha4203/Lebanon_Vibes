<?php require 'db_connection.php';
if (!isset($_SESSION["user_id"])) { header("Location:login_process.php"); exit(); }
$uid = $_SESSION["user_id"];

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    if (!empty($_POST["password"])) {
        $pass = password_hash($_POST["password"], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET name=?, phone_nb=?, password=? WHERE user_id=?");
        $stmt->bind_param("sssi", $name, $phone, $pass, $uid);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, phone_nb=? WHERE user_id=?");
        $stmt->bind_param("ssi", $name, $phone, $uid);
    }
    $stmt->execute();
    echo "<div class='success'>Profile updated.</div>";
}

// Load user info
$stmt = $conn->prepare("SELECT name, email, phone_nb, registration_date, user_type FROM users WHERE user_id=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($name, $email, $phone, $reg_date, $type);
$stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - Lebanon Vibes</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<div class="form-container">
    <h2>Your Profile</h2>
    <a href="logout.php" class="logout">Logout</a>
    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
        <label>Email:</label>
        <input type="email" value="<?= htmlspecialchars($email) ?>" disabled>
        <label>Phone:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>">
        <label>Password: <small>(Fill to change)</small></label>
        <input type="password" name="password" placeholder="New password">
        <button type="submit" name="update">Update Profile</button>
    </form>
    <div><b>User type:</b> <?= $type ?></div>
    <div><b>Registered:</b> <?= $reg_date ?></div>
    <?php if ($type === 'business'): ?>
      <a href="business_profile.php">Business Details</a>
    <?php endif; ?>
    <a href="book_event.php">Book an Event</a>
    <a href="reviews.php">My Reviews</a>
    <a href="report.php">Report Issue</a>
</div>
</body>
</html>