<?php

session_start();
 include "db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// 4) Fetch user info
$stmt = $conn->prepare("SELECT id, name, email, user_type, avatar_url, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}
$user = $result->fetch_assoc();
$stmt->close();

// 5) Fetch profile details
$profile = null;
if ($user['user_type'] === 'normal') {
    $stmt = $conn->prepare("SELECT phone, address, bio FROM profiles WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $profile = $res->num_rows ? $res->fetch_assoc() : null;
    $stmt->close();
} elseif ($user['user_type'] === 'business') {
    $stmt = $conn->prepare("SELECT company_name, industry, website, phone, address, bio FROM business_profiles WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $profile = $res->num_rows ? $res->fetch_assoc() : null;
    $stmt->close();
}

$conn->close();

// Helper for safe output
function e($v) {
    return htmlspecialchars((string)$v ?? "", ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile - Lebanon Vibes</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:0; }
        .container { width:80%; margin:20px auto; }
        .card { background:#fff; padding:20px; border-radius:8px; margin-bottom:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
        .avatar { width:80px; height:80px; border-radius:50%; overflow:hidden; background:#ddd; display:inline-block; }
        .avatar img { width:100%; height:100%; object-fit:cover; }
        .info { margin-left:100px; }
        .label { font-weight:bold; color:#555; }
        .value { margin-bottom:10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div style="display:flex; align-items:center;">
            <div class="avatar">
                <?php if (!empty($user['avatar_url'])): ?>
                    <img src="<?php echo e($user['avatar_url']); ?>" alt="Avatar">
                <?php else: ?>
                    <span style="display:flex; align-items:center; justify-content:center; height:100%; font-weight:bold;">
                        <?php echo strtoupper(substr($user['name'],0,2)); ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="info">
                <div class="label">Name:</div>
                <div class="value"><?php echo e($user['name']); ?></div>
                <div class="label">Email:</div>
                <div class="value"><?php echo e($user['email']); ?></div>
                <div class="label">Type:</div>
                <div class="value"><?php echo e($user['user_type']); ?></div>
                <div class="label">Member since:</div>
                <div class="value"><?php echo e(date("Y-m-d", strtotime($user['created_at']))); ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <?php if ($user['user_type'] === 'normal'): ?>
            <h3>Personal Profile</h3>
            <?php if ($profile): ?>
                <div class="label">Phone:</div>
                <div class="value"><?php echo e($profile['phone']); ?></div>
                <div class="label">Address:</div>
                <div class="value"><?php echo e($profile['address']); ?></div>
                <div class="label">Bio:</div>
                <div class="value"><?php echo nl2br(e($profile['bio'])); ?></div>
            <?php else: ?>
                <p>No personal details yet. <a href="edit_profile.php">Add now</a>.</p>
            <?php endif; ?>
        <?php elseif ($user['user_type'] === 'business'): ?>
            <h3>Business Profile</h3>
            <?php if ($profile): ?>
                <div class="label">Company Name:</div>
                <div class="value"><?php echo e($profile['company_name']); ?></div>
                <div class="label">Industry:</div>
                <div class="value"><?php echo e($profile['industry']); ?></div>
                <div class="label">Website:</div>
                <div class="value"><a href="<?php echo e($profile['website']); ?>" target="_blank"><?php echo e($profile['website']); ?></a></div>
                <div class="label">Phone:</div>
                <div class="value"><?php echo e($profile['phone']); ?></div>
                <div class="label">Address:</div>
                <div class="value"><?php echo e($profile['address']); ?></div>
                <div class="label">About:</div>
                <div class="value"><?php echo nl2br(e($profile['bio'])); ?></div>
            <?php else: ?>
                <p>No business details yet. <a href="mybusiness.php">Complete setup</a>.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>