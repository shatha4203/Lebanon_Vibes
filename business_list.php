<?php
session_start();
include 'db_connection.php';

$search = trim($_GET['search'] ?? '');
$selected_categories = $_GET['categories'] ?? [];
$selected_vibes = $_GET['vibes'] ?? [];

$all_categories = ["Restaurant", "Cafe", "Shop", "Pool", "Hotel", "Bar", "Park", "Museum", "Beach", "Gym"];
$all_vibes = ["Relaxing", "Adventurous", "Romantic", "Cultural", "Fun", "Luxurious", "Nature", "Family"];

$sql = "SELECT business_id, name, category, media_url, vibes FROM businesses WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR category LIKE ? OR vibes LIKE ?)";
    $types .= "sss";
    $search_param = "%$search%";
    $params[] = $search_param; $params[] = $search_param; $params[] = $search_param;
}

if (!empty($selected_categories)) {
    $placeholders = implode(',', array_fill(0, count($selected_categories), '?'));
    $sql .= " AND category IN ($placeholders)";
    foreach ($selected_categories as $c) { $types .= "s"; $params[] = $c; }
}

if (!empty($selected_vibes)) {
    foreach ($selected_vibes as $v) {
        $sql .= " AND vibes LIKE ?";
        $types .= "s";
        $params[] = "%$v%";
    }
}

$sql .= " ORDER BY name ASC";
$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lebanon Vibes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #F9F7F2; color: #064E3B; }
        
        /* Header & Search */
        .header-title { text-align: center; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.05em; color: #064E3B; margin: 2rem 0 1rem; }
        .search-box { display: flex; justify-content: center; margin-bottom: 1.5rem; }
        .search-box input { 
            width: 90%; max-width: 400px; padding: .7rem 1.5rem; border-radius: 999px; 
            border: 1px solid #d1fae5; outline: none; box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        /* Small Bubbles Styling */
        .filter-container { display: flex; flex-wrap: wrap; justify-content: center; gap: .4rem; padding: 0 1rem; margin-bottom: 0.8rem; }
        
        .cat-dot {
            padding: .35rem .9rem; border-radius: 999px; background: #fff; 
            color: #16a34a; font-weight: 600; font-size: 0.75rem; border: 1px solid #16a34a;
            cursor: pointer; transition: .2s;
        }
        .cat-dot:hover { background: #f0fdf4; }
        .cat-selected { background: #16a34a !important; color: white; }

        .vibe-dot {
            padding: .3rem .8rem; border-radius: 999px; background: #fff; 
            color: #92400e; font-weight: 500; font-size: 0.7rem; border: 1px solid #f59e0b;
            cursor: pointer; transition: .2s;
        }
        .vibe-dot:hover { background: #fffbeb; }
        .vibe-selected { background: #f59e0b !important; color: white; }

        /* Grid & Cards */
        .business-grid { 
            display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); 
            gap: 1.2rem; padding: 1.5rem; 
        }
        .business-card { 
            background: white; border-radius: 1.5rem; overflow: hidden; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.05); transition: .3s; 
        }
        .business-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .card-media { width: 100%; height: 130px; object-fit: cover; background: #eee; }
        .business-info { padding: 0.8rem; text-align: center; }
        .business-name { font-weight: 700; font-size: 0.95rem; color: #064E3B; margin-bottom: 0.3rem; }
        .vibe-tag { font-size: 0.6rem; padding: 0.2rem 0.5rem; border-radius: 4px; background: #f1f5f9; color: #475569; margin: 2px; display: inline-block; }
    </style>
</head>
<body>

<h1 class="header-title">LEBANON VIBES</h1>

<div class="search-box">
    <input type="text" id="search-input" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
</div>

<div class="filter-container">
    <?php foreach($all_categories as $c): ?>
    <div class="cat-dot <?= in_array($c,$selected_categories)?'cat-selected':'' ?>" onclick="toggleFilter('categories[]', '<?= $c ?>')">
        <?= $c ?>
    </div>
    <?php endforeach; ?>
</div>

<div class="filter-container">
    <?php foreach($all_vibes as $v): ?>
    <div class="vibe-dot <?= in_array($v,$selected_vibes)?'vibe-selected':'' ?>" onclick="toggleFilter('vibes[]', '<?= $v ?>')">
        <?= $v ?>
    </div>
    <?php endforeach; ?>
</div>

<main class="business-grid">
    <?php while($row=$result->fetch_assoc()):
        $mediaArray = explode(',',$row['media_url']);
        $firstMedia = trim($mediaArray[0]);
        $path = (strpos($firstMedia, 'http') === 0) ? $firstMedia : "PICS/".ltrim($firstMedia, '/');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isVideo = in_array($ext, ['mp4','webm','mov']);
        $vibes = array_slice(array_map('trim', explode(',', $row['vibes'])), 0, 2);
    ?>
    <div class="business-card" onclick="location.href='business_details.php?id=<?= $row['business_id'] ?>'">
        <?php if($firstMedia): ?>
            <?php if($isVideo): ?>
                <video class="card-media" muted autoplay loop playsinline><source src="<?= $path ?>"></video>
            <?php else: ?>
                <img src="<?= $path ?>" class="card-media">
            <?php endif; ?>
        <?php else: ?>
            <div class="card-media flex items-center justify-center bg-gray-100 text-gray-400">No Media</div>
        <?php endif; ?>
        
        <div class="business-info">
            <div class="business-name"><?= htmlspecialchars($row['name']) ?></div>
            <div>
                <?php foreach($vibes as $v): ?>
                    <span class="vibe-tag">#<?= htmlspecialchars($v) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</main>

<script>
// Unified toggle function to keep URL clean
function toggleFilter(paramName, value) {
    const url = new URL(window.location);
    let currentValues = url.searchParams.getAll(paramName);
    
    if (currentValues.includes(value)) {
        currentValues = currentValues.filter(v => v !== value);
    } else {
        currentValues.push(value);
    }
    
    url.searchParams.delete(paramName);
    currentValues.forEach(v => url.searchParams.append(paramName, v));
    window.location = url.href;
}

// Search with Enter key preserving existing filters
document.getElementById('search-input').addEventListener('keypress', e => {
    if(e.key === 'Enter'){
        const url = new URL(window.location);
        url.searchParams.set('search', e.target.value);
        window.location = url.href;
    }
});
</script>

</body>
</html>