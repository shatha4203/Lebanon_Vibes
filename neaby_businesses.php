 <?php
session_start();
include 'db_connection.php';
include 'weather_functions.php';
include 'ai_recommendations.php';
include 'header.php';

/* ---------- LOCATION ---------- */
$userLat = $userLon = null;
$city = 'Beirut';
$is_coords = false;

if (isset($_GET['lat'], $_GET['lon'])) {
    $userLat = (float)$_GET['lat'];
    $userLon = (float)$_GET['lon'];
    $is_coords = true;
    $_SESSION['user_lat']=$userLat;
    $_SESSION['user_lon']=$userLon;
} elseif (!empty($_SESSION['user_lat']) && !empty($_SESSION['user_lon'])) {
    $userLat = $_SESSION['user_lat'];
    $userLon = $_SESSION['user_lon'];
    $is_coords = true;
} elseif (!empty($_GET['city'])) {
    $city=trim($_GET['city']);
    $_SESSION['user_city']=$city;
} elseif (!empty($_SESSION['user_city'])) {
    $city=$_SESSION['user_city'];
}

$user_location_param = $is_coords ? "lat={$userLat}&lon={$userLon}" : $city;

/* ---------- WEATHER ---------- */
$weather_info = get_weather_and_suggestions($user_location_param, $is_coords);

/* ---------- AI RECOMMENDATIONS ---------- */
$recommendedCategories = recommend_business_categories($weather_info['weather_type']);
$placeholders = implode(',', array_fill(0,count($recommendedCategories),'?'));
$stmt=$conn->prepare("SELECT business_id,name,category,latitude,longitude,address FROM businesses WHERE category IN ($placeholders) LIMIT 20");
$stmt->bind_param(str_repeat('s',count($recommendedCategories)),...$recommendedCategories);
$stmt->execute();
$recommendedBusinesses=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<link rel="stylesheet" href="styles.css"/>
<body class="<?= htmlspecialchars($weather_info['weather_class']) ?>">
<div id="weather-particles"></div>
<div id="lightning"></div>

<section class="hero-cedar text-center mb-5">
  <img src="cedar_logo.svg" style="height:120px; margin-bottom:20px;">
  <h1 class="fw-bold display-6" style="color:var(--cedar)">Nearby Businesses</h1>
  <p class="lead text-muted" style="max-width:650px; margin:auto;">AI-recommended based on weather 🌿🇱🇧</p>
</section>

<div class="container">
  <form method="GET" class="card card-cedar mb-4">
      <input name="city" class="form-control form-control-lg" value="<?= htmlspecialchars($city) ?>" placeholder="<?= htmlspecialchars($weather_info['search_placeholder']) ?>">
      <button class="btn btn-cedar btn-lg mt-2">Search</button>
  </form>

  <div class="text-center mb-3">
      <button id="detectLocationBtn" class="btn btn-outline-primary">📡 Use my location</button>
  </div>

  <div class="card card-cedar mb-4">
      <h4>Recommended Businesses</h4>
      <ul class="list-group">
        <?php foreach($recommendedBusinesses as $b): ?>
            <li class="list-group-item"><?= htmlspecialchars($b['name']) ?> (<?= htmlspecialchars($b['category']) ?>)</li>
        <?php endforeach; ?>
      </ul>
  </div>

  <div id="map" style="height:70vh;"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="weather.js"></script>
<script>
// ----- MAP -----
let map = L.map('map').setView([33.8547,35.8623],9);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(map);

const businesses = <?= json_encode($recommendedBusinesses) ?>;
const userLat = <?= $userLat ?? 'null' ?>;
const userLon = <?= $userLon ?? 'null' ?>;

// Add markers & calculate distance
businesses.forEach(b=>{
    if(!b.latitude||!b.longitude) return;
    const marker=L.marker([b.latitude,b.longitude]).addTo(map);
    let popup=`<b>${b.name}</b><br>${b.category}`;
    if(userLat && userLon){
        const d = Math.round(Math.sqrt(Math.pow(userLat-b.latitude,2)+Math.pow(userLon-b.longitude,2))*111*100)/100;
        popup+=`<br>Distance: ${d} km`;
    }
    marker.bindPopup(popup);
});

// GPS
document.getElementById('detectLocationBtn')?.addEventListener('click',()=>{
    navigator.geolocation.getCurrentPosition(p=>{ location.href=`?lat=${p.coords.latitude}&lon=${p.coords.longitude}`; });
});
</script>
<?php include 'footer.php'; ?>
