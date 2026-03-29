<?php
session_start();
include "db_connection.php";

if (!isset($_GET['id'])) {
    die("Business ID missing.");
}

$business_id = intval($_GET['id']);

$sql = "SELECT * FROM businesses WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $business_id);
$stmt->execute();
$result = $stmt->get_result();
$business = $result->fetch_assoc();

if (!$business) {
    die("Business not found.");
}

$business_name = htmlspecialchars($business['name']);
$location = $business['location']; // "lat,lng"
list($bizLat, $bizLng) = explode(",", $location);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

<title>Directions to <?= $business_name ?></title>

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Leaflet Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
#map {
    height: 420px;
    border-radius: 20px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.20);
}
</style>
</head>

<body class="bg-gray-100 min-h-screen p-5">

<!-- Page Header -->
<div class="max-w-3xl mx-auto mb-6">
    <h1 class="text-3xl font-bold text-green-main">
        Directions to <?= $business_name ?>
    </h1>
    <p class="text-gray-600 mt-1">Follow the route to reach the business easily.</p>
</div>

<!-- Main Container -->
<div class="max-w-3xl mx-auto bg-white p-6 rounded-2xl shadow-lg border border-gray-200">

    <!-- Map -->
    <div id="map" class="mb-6"></div>

    <!-- Status Message -->
    <p id="status" class="text-gray-700 text-lg mb-4"></p>

    <!-- Google Maps Btn -->
    <a id="gmapsBtn"
       href="#"
       target="_blank"
       class="hidden w-full text-center bg-green-main hover:bg-green-dark text-white py-3 rounded-xl font-bold shadow transition">
       🚗 Open in Google Maps
    </a>
</div>

<!-- Back Button -->
<div class="max-w-3xl mx-auto mt-6">
    <a href="javascript:history.back()"
       class="inline-block bg-gray-800 hover:bg-black text-white px-5 py-3 rounded-xl font-semibold">
        ← Back
    </a>
</div>


<!-- JS Logic -->
<script>
let bizLat = <?= $bizLat ?>;
let bizLng = <?= $bizLng ?>;

// Initialize Map
let map = L.map('map').setView([bizLat, bizLng], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19
}).addTo(map);

// Business Marker
let businessMarker = L.marker([bizLat, bizLng], {
    icon: L.icon({
        iconUrl: "https://cdn-icons-png.flaticon.com/512/684/684908.png",
        iconSize: [40, 40]
    })
}).addTo(map)
  .bindPopup("<?= $business_name ?>")
  .openPopup();

let userMarker;
let routeLine;

// Load User Location
function loadUserLocation() {
    document.getElementById("status").innerText =
        "Getting your current location...";

    navigator.geolocation.getCurrentPosition(pos => {

        let userLat = pos.coords.latitude;
        let userLng = pos.coords.longitude;

        // User Marker
        userMarker = L.marker([userLat, userLng], {
            icon: L.icon({
                iconUrl: "https://cdn-icons-png.flaticon.com/512/64/64113.png",
                iconSize: [40, 40]
            })
        }).addTo(map)
          .bindPopup("You are here")
          .openPopup();

        // Route Line
        routeLine = L.polyline(
            [[userLat, userLng], [bizLat, bizLng]],
            { color: "blue", weight: 5 }
        ).addTo(map);

        map.fitBounds(routeLine.getBounds());

        document.getElementById("status").innerText =
            "Route shown. You can open Google Maps for full navigation.";

        // Generate Google Maps Link
        let gmapsURL =
            https://www.google.com/maps/dir/?api=1&origin=${userLat},${userLng}&destination=${bizLat},${bizLng};

        let btn = document.getElementById("gmapsBtn");
        btn.href = gmapsURL;
        btn.classList.remove("hidden");

    }, () => {
        document.getElementById("status").innerText =
            "Unable to access your location.";
    });
}

loadUserLocation();
</script>

</body>
</html>