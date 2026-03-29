<?php
session_start();
include 'db_connection.php';

// Only Admin access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch events where approves is 0
$sql = "SELECT e.event_id, e.title, e.description, b.name as business_name 
        FROM events e 
        JOIN businesses b ON e.business_id = b.business_id 
        WHERE e.approves = 0";
$result = $conn->query($sql);
?>

<table>
    <thead>
        <tr><th>Title</th><th>Business</th><th>Action</th></tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['business_name']); ?></td>
            <td>
                <a href="admin_approval_action.php?type=event&id=<?php echo $row['event_id']; ?>">Approve</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>