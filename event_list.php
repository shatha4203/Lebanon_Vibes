<?php include 'db_connection.php'; ?>
<?php include 'header.php'; ?>
 
<div class="container">
  <h2 class="title">🎭 Explore All Events</h2>
  <div class="events-grid">
<div class="d-flex gap-2 mb-3 flex-wrap">
  <a href="event_list.php?filter=sunny" class="btn btn-cedar btn-sm">☀ Outdoor</a>
  <a href="event_list.php?filter=rainy" class="btn btn-cedar btn-sm">🌧 Cozy Indoor</a>
  <a href="event_list.php?filter=snow" class="btn btn-cedar btn-sm">❄ Warm Spots</a>
  <a href="event_list.php" class="btn btn-cedar-outline btn-sm">Reset</a>
</div>

<?php
session_start();
  
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$sql = "SELECT * FROM events WHERE approved = 1";
$params = [];
$types = "";

// Search filter
if ($search !== '') {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $like = "%{$search}%";
    $params = [&$like, &$like];
    $types = "ss";
}

// Category filter
if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = &$category;
    $types .= "s";
}
 
if (!empty($filter)) {
    if ($filter === "sunny") {
        $sql .= " AND event_type = 'outdoor'";
    }
    if ($filter === "rainy") {
        $sql .= " AND event_type = 'indoor'";
    }
    if ($filter === "snow") {
        $sql .= " AND (category = 'food' OR heated = 1)";
    }
}

$sql .= " ORDER BY date_from DESC";
 
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
 
if (!empty($params)) {
    array_unshift($params, $types);
    call_user_func_array([$stmt, 'bind_param'], $params);
}

$stmt->execute();
$result = $stmt->get_result();
 
while ($row = $result->fetch_assoc()) {
    echo "<div class='event-card'>";
    echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
    echo "<p>" . htmlspecialchars($row['description']) . "</p>";
    echo "<p><strong>Location:</strong> " . $row['location'] . "</p>";
    echo "<p><strong>Date:</strong> " . $row['date_from'] . " → " . $row['date_to'] . "</p>";
    echo "<p><strong>VIP:</strong> $" . $row['vip_price'] . " | <strong>Standard:</strong> $" . $row['second_price'] . "</p>";
    echo "<a href='event_details.php?id=" . $row['event_id'] . "' class='btn btn-cedar btn-sm'>View Details</a>";
    echo "</div><hr>";
}
?>

  <div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Events</h2>
  <form class="d-flex" method="GET">
    <input name="search" class="form-control me-2" placeholder="Search events">
    <button class="btn btn-cedar">Search</button>
  </form>
</div>

<div class="row g-4">
 
  <div class="col-md-4">
    <div class="card card-cedar h-100">
      <img src="event-sample.jpg" class="card-img-top">
      <div class="card-body">
        <h5 class="card-title">Beirut Jazz Night</h5>
        <p class="small text-muted">From $15 • Beirut</p>
        <p class="card-text">Short description here...</p>
      </div>
      <div class="card-footer">
        <a href="event_details.php?id=1" class="btn btn-cedar btn-sm">Details</a>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
