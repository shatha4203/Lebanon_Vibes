 <?php
session_start();
include 'db_connection.php';

//  Safely get event and business IDs
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$business_id = isset($_SESSION['business_id']) ? intval($_SESSION['business_id']) : 0;

// Validate IDs before proceeding
if ($event_id <= 0 || $business_id <= 0) {
    die("<p>Invalid event or session data.</p>");
}

if (isset($_POST['update'])) {
    // Sanitize form inputs
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $time_from = $_POST['time_from'] ?? '';
    $time_to = $_POST['time_to'] ?? '';
    $vip_price = $_POST['vip_price'] ?? null;
    $second_price = $_POST['second_price'] ?? null;
    $third_price = $_POST['third_price'] ?? null;
    $event_type = trim($_POST['event_type'] ?? '');

    //  Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("
        UPDATE events
        SET title = ?, description = ?, location = ?,
            date_from = ?, date_to = ?, 
            time_from = ?, time_to = ?, 
            vip_price = ?, second_price = ?, third_price = ?, 
            event_type = ?
        WHERE event_id = ? AND business_id = ?
    ");
    $stmt->bind_param(
        "ssssssssssssi",
        $title, $description, $location,
        $date_from, $date_to,
        $time_from, $time_to,
        $vip_price, $second_price, $third_price,
        $event_type, $event_id, $business_id
    );

    if ($stmt->execute()) {
        echo "<p>Event updated successfully!</p>";
    } else {
        echo "<p>Error: " . htmlspecialchars($stmt->error) . "</p>";
    }

    $stmt->close();
}

// Securely fetch event data
$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND business_id = ?");
$stmt->bind_param("ii", $event_id, $business_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die("<p>Event not found or access denied.</p>");
}
?>

<h2>Edit Event</h2>
<form method="POST">
  <input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>" required><br><br>
  <textarea name="description"><?= htmlspecialchars($row['description']) ?></textarea><br><br>
  <input type="text" name="location" value="<?= htmlspecialchars($row['location']) ?>"><br><br>
  <input type="date" name="date_from" value="<?= htmlspecialchars($row['date_from']) ?>"><br><br>
  <input type="date" name="date_to" value="<?= htmlspecialchars($row['date_to']) ?>"><br><br>
  <input type="time" name="time_from" value="<?= htmlspecialchars($row['time_from']) ?>"><br><br>
  <input type="time" name="time_to" value="<?= htmlspecialchars($row['time_to']) ?>"><br><br>
  <input type="number" step="0.01" name="vip_price" value="<?= htmlspecialchars($row['vip_price']) ?>"><br><br>
  <input type="number" step="0.01" name="second_price" value="<?= htmlspecialchars($row['second_price']) ?>"><br><br>
  <input type="number" step="0.01" name="third_price" value="<?= htmlspecialchars($row['third_price']) ?>"><br><br>
  <input type="text" name="event_type" value="<?= htmlspecialchars($row['event_type']) ?>"><br><br>
  <button type="submit" name="update">Update</button>
</form>
