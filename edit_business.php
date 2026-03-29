<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$business_id = intval($_GET['id'] ?? 0);
if ($business_id <= 0) die("Invalid Business ID");

// FETCH BUSINESS
$stmt = $conn->prepare("SELECT * FROM businesses WHERE business_id=?");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$business) die("Business not found");

// PRELOAD DATA
$name        = $business['name'];
$category    = $business['category'];
$description = $business['description'];
$email       = $business['email'];
$phone       = $business['phone'];
$address     = $business['address'];
$vibes       = $business['vibes'];
$media_url   = $business['media_url'];
$location    = $business['location'];

// LOCATION DEFAULT
$lat = 33.8938;
$lng = 35.5018;
if ($location && strpos($location, ',') !== false) {
    [$lat, $lng] = explode(',', $location);
}

// MEDIA
$currentMedia = [];
if ($media_url) {
    parse_str($media_url, $parsed);
    $currentMedia = array_values($parsed);
}

// OPEN HOURS (JSON)
$openHours = json_decode($business['open_hours'], true) ?? [];

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['business_name']);
    $description = trim($_POST['description']);
    $email       = trim($_POST['email']);
    $phone       = trim($_POST['phone']);
    $address     = trim($_POST['address']);
    $location    = trim($_POST['location']);
    $vibes       = isset($_POST['vibes']) ? implode(", ", $_POST['vibes']) : "";

    // OPEN HOURS
    $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    $hours = [];
    foreach ($days as $day) {
        $o = $_POST["day{$day}_open"] ?? '';
        $c = $_POST["day{$day}_close"] ?? '';
        $hours[$day] = ($o && $c) ? "$o-$c" : "CLOSED";
    }
    $open_hours = json_encode($hours);

    // MEDIA KEEP / DELETE
    $remainingMedia = $_POST['keep_media'] ?? [];

    // UPLOAD NEW MEDIA
    if (!empty($_FILES['media']['name'][0])) {
        $dir = __DIR__ . "/PICS/";
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        foreach ($_FILES['media']['name'] as $i => $file) {
            $tmp = $_FILES['media']['tmp_name'][$i];
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif','mp4','mov'])) continue;
            $new = time() . "_" . uniqid() . ".$ext";
            move_uploaded_file($tmp, $dir.$new);
            $remainingMedia[] = $new;
        }
    }

    // MEDIA STRING
    $mediaParts = [];
    foreach ($remainingMedia as $m) {
        $mediaParts[] = "media=" . urlencode($m);
    }
    $finalMedia = implode("&", $mediaParts);

    // UPDATE
    $sql = "UPDATE businesses SET
        name=?, description=?, address=?, phone=?, email=?,
        media_url=?, vibes=?, open_hours=?, location=?
        WHERE business_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssi",
        $name, $description, $address, $phone, $email,
        $finalMedia, $vibes, $open_hours, $location, $business_id
    );

    if ($stmt->execute()) {
        $success = "Business updated successfully!";
        header("Refresh:2; url=my_business.php");
    } else {
        $error = $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Business | Lebanon Vibes</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</head>

<body class="bg-gray-100 py-10 font-sans">

<main class="max-w-4xl mx-auto bg-white p-10 rounded-2xl shadow-lg">

<h2 class="text-3xl font-bold text-green-700 mb-8">Edit Business</h2>

<?php if ($success): ?><div class="bg-green-100 p-4 mb-6 rounded"><?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-100 p-4 mb-6 rounded"><?= $error ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="space-y-10">

<input type="hidden" name="location" id="location" value="<?= $lat . ',' . $lng ?>">

<!-- GENERAL -->
<div class="bg-gray-50 p-6 rounded-xl space-y-4">
<input name="business_name" value="<?= htmlspecialchars($name) ?>" class="w-full p-3 rounded border" placeholder="Business Name">
<textarea name="description" class="w-full p-3 rounded border" placeholder="Description"><?= htmlspecialchars($description) ?></textarea>
</div>

<!-- CONTACT -->
<div class="bg-gray-50 p-6 rounded-xl grid md:grid-cols-2 gap-6">
<input name="email" type="email" value="<?= htmlspecialchars($email) ?>" class="p-3 rounded border" placeholder="Email (Gmail)">
<input name="phone" type="text" value="<?= htmlspecialchars($phone) ?>" class="p-3 rounded border" placeholder="Phone Number">
<input name="address" value="<?= htmlspecialchars($address) ?>" class="p-3 rounded border md:col-span-2" placeholder="Address">
</div>

<!-- VIBES -->
<div class="bg-gray-50 p-6 rounded-xl">
<h3 class="font-semibold mb-4">Vibes</h3>
<?php
$allVibes = ['Chill','Romantic','Family Friendly','Luxury','Outdoor','Music','Historic','Modern'];
$current = explode(", ", $vibes);
?>
<div class="grid grid-cols-2 md:grid-cols-3 gap-3">
<?php foreach ($allVibes as $v): ?>
<label class="flex items-center gap-2">
<input type="checkbox" name="vibes[]" value="<?= $v ?>" <?= in_array($v,$current)?'checked':'' ?> class="accent-green-600">
<?= $v ?>
</label>
<?php endforeach; ?>
</div>
</div>

<!-- MEDIA -->
<div class="bg-gray-50 p-6 rounded-xl">
<h3 class="font-semibold mb-4">Media (uncheck to delete)</h3>
<div class="flex flex-wrap gap-4">
<?php foreach ($currentMedia as $m): ?>
<label class="relative">
<input type="checkbox" name="keep_media[]" value="<?= $m ?>" checked class="absolute top-2 left-2">
<?php if (preg_match('/\.(mp4|mov)$/i',$m)): ?>
<video src="PICS/<?= $m ?>" class="w-32 rounded" controls></video>
<?php else: ?>
<img src="PICS/<?= $m ?>" class="w-32 h-32 object-cover rounded">
<?php endif; ?>
</label>
<?php endforeach; ?>
</div>
<input type="file" name="media[]" multiple class="mt-4">
</div>

<!-- OPEN HOURS -->
<div class="bg-gray-50 p-6 rounded-xl">
<h3 class="font-semibold mb-4">Operating Hours</h3>
<?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d):
$val = $openHours[$d] ?? 'CLOSED';
[$o,$c] = $val !== 'CLOSED' ? explode('-',$val) : ['',''];
?>
<div class="grid grid-cols-3 gap-4 mb-2">
<span><?= $d ?></span>
<input type="time" name="day<?= $d ?>_open" value="<?= $o ?>">
<input type="time" name="day<?= $d ?>_close" value="<?= $c ?>">
</div>
<?php endforeach; ?>
</div>

<!-- MAP -->
<div class="bg-gray-50 p-6 rounded-xl">
<h3 class="font-semibold mb-3">Location</h3>

<button type="button" onclick="getMyLocation()"
class="mb-3 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
📍 Use My Current Location
</button>

<div id="map" class="h-72 rounded"></div>
<p class="text-sm text-gray-500 mt-2">Click or drag marker to change location</p>
</div>

<button class="w-full bg-green-600 hover:bg-green-700 text-white text-xl py-3 rounded-xl">
Save Changes
</button>

</form>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map = L.map('map').setView([<?= $lat ?>, <?= $lng ?>], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

let marker = L.marker([<?= $lat ?>, <?= $lng ?>], { draggable:true }).addTo(map);

function updateLoc(latlng) {
document.getElementById('location').value =
latlng.lat.toFixed(6) + ',' + latlng.lng.toFixed(6);
}

marker.on('dragend', e => updateLoc(e.target.getLatLng()));
map.on('click', e => { marker.setLatLng(e.latlng); updateLoc(e.latlng); });

function getMyLocation() {
if (!navigator.geolocation) {
alert("Geolocation not supported");
return;
}
navigator.geolocation.getCurrentPosition(pos => {
let lat = pos.coords.latitude;
let lng = pos.coords.longitude;
marker.setLatLng([lat, lng]);
map.setView([lat, lng], 16);
updateLoc({lat:()=>lat,lng:()=>lng});
}, () => alert("Location access denied"));
}
</script>

</body>
</html>
