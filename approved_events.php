 
<?php
include 'db_connection.php';
session_start();

 if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}


$event_id = (int)$_GET['id'];

$stmt = $conn->prepare("UPDATE events SET approved = 1 WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();

header("Location: admin_pending_events.php?approved=1");
exit;
?>