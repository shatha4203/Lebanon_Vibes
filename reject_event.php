 
 <?php
include 'db_connection.php';
session_start();

// Securely check if user is logged in and has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

//  Validate event ID
if ($event_id <= 0) {
    die("Invalid event ID");
}

$stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();

header("Location: admin_pending_events.php?rejected=1");
exit;
?>
