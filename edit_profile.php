<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$message = "";
$error = "";

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, phone_nb FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    header("Location: logout.php");
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '') {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($new_password !== '' && strlen($new_password) < 8) {
        $error = "New password must be at least 8 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password confirmation does not match.";
    } else {
        if ($new_password !== '') {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone_nb = ?, password = ? WHERE user_id = ?");
            $update_stmt->bind_param("ssssi", $name, $email, $phone, $hashed, $userId);
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone_nb = ? WHERE user_id = ?");
            $update_stmt->bind_param("sssi", $name, $email, $phone, $userId);
        }

        if ($update_stmt->execute()) {
            $message = "Profile updated successfully!";
            $user['name'] = $name;
            $user['email'] = $email;
            $user['phone_nb'] = $phone;
            $_SESSION['user_name'] = $name;
        } else {
            $error = "Failed to update profile. Email might already be in use.";
        }
        $update_stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Profile | Lebanon Vibes</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="main.css"> 
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">🌿 Lebanon Vibes</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="profile.php" class="active">My Profile</a>
        </div>
    </nav>
    
    <div class="profile-container">
        
        <div class="profile-sidebar">
            <div class="user-snippet">
                <div class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <h3><?= htmlspecialchars($user['name']) ?></h3>
            </div>

            <div class="sidebar-menu">
                <a href="profile.php">Profile Overview</a>
                <a href="edit_profile.php" class="active">Edit Profile</a>
                <a href="my_events.php">My Events</a>
                <a href="logout.php" class="logout-btn">Log Out</a>
            </div>
        </div>

        <div class="profile-content">
            
            <div class="profile-header">
                <h2>Edit Account Details</h2>
            </div>
            
            <?php if ($message): ?>
                <div class="success-message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" action="">
                
                <div class="input-group">
                    <label for="name">Full Name</label>
                    <input id="name" name="name" type="text" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="input-group">
                    <label for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="tel" value="<?= htmlspecialchars($user['phone_nb'] ?? '') ?>" placeholder="+961 XX XXX XXX">
                </div>

                <hr style="margin: 40px 0; border: none; border-top: 1px solid #eee;">
                
                <h3 style="color: var(--cedar-green); margin-bottom: 10px;">Security & Password</h3>
                <p class="note">Leave password fields empty if you do not want to change your current password.</p>

                <div class="info-grid">
                    <div class="input-group">
                        <label for="new_password">New Password</label>
                        <input id="new_password" name="new_password" type="password" placeholder="Min. 8 characters">
                    </div>
                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" placeholder="Repeat new password">
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: left;">
                    <button type="submit" class="btn-primary" style="width: auto; padding: 12px 40px;">Save Changes</button>
                </div>
            </form>

        </div>
    </div>
</body>
</html>