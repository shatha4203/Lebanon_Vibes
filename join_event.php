<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];
$event_id = $_GET['id'] ?? 0;


$check = $conn->query("SELECT * FROM user_event WHERE user_id=$user_id AND event_id=$event_id");
if ($check->num_rows > 0) {
    echo "<p>You already joined this event.</p>";
} else {
    $sql = "INSERT INTO user_event (user_id, event_id) VALUES ($user_id, $event_id)";
    if ($conn->query($sql)) {
        echo "<p>✅ You have successfully joined the event!</p>";
    } else {
        echo "<p>❌ Error: " . $conn->error . "</p>";
    }
}
?>
<a href='my_events.php'>Go to My Events</a>
