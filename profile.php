<?php
session_start();
include 'db_connection.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, email, phone_nb, registration_date, user_type FROM users WHERE user_id = ?");
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

$reviews_stmt = $conn->prepare("SELECT comment, created_at FROM reviews WHERE user_id = ? ORDER BY created_at DESC");
$reviews_stmt->bind_param("i", $userId);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($user['name']) ?>'s Profile — Lebanon Vibes</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="main.css">   
</head>
<body>
    
    <nav class="navbar">
             <a href="index.php" class="logo">🌿 Lebanon Vibes</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
        </div>
    </nav>

    <div class="profile-container">
        
        <div class="profile-sidebar">
            
            <div class="user-snippet">
                <div class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <h3><?= htmlspecialchars($user['name']) ?></h3>
                <p style="color: #666; font-size: 0.9rem;"><?= htmlspecialchars(ucfirst($user['user_type'])) ?></p>
            </div>

            <div class="sidebar-menu">
                <a href="profile.php" class="active">Profile Overview</a>
                <a href="edit_profile.php">Edit Profile</a>
                <a href="my_events.php">MY Events</a>


                <?php if ($user['user_type'] === 'business'): ?>
                    <a href="my_business.php">My Business Page</a>
                <?php endif; ?>

                <?php if ($user['user_type'] === 'admin'): ?>
                    <a href="admin_dashboard.php">Admin Dashboard</a>
                <?php endif; ?>

                <a href="logout.php" class="logout-btn">Log Out</a>
            </div>
            
        </div>

        <div class="profile-content">
            
            <div class="profile-header">
                <h2>Welcome <?= htmlspecialchars($user['name']) ?></h2>
            </div>
            
            <h4 style="margin-bottom: 15px;">Account Details</h4>
            <section class="details-vertical">

    <div class="detail-row">
        <p><label><b>Phone Number:</b></label><?= htmlspecialchars($user['phone_nb'] ?? 'N/A') ?></p>
    </div>

    <div class="detail-row">
        <p><label><b>Email:</b></label><?= htmlspecialchars($user['email']) ?></p>
    </div>

    <div class="detail-row">
        <p><label><b>Registered On:</b></label><?= htmlspecialchars(date('M d, Y', strtotime($user['registration_date']))) ?></p>
    </div>

</section>
            <hr style="margin: 40px 0; border-color: #eee;">

            <div class="reviews">
                <h3>My Reviews</h3>

                <?php if ($reviews_result && $reviews_result->num_rows > 0): ?>
                    <div class="reviews-list">
                        <?php while ($rev = $reviews_result->fetch_assoc()): ?>
                            <div class="review-item" style="border-left: 3px solid var(--cedar-green); padding: 10px; background: #fcfcfc; border-radius: 4px; margin-bottom: 15px;">
                                <div style="font-style: italic; color: var(--text-dark); margin-bottom: 5px;"><?= nl2br(htmlspecialchars($rev['comment'])) ?></div>
                                <small style="color: #888; display: block; text-align: right;">Posted on <?= htmlspecialchars(date('Y-m-d', strtotime($rev['created_at']))) ?></small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #777;">You haven't posted any reviews yet.</p>
                <?php endif; ?>

                <?php $reviews_stmt->close(); ?>
            </div>

        </div>
    </div>
    
</body>
</html>

<?php
$conn->close();
?>