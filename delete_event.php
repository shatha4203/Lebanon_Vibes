 <?php
include 'db_connection.php';
session_start();

// ✅ Safely get event ID and business ID
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$business_id = isset($_SESSION['business_id']) ? intval($_SESSION['business_id']) : 0;

// ✅ Validate inputs before deleting
if ($event_id <= 0 || $business_id <= 0) {
    die("<p>Invalid event or session data.</p>");
}

// ✅ Use a prepared statement for safety
$stmt = $conn->prepare("DELETE FROM events WHERE event_id = ? AND business_id = ?");
$stmt->bind_param("ii", $event_id, $business_id);

if ($stmt->execute()) {
    echo "<p>Event deleted successfully.</p>";
} else {
    echo "<p>Error deleting event: " . htmlspecialchars($stmt->error) . "</p>";
}

$stmt->close();
$conn->close();
?>
<a href="manage_events.php">Back to My Events</a>
