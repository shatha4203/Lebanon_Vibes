 <?php
include('db_connection.php');
session_start();

//  Safely get and validate user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = intval($_SESSION['user_id']); // always cast to int for safety

//  Use a prepared statement instead of direct SQL
$stmt = $conn->prepare("
    SELECT e.*
    FROM events e
    JOIN user_event ue ON e.event_id = ue.event_id
    WHERE ue.user_id = ?
    ORDER BY e.date_from DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>My Joined Events</h2>

<?php while ($row = $result->fetch_assoc()): ?>
  <div class="event-card">
    <h3><?= htmlspecialchars($row['title']) ?></h3>
    <p><?= htmlspecialchars($row['description']) ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($row['date_from']) ?></p>
    <a href="event_details.php?id=<?= urlencode($row['event_id']) ?>">View Details</a>
  </div>
  <hr>
<?php endwhile; ?>

<?php
$stmt->close();
$conn->close();
?>
