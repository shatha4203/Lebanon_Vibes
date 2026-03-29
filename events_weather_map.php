<?php
require_once 'db_connection.php';

$q = $conn->query("
  SELECT event_id,title,lat,lon,location,weather_tag,start_date
  FROM events
  WHERE approves=1 AND start_date >= NOW()
");

$events = $q->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css">
<link rel="stylesheet" href="css/weather-map.css">
</head>
<body>

<h2 class="title">Events by Weather & Time</h2>

<div class="weather-toggle">
  <button data-time="today">Today</button>
  <button data-time="weekend">Weekend</button>
  <button data-time="all">All</button>
</div>

<div class="weather-toggle">
  <label><input type="checkbox" value="sunny" checked>Sunny</label>
  <label><input type="checkbox" value="cloudy" checked>Cloudy</label>
  <label><input type="checkbox" value="rainy" checked>Rainy</label>
  <label><input type="checkbox" value="snowy" checked>Snowy</label>
</div>

<div id="map"></div>

<script>
const EVENTS = <?= json_encode($events); ?>;
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
<script src="js/weather-clusters.js"></script>

</body>
</html>
