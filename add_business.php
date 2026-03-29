<?php
session_start();
include 'db_connection.php';


if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? 'Connection object not initialized.'));
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$success = $error = "";
$location = trim($_POST['location'] ?? '');
$name = trim($_POST['business_name'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$added_by = (int) ($_SESSION['user_id'] ?? 0); 
$media_url = '';
$vibes = '';      
$open_hours = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

   
    $vibes = isset($_POST['vibes']) ? implode(", ", $_POST['vibes']) : '';

    
    $hours_data = [];
    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    foreach ($days as $day) {
        
        $open = trim($_POST["day{$day}_open"] ?? '');
        $close = trim($_POST["day{$day}_close"] ?? '');

        
        if ($open === '' || $close === '' || $open === '00:00' || $close === '00:00') {
            $hours_data[$day] = 'CLOSED';
        } else {
            
            $hours_data[$day] = $open . '-' . $close;
        }
    }
    
    $open_hours = http_build_query($hours_data, '', '&');

    
    $uploaded_files = [];
    if (isset($_FILES['media']) && is_array($_FILES['media']['name'])) {
        
        $targetDir = __DIR__ . '/PICS/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov'];
        $fileCount = count($_FILES['media']['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            
            if (!isset($_FILES['media']['error'][$i])) {
                continue;
            }

            if ($_FILES['media']['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['media']['name'][$i];
                $tmpName = $_FILES['media']['tmp_name'][$i];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (in_array($fileExtension, $allowedTypes, true)) {
                   
                    $newFileName = time() . '_' . uniqid() . '_' . $i . '.' . $fileExtension;
                    $targetFile = $targetDir . $newFileName;

                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $uploaded_files[] = $newFileName;
                    } else {
                        $error = "Error uploading file #{$i}. Check server permissions.";
                        break;
                    }
                } else {
                    $error = "File #{$i} has an unsupported file type.";
                    break;
                }
            } elseif ($_FILES['media']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $error = "File upload error for file #{$i}: code " . $_FILES['media']['error'][$i];
                break;
            }
        }

        if (!$error && !empty($uploaded_files)) {
            
            $query_parts = array_map(function ($filename) {
                return "media=" . urlencode($filename);
            }, $uploaded_files);

            $media_url = implode('&', $query_parts);
        }
    }

   
    if ($name && $category && !$error) {

        $sql = "INSERT INTO businesses
                (name, description, address, phone, email, category, added_by, media_url, vibes, open_hours, location)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
           
            $stmt->bind_param(
                "sssssisssss",
                $name,
                $description,
                $address,
                $phone,
                $email,
                $category,
                $added_by,
                $media_url,
                $vibes,
                $open_hours,
                $location
            );

            if ($stmt->execute()) {
                $success = "Business **{$name}** added successfully! Redirecting...";
              
                header("Refresh:2; url=business_list.php");
            } else {
                $error = "Database error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $error = "SQL Prepare Error: " . $conn->error;
        }
    } elseif (!$error) {
        $error = "Please fill all required fields (Business Name and Category).";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Add Business | Lebanon Vibes</title>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'green-main': '#10b981',
                    'green-dark': '#065f46',
                    'leaf-green': '#22c55e',
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                },
            }
        }
    }
</script>
<style>
    .green-main-text { color: #10b981; }
    .green-dark-text { color: #065f46; }
    .border-green-main { border-color: #10b981; }

    .card-modern {
        border-radius: 1.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .input-focus:focus {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.5);
    }
</style>

</head>
<body class="min-h-screen py-10 px-4 md:px-6 bg-gray-100 font-sans">

<header class="mb-12">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <h1 class="text-4xl font-extrabold green-dark-text tracking-tight flex items-center">
            <i class="fas fa-tree mr-3 green-main-text text-3xl"></i>LEBANON VIBES
        </h1>
        <a href="business_list.php" class="text-gray-600 hover:text-green-dark font-semibold transition p-3 rounded-xl hover:bg-green-100">
            <i class="fas fa-list-alt mr-1"></i> View Businesses
        </a>
    </div>
</header>

<main class="max-w-4xl mx-auto">
    <div class="bg-white p-8 md:p-12 card-modern">

        <h2 class="text-3xl font-bold green-dark-text mb-8 border-b pb-4 flex items-center">
            <i class="fas fa-plus-circle mr-3 text-green-main"></i> Add a New Business
        </h2>

        <?php if ($success): ?>
            <div class="bg-green-100 border-l-4 border-green-main text-green-700 p-4 mb-6 rounded-lg" role="alert">
                <p class="font-bold">Success!</p>
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                <p class="font-bold">Error!</p>
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-8">

            <input type="hidden" name="location" id="location" value="<?= htmlspecialchars($location) ?>">

            <div class="p-6 bg-gray-50 rounded-xl border-t-4 border-green-main">
                <h3 class="text-xl font-semibold mb-6 flex items-center text-gray-800">
                    <i class="fas fa-store mr-2 text-green-main"></i> General Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="business_name" class="block text-sm font-medium text-gray-700 mb-1">Business Name <span class="text-red-500">*</span></label>
                        <input type="text" name="business_name" id="business_name" required value="<?= htmlspecialchars($name) ?>"
                               class="input-focus mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3">
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select name="category" id="category" required
                                class="input-focus mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 bg-white">
                            <option value="">Select a Category</option>
                            <option value="Restaurant" <?= $category === 'Restaurant' ? 'selected' : '' ?>>Restaurant</option>
                            <option value="Cafe" <?= $category === 'Cafe' ? 'selected' : '' ?>>Cafe</option>
                            <option value="Activity" <?= $category === 'Activity' ? 'selected' : '' ?>>Activity/Site</option>
                            <option value="Hotel" <?= $category === 'Hotel' ? 'selected' : '' ?>>Hotel/Stay</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="4"
                              class="input-focus mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3"><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vibes (Select all that apply)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php
                            $allVibes = ['Chill', 'Lively', 'Romantic', 'Historic', 'Modern', 'Mountain View', 'Coastal', 'Family Friendly'];
                            // Convert current vibes to array safely
                            $currentVibes = [];
                            if (!empty($vibes)) {
                                $currentVibes = explode(', ', $vibes);
                            }
                            foreach ($allVibes as $v) {
                                $isChecked = in_array($v, $currentVibes);
                                echo '<label class="inline-flex items-center text-gray-700">';
                                echo '<input type="checkbox" name="vibes[]" value="' . htmlspecialchars($v) . '" ' . ($isChecked ? 'checked' : '') . ' class="rounded text-green-main focus:ring-green-main border-gray-300">';
                                echo '<span class="ml-2 text-sm">' . htmlspecialchars($v) . '</span>';
                                echo '</label>';
                            }
                        ?>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gray-50 rounded-xl border-t-4 border-green-main">
                <h3 class="text-xl font-semibold mb-6 flex items-center text-gray-800">
                    <i class="fas fa-phone-alt mr-2 text-green-main"></i> Contact & Address
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>"
                               class="input-focus mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($phone) ?>"
                               class="input-focus mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3">
                    </div>
                </div>

                <div class="mt-6">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                    <input type="text" name="address" id="address" value="<?= htmlspecialchars($address) ?>"
                           class="input-focus mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3">
                </div>

                <div class="mt-10 p-5 bg-white rounded-lg border border-gray-200">
                    <h4 class="text-lg font-bold mb-4 flex items-center text-gray-800">
                        <i class="fas fa-map-pin mr-2 text-leaf-green"></i> Current GPS Location (Lat/Lng)
                    </h4>

                    <button type="button" onclick="getMyLocation()"
                        class="px-5 py-3 rounded-lg bg-green-main text-white font-semibold hover:bg-green-dark transition">
                        📍 Get My Current GPS Location
                    </button>

                    <p id="locStatus" class="text-gray-600 mt-3 italic text-sm">Click the button to get the coordinates.</p>

                    <div id="map" style="height: 300px; margin-top: 15px; border-radius: 10px; z-index: 1;"></div>
                </div>
            </div>

            <div class="p-6 bg-gray-50 rounded-xl border-t-4 border-green-main">
                <h3 class="text-xl font-semibold mb-6 flex items-center text-gray-800">
                    <i class="fas fa-clock mr-2 text-green-main"></i> Operating Hours
                </h3>

                <div class="space-y-4">
                    <?php
                        // parse open_hours only if present
                        $displayedHours = [];
                        if (!empty($open_hours)) {
                            parse_str($open_hours, $displayedHours);
                        }

                        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

                        foreach ($days as $day) {
                            $hourValue = $displayedHours[$day] ?? '';
                            // Default open/close
                            $openTime = '';
                            $closeTime = '';

                            if ($hourValue === 'CLOSED' || $hourValue === '') {
                                // leave blank
                            } else {
                                // safe explode: if missing part, provide empty string
                                $parts = explode('-', $hourValue, 2);
                                $openTime = $parts[0] ?? '';
                                $closeTime = $parts[1] ?? '';
                                if ($openTime === 'CLOSED') { $openTime = ''; $closeTime = ''; }
                            }
                            ?>
                            <div class="grid grid-cols-3 gap-4 items-center">
                                <label class="font-medium text-gray-700"><?= htmlspecialchars($day) ?></label>
                                <input type="time" name="day<?= htmlspecialchars($day) ?>_open" value="<?= htmlspecialchars($openTime) ?>"
                                       class="input-focus border border-gray-300 rounded-md p-2 w-full" placeholder="Open">
                                <input type="time" name="day<?= htmlspecialchars($day) ?>_close" value="<?= htmlspecialchars($closeTime) ?>"
                                       class="input-focus border border-gray-300 rounded-md p-2 w-full" placeholder="Close">
                            </div>
                    <?php } ?>
                    <p class="text-sm text-gray-500 mt-4">Leave times blank or 00:00 to mark the day as <strong>CLOSED</strong>.</p>
                </div>
            </div>

            <div class="p-6 bg-gray-50 rounded-xl border-t-4 border-green-main">
                <h3 class="text-xl font-semibold mb-6 flex items-center text-gray-800">
                    <i class="fas fa-camera-retro mr-2 text-green-main"></i> Media (Multiple Images/Videos)
                </h3>

                <label for="media" class="block text-sm font-medium text-gray-700 mb-2">Upload Files</label>
                <input type="file" name="media[]" id="media" multiple accept=".jpg,.jpeg,.png,.gif,.mp4,.mov"
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-100 file:text-green-main hover:file:bg-green-200">
                <p class="text-sm text-gray-500 mt-2">Allowed types: JPG, PNG, GIF, MP4, MOV. Select multiple files by holding Ctrl/Cmd.</p>
            </div>

            <button type="submit" class="w-full bg-green-main hover:bg-green-dark text-white text-xl font-bold px-6 py-3 rounded-xl transition duration-300 shadow-md hover:shadow-lg mt-8">
                <i class="fas fa-check-circle mr-2"></i> Add Business to Directory
            </button>

        </form>
    </div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Initialize the map, centered on Lebanon (default view)
    let map = L.map('map').setView([33.8938, 35.5018], 10);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker;

    // Check if the location field has a value on page load
    const initialLocation = document.getElementById("location").value;
    if (initialLocation) {
        const parts = initialLocation.split(',');
        if (parts.length === 2) {
            const parsedLat = parseFloat(parts[0]);
            const parsedLng = parseFloat(parts[1]);
            if (!isNaN(parsedLat) && !isNaN(parsedLng)) {
                marker = L.marker([parsedLat, parsedLng]).addTo(map)
                    .bindPopup("Set Location")
                    .openPopup();
                map.setView([parsedLat, parsedLng], 16);
                document.getElementById("locStatus").innerText = "Location loaded: " + parsedLat.toFixed(6) + ", " + parsedLng.toFixed(6);
            }
        }
    }

    // Function to get the user's geolocation
    function getMyLocation() {
        document.getElementById("locStatus").innerText = "Getting location, please wait...";

        if (!navigator.geolocation) {
             document.getElementById("locStatus").innerText = "Geolocation is not supported by your browser.";
             return;
        }

        navigator.geolocation.getCurrentPosition(pos => {

            let lat = pos.coords.latitude;
            let lng = pos.coords.longitude;

            if (marker) map.removeLayer(marker);

            marker = L.marker([lat, lng]).addTo(map)
                .bindPopup("Business Location")
                .openPopup();

            map.setView([lat, lng], 16);

            document.getElementById("location").value = lat + "," + lng;

            document.getElementById("locStatus").innerText =
                "Location added: " + lat.toFixed(6) + ", " + lng.toFixed(6);

        }, (error) => {
            let errorMessage = "Failed to get location.";
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = "Location access denied. Please allow location access in your browser settings.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = "Location information is unavailable.";
                    break;
                case error.TIMEOUT:
                    errorMessage = "The request to get user location timed out.";
                    break;
            }
            document.getElementById("locStatus").innerText = errorMessage;
        });
    }

    // Allow the user to click on the map to set a location
    map.on('click', function(e) {
        let lat = e.latlng.lat;
        let lng = e.latlng.lng;

        if (marker) map.removeLayer(marker);

        marker = L.marker([lat, lng]).addTo(map)
            .bindPopup("Manually Set Location")
            .openPopup();

        document.getElementById("location").value = lat + "," + lng;
        document.getElementById("locStatus").innerText =
            "Location manually set: " + lat.toFixed(6) + ", " + lng.toFixed(6);
    });
</script>

</body>
</html>
