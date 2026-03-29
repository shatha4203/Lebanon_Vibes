 <?php
session_start();
include "db_connection.php";
include 'weather_functions.php' ;
$weather_info = get_weather_and_suggestions('Beirut'); 
$weather_class = $weather_info['weather_class'];
// --- END: DYNAMIC WEATHER LOGIC ---
$message = '';
 
$business_id = null;
 
if (isset($_GET['business_id']) && is_numeric($_GET['business_id'])) {
    $business_id = intval($_GET['business_id']);
    $_SESSION['business_id'] = $business_id;
}
 
if (!$business_id && isset($_SESSION['business_id'])) {
    $business_id = intval($_SESSION['business_id']);
}
 
if (!$business_id) {
    die("<h2 style='color:red;text-align:center;margin-top:50px'>❌ Missing business_id.  
    You must open this page from a business profile.</h2>");
}

 
if (isset($_POST['submit'])) {

    $title         = trim($_POST['title']);
    $description   = trim($_POST['description']);
    $location      = trim($_POST['location']);
    $address       = trim($_POST['address']);
    $date_from     = $_POST['date_from'];
    $date_to       = !empty($_POST['date_to']) ? $_POST['date_to'] : null;
    $time_from     = $_POST['time_from'] ?? null;
    $time_to       = !empty($_POST['time_to']) ? $_POST['time_to'] : null;
    $max_attendees = intval($_POST['max_attendees']);
    $event_type    = trim($_POST['category'] ?? null);
    $base_price    = floatval($_POST['base_price']);
    $created_at    = date("Y-m-d H:i:s");

    $media_url = "";

    if (!empty($_FILES['cover_image']['name'])) {

        $target_dir = "uploads/events/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $file_name = basename($_FILES['cover_image']['name']);
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed)) {

            $new_file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9_-]/", "", $file_name);
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
                $media_url = $target_file;
            } else {
                $message = "❌ Failed to upload image.";
            }
        } else {
            $message = "❌ Invalid image format.";
        }
    }

    
    $sql = "INSERT INTO events  (title, description, location, date_from, date_to, time_from, time_to, 
        event_type, created_at, business_id, base_price, approves, address, 
        max_attendees, media_url)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    try {
        $stmt = $conn->prepare($sql);
        $approves = 0;
  
        $stmt->bind_param(
            "sssssssssidssis",
            $title,
            $description,
            $location,
            $date_from,
            $date_to,
            $time_from,
            $time_to,
            $event_type,
            $created_at,
            $business_id,
            $base_price,
            $approves,
            $address,
            $max_attendees,
            $media_url
        );

        $stmt->execute();
        $message = "✅ Event added successfully! Waiting for admin approval.";

    } catch (mysqli_sql_exception $e) {
        $message = "❌ SQL Error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event - Lebanon Vibes</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --cedar: #38761d; 
            --cedar-light: #52b126;
            --cedar-outline: #f3f9f1;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--cedar-outline); 
        }
        .cedar-bg { background-color: var(--cedar); }
        .cedar-text { color: var(--cedar); }
        .cedar-border { border-color: var(--cedar); }
        .btn-cedar { background-color: var(--cedar); color: white; transition: 0.2s; }
        .btn-cedar:hover { background-color: var(--cedar-light); }
        .card-cedar { border: 1px solid rgba(0,0,0,0.05); border-radius: 1.5rem; }

        input:focus, textarea:focus, select:focus {
            box-shadow: 0 0 0 3px rgba(56,118,29,0.4);
            border-color: var(--cedar);
        }
        .vibe-tag { cursor: pointer; transition: .2s; user-select: none; }
        .vibe-tag.selected { background: var(--cedar-light); color: white; border-color: var(--cedar); transform: scale(1.05); }
    </style>
</head>

<body class="min-h-screen p-4 md:p-8">

<header class="mb-8">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <h1 class="text-3xl font-extrabold cedar-text">Lebanon Vibes</h1>
        <nav class="hidden sm:flex space-x-4">
            <a href="index.php" class="text-gray-600 hover:cedar-text">Home</a>
            <a href="#" class="text-gray-600 hover:cedar-text">My Events</a>
            <a href="#" class="btn-cedar px-4 py-2 rounded-full text-sm shadow-md">Business Dashboard</a>
        </nav>
    </div>
</header>

<main class="max-w-5xl mx-auto">
    <div class="bg-white p-6 md:p-10 card-cedar shadow-2xl">
        <h2 class="text-4xl font-bold mb-4 cedar-text">
            <i class="fas fa-calendar-plus mr-3"></i>Create a New Vibe
        </h2>
        <p class="text-gray-600 mb-8">
            Tell us about your next big event! All fields are required to ensure the best listing quality.
        </p>

        <!-- FIXED FORM -->
        <form id="add-event-form" method="POST" enctype="multipart/form-data">

            <!-- Event Details -->
            <div class="mb-8 p-6 bg-gray-50 rounded-xl">
                <h3 class="text-2xl font-semibold mb-4 cedar-text">1. Event Details</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                    <input type="text" id="title" name="title" required class="w-full p-3 border rounded-lg">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Detailed Description</label>
                    <textarea id="description" name="description" rows="4" required minlength="150"
                        class="w-full p-3 border rounded-lg"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City / Location</label>
                        <input type="text" id="location" name="location" required class="w-full p-3 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Specific Address</label>
                        <input type="text" id="address" name="address" class="w-full p-3 border rounded-lg">
                    </div>
                </div>
            </div>

            <!-- Timing -->
            <div class="mb-8 p-6 bg-gray-50 rounded-xl">
                <h3 class="text-2xl font-semibold mb-4 cedar-text">2. Timing & Logistics</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm">Start Date & Time</label>
                        <input type="datetime-local" id="date_from" name="date_from" required
                            class="w-full p-3 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm">End Date & Time</label>
                        <input type="datetime-local" id="date_to" name="date_to"
                            class="w-full p-3 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm">Max Attendees</label>
                        <input type="number" id="max_attendees" name="max_attendees" min="1" required
                            class="w-full p-3 border rounded-lg">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm mb-1">Event Category</label>
                    <select id="category" name="category" required class="w-full p-3 border rounded-lg">
                        <option value="">-- Select Category --</option>
                        <option value="Music & Concert">Music & Concert</option>
                        <option value="Food & Drink">Food & Drink</option>
                        <option value="Wellness & Yoga">Wellness & Yoga</option>
                        <option value="Art & Culture">Art & Culture</option>
                        <option value="Nightlife & Party">Nightlife & Party</option>
                        <option value="Outdoor & Adventure">Outdoor & Adventure</option>
                        <option value="Workshop & Education">Workshop & Education</option>
                    </select>
                </div>
            </div>

            <!-- Pricing & Vibes -->
            <div class="mb-8 p-6 bg-gray-50 rounded-xl">
                <h3 class="text-2xl font-semibold mb-4 cedar-text">3. Pricing & Atmosphere</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm">Pricing Model</label>
                        <select id="price_model" name="price_model" required class="w-full p-3 border rounded-lg">
                            <option value="ticket">Per Ticket Price</option>
                            <option value="free">Free Entry</option>
                            <option value="reservation">Reservation Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm">Base Price (USD)</label>
                        <input type="number" id="base_price" name="base_price" min="0" value="0.00" step="0.01" required
                            class="w-full p-3 border rounded-lg">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm mb-2">Select Primary Vibes (Choose up to 3)</label>
                    <div id="vibes-container" class="flex flex-wrap gap-2"></div>
                    <input type="hidden" id="vibes" name="vibes" required>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="mb-8 p-6 bg-gray-50 rounded-xl">
                <h3 class="text-2xl font-semibold mb-4 cedar-text">4. Cover Image</h3>
                <input type="file" id="cover_image" name="cover_image" accept="image/*" required
                    class="w-full p-3 border rounded-lg bg-white">
            </div>

            <!-- Submit -->
            <div class="text-center">
            <button type="submit" name="submit" class="btn-cedar"><i class="fas fa-plus-circle mr-2"></i>Submit Event</button>
         </div>

            <?php if (!empty($message)): ?>
                <p class="mt-6 text-center text-lg <?= strpos($message, '✅') === 0 ? 'text-green-600' : 'text-red-600' ?>">
                    <?= htmlspecialchars($message) ?>
                </p>
            <?php endif; ?>
        </form>
    </div>
</main>

<script>
const VIBE_OPTIONS = [
    { key: 'party', label: 'Party 🎉' },
    { key: 'chill', label: 'Chill 😌' },
    { key: 'adventurous', label: 'Adventurous 🏞️' },
    { key: 'romantic', label: 'Romantic 🥰' },
    { key: 'luxury', label: 'Luxury 💎' },
    { key: 'social', label: 'Social 🗣️' },
    { key: 'cultural', label: 'Cultural 🏛️' },
];

const selectedVibes = new Set();
const vibesInput = document.getElementById('vibes');
const vibesContainer = document.getElementById('vibes-container');

function initializeVibeTags() {
    vibesContainer.innerHTML = VIBE_OPTIONS.map(v => `
        <div data-key="${v.key}" class="vibe-tag bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-100">
            ${v.label}
        </div>
    `).join('');

    document.querySelectorAll('.vibe-tag').forEach(tag => {
        tag.addEventListener('click', () => toggleVibe(tag));
    });
}

function toggleVibe(tag) {
    const key = tag.getAttribute('data-key');

    if (selectedVibes.has(key)) {
        selectedVibes.delete(key);
        tag.classList.remove('selected');
    } else {
        if (selectedVibes.size < 3) {
            selectedVibes.add(key);
            tag.classList.add('selected');
        } else {
            alert("You can select up to 3 vibes only.");
        }
    }

    vibesInput.value = Array.from(selectedVibes).join(',');
}

window.addEventListener('load', initializeVibeTags);
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the dynamically determined weather class from PHP
        const weatherClass = '<?php echo $weather_class ?? "sunny"; ?>';
        
        // Apply the class to the body element (assumed to be in header.php)
        document.body.classList.add(weatherClass);
        
        // Create a full-screen container for weather animation effects
        const animationContainer = document.createElement('div');
        animationContainer.id = 'weather-animation-layer';
        animationContainer.classList.add(weatherClass + '-animation');
        document.body.prepend(animationContainer);
    });
</script>
</body>
</html>