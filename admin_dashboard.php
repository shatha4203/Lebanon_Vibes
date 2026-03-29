<?php
session_start();
include "db_connection.php"; 

// 1. Security Check: Ensure only Admins can enter
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header("HTTP/1.1 403 Forbidden");
    echo "Access denied. Admins only.";
    exit;
}

// Helper for redirection
function redirect_here($qs = '') {
    $loc = basename(__FILE__);
    if ($qs) $loc .= '?' . $qs;
    header("Location: " . $loc);
    exit;
}

// 2. Handle Admin Actions (Delete Businesses or Reviews)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'delete_business' && isset($_POST['business_id'])) {
        $business_id = intval($_POST['business_id']);
        $conn->begin_transaction();
        try {
            $conn->query("DELETE FROM reviews WHERE business_id = $business_id");
            $conn->query("DELETE FROM businesses WHERE business_id = $business_id");
            $conn->commit();
            redirect_here('msg=business_deleted');
        } catch (Exception $e) {
            $conn->rollback();
            redirect_here('msg=error');
        }
    }

    if ($action === 'delete_review' && isset($_POST['review_id'])) {
        $rid = intval($_POST['review_id']);
        $conn->query("DELETE FROM reviews WHERE review_id = $rid");
        redirect_here('msg=review_deleted');
    }
}

// 3. Fetch Summary Data for Cards
$stats = [
    'users'      => $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0],
    'businesses' => $conn->query("SELECT COUNT(*) FROM businesses")->fetch_row()[0],
    'events'     => $conn->query("SELECT COUNT(*) FROM events")->fetch_row()[0],
    'reviews'    => $conn->query("SELECT COUNT(*) FROM reviews")->fetch_row()[0]
];

// 4. Fetch Recent Records (MATCHED TO YOUR DATABASE COLUMNS)

// Businesses: uses 'name' instead of 'business_name'
$recentBusinesses = $conn->query("SELECT business_id, name, business_type, location, created_at FROM businesses ORDER BY created_at DESC LIMIT 10");

// Events: uses 'title' instead of 'event_name'
$recentEvents = $conn->query("SELECT event_id, title, date_from, date_to FROM events ORDER BY created_at DESC LIMIT 10");

// Reviews: JOIN users to get the 'name' column (aliased as 'username' for the table)
$recentReviews = $conn->query("
    SELECT r.*, u.name AS username 
    FROM reviews r 
    LEFT JOIN users u ON r.user_id = u.user_id 
    ORDER BY r.created_at DESC LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Lebanon Vibes</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 20px; color: #333; }
        .container { max-width: 1200px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-top: 5px solid #e91e63; }
        .stat-card h3 { margin: 0; color: #777; font-size: 14px; text-transform: uppercase; }
        .stat-card p { font-size: 28px; font-weight: bold; margin: 10px 0 0; color: #e91e63; }
        .btn-box { margin-bottom: 25px; }
        .btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; color: white; display: inline-block; border: none; cursor: pointer; }
        .btn-primary { background: #e91e63; }
        .btn-blue { background: #2196F3; margin-left: 10px; }
        .btn-danger { background: #f44336; font-size: 12px; padding: 5px 10px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 40px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #333; color: white; font-size: 13px; }
        tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Admin Control Panel</h1>
        <a href="logout.php" style="color: #666;">Logout</a>
    </div>

    <div class="btn-box">
        <a href="admin_business.php" class="btn btn-primary">Verify Businesses</a>
        <a href="admin_approve_events.php" class="btn btn-blue">Approve Events</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><h3>Users</h3><p><?php echo $stats['users']; ?></p></div>
        <div class="stat-card"><h3>Businesses</h3><p><?php echo $stats['businesses']; ?></p></div>
        <div class="stat-card"><h3>Events</h3><p><?php echo $stats['events']; ?></p></div>
        <div class="stat-card"><h3>Reviews</h3><p><?php echo $stats['reviews']; ?></p></div>
    </div>

    <h2>Recent Businesses</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>Type</th><th>Location</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php if($recentBusinesses): while ($b = $recentBusinesses->fetch_assoc()): ?>
            <tr>
                <td><?php echo $b['business_id']; ?></td>
                <td><strong><?php echo htmlspecialchars($b['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($b['business_type']); ?></td>
                <td><?php echo htmlspecialchars($b['location']); ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Delete this business?');">
                        <input type="hidden" name="action" value="delete_business">
                        <input type="hidden" name="business_id" value="<?php echo $b['business_id']; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>

    <h2>Recent Events</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Event Title</th><th>Date From</th><th>Date To</th></tr>
        </thead>
        <tbody>
            <?php if($recentEvents): while ($e = $recentEvents->fetch_assoc()): ?>
            <tr>
                <td><?php echo $e['event_id']; ?></td>
                <td><strong><?php echo htmlspecialchars($e['title']); ?></strong></td>
                <td><?php echo $e['date_from']; ?></td>
                <td><?php echo $e['date_to']; ?></td>
            </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>

    <h2>Recent Reviews</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>User</th><th>Rating</th><th>Comment</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php if($recentReviews): while ($rv = $recentReviews->fetch_assoc()): ?>
            <tr>
                <td><?php echo $rv['review_id']; ?></td>
                <td><?php echo htmlspecialchars($rv['username'] ?? 'User'); ?></td>
                <td><?php echo $rv['rating']; ?>/5</td>
                <td><?php echo htmlspecialchars(substr($rv['comment'], 0, 80)) . '...'; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="delete_review">
                        <input type="hidden" name="review_id" value="<?php echo $rv['review_id']; ?>">
                        <button type="submit" class="btn btn-danger">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>