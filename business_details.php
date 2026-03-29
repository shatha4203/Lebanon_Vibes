<?php
session_start();
include "db_connection.php"; 

$id = isset($_GET['id']) ? intval($_GET['id']) : 1;

$stmt = $conn->prepare("SELECT * FROM businesses WHERE business_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$business_res = $stmt->get_result();

if ($business_res->num_rows === 0) {
    die("<div style='font-family:sans-serif; text-align:center; padding:80px; background:#F9F7F2; color:#064E3B;'>
            <h1 style='font-size:4rem;'>🌿</h1>
            <h2 style='font-family:serif;'>حكاية لم تبدأ بعد</h2>
            <p>We couldn't find this story. Please check the ID.</p>
            <a href='index.php' style='color:#00A651; font-weight:bold; text-decoration:none; border-bottom:2px solid;'>Return to Home</a>
         </div>");
}

$business = $business_res->fetch_assoc();


$media_raw = $business['media_url'] ?? '';
$mediaList = [];

if (!empty($media_raw)) {
    $urls = array_filter(array_map('trim', explode(',', $media_raw)));
    foreach ($urls as $u) {
        if (preg_match('#^https?://#', $u)) {
            $mediaList[] = $u;
        } else {
            $clean_path = ltrim($u, '/');
            $mediaList[] = "PICS/" . $clean_path;
        }
    }
}

if (empty($mediaList)) {
    $mediaList[] = "PICS/placeholder.jpg";
}

// Vibes
$vibes_raw = $business['vibes'] ?? 'Nature, Soul, Warmth';
$vibes = array_filter(array_map('trim', explode(',', $vibes_raw)));

// Open Hours
$hours_raw = $business['open_hours'] ?? '{}';
$hours = json_decode($hours_raw, true);
if (!is_array($hours)) $hours = [];
$current_day = date('l');

// Events
$has_events = false;
$events_res = null;
$e_stmt = $conn->prepare("SELECT * FROM events WHERE business_id = ? ORDER BY date DESC LIMIT 3");
if ($e_stmt) {
    $e_stmt->bind_param("i", $id);
    $e_stmt->execute();
    $events_res = $e_stmt->get_result();
    $has_events = ($events_res && $events_res->num_rows > 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($business['name'] ?? 'Lebanon Vibes') ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital@1&family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root { --lv-green:#00A651; --lv-dark:#064E3B; --lv-beige:#F9F7F2; }
        body { background: var(--lv-beige); font-family:'Plus Jakarta Sans',sans-serif; color: var(--lv-dark); overflow-x:hidden; }
        
        .signature-title { background: linear-gradient(135deg,var(--lv-dark),var(--lv-green),var(--lv-dark)); background-size:200% auto; -webkit-background-clip:text; -webkit-text-fill-color:transparent; animation: shine 6s linear infinite; }
        @keyframes shine { to { background-position:200% center; } }

        .no-scrollbar::-webkit-scrollbar { display:none; }
        .gallery-container { display:flex; overflow-x:auto; gap:1.5rem; scroll-snap-type:x mandatory; padding: 1rem 2rem; }
        .gallery-card { flex:0 0 85vw; height:350px; border-radius:30px; transition: all 0.6s cubic-bezier(0.16,1,0.3,1); scroll-snap-align:center; overflow:hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        @media(min-width:768px){.gallery-card{flex:0 0 450px;}}

        .gallery-card img, .gallery-card video { width:100%; height:100%; object-fit:cover; cursor:pointer; }
        .vibe-pill{transition:all 0.3s ease;}
        .vibe-pill:hover{transform:translateY(-3px); box-shadow:0 6px 12px rgba(0,166,81,0.1);}
        .reveal{opacity:0; transform:translateY(25px);}
        
        .contact-card { background: white; border: 1px solid rgba(0,166,81,0.1); transition: all 0.4s ease; }
        .contact-card:hover { border-color: var(--lv-green); transform: scale(1.02); }
    </style>
</head>
<body>

<header class="min-h-[40vh] flex flex-col items-center justify-center text-center px-4">
    <div class="reveal">
        <h1 class="signature-title text-4xl md:text-6xl font-extrabold tracking-tighter mb-2">
            <?= htmlspecialchars($business['name'] ?? 'Lebanon Vibes') ?>
        </h1>
    </div>
    <div class="reveal" style="transition-delay:0.1s;">
        <h2 class="font-amiri text-2xl md:text-3xl text-green-700/80 mb-1 italic">لبنان في قلبي… حكاية خضراء لا تنتهي</h2>
        <p class="text-[10px] uppercase tracking-[0.3em] text-green-900/40">Lebanon in my heart — a green story that never fades.</p>
    </div>
</header>

<section class="py-6 reveal">
    <div class="gallery-container no-scrollbar">
        <?php foreach($mediaList as $m): 
            $fileExtension = pathinfo($m, PATHINFO_EXTENSION);
            $isVideo = in_array(strtolower($fileExtension), ['mp4', 'mov', 'webm']);
        ?>
            <div class="gallery-card group">
                <?php if($isVideo): ?>
                    <video controls muted class="w-full h-full object-cover">
                        <source src="<?= htmlspecialchars($m) ?>" type="video/<?= $fileExtension ?>">
                        Your browser does not support video.
                    </video>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($m) ?>" 
                         onclick="openFullscreen(this)" 
                         class="transition-transform duration-700 group-hover:scale-110">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 py-10 reveal">
    <div class="bg-white p-8 rounded-[40px] shadow-sm border border-green-50">
        <h3 class="text-[10px] font-black uppercase tracking-[0.5em] text-green-400 mb-4">Our Story</h3>
        <p class="leading-relaxed text-lg text-slate-700 italic font-medium">
            "<?= nl2br(htmlspecialchars($business['description'] ?? 'No description available.')) ?>"
        </p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-8 grid lg:grid-cols-2 gap-8 reveal">
    <div>
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="fa-regular fa-clock text-green-500"></i> Opening Hours</h3>
        <div class="grid gap-2">
            <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day):
                $time = $hours[$day] ?? 'Closed';
                $isToday = ($day == $current_day);
            ?>
            <div class="flex justify-between items-center p-3 rounded-2xl <?= $isToday?'bg-green-600 text-white shadow-lg shadow-green-200':'bg-white border border-green-50 text-slate-600'?>">
                <span class="font-bold"><?= $day ?></span>
                <span class="font-semibold"><?= $time ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div>
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="fa-solid fa-leaf text-green-500"></i> The Vibe</h3>
        <div class="flex flex-wrap gap-3">
            <?php foreach($vibes as $v): ?>
            <div class="vibe-pill px-5 py-2 bg-white border border-green-100 rounded-full text-green-700 font-bold shadow-sm flex items-center gap-2">
                <span class="text-xs">🌿</span> <?= htmlspecialchars($v) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if($has_events): ?>
<section class="max-w-7xl mx-auto px-4 py-10 reveal">
    <h3 class="text-2xl font-bold text-green-900 mb-6">Recent Events</h3>
    <div class="grid md:grid-cols-3 gap-6">
        <?php while($event = $events_res->fetch_assoc()): ?>
        <a href="event_details.php?id=<?= $event['event_id'] ?>" class="group bg-white rounded-3xl overflow-hidden shadow-sm border border-green-50">
            <div class="h-48 overflow-hidden">
                <img src="<?= htmlspecialchars($event['image_url'] ?? 'PICS/placeholder.jpg') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>
            <div class="p-4">
                <h4 class="font-bold text-lg"><?= htmlspecialchars($event['title']) ?></h4>
                <p class="text-green-600 font-semibold text-sm"><?= date('M d, Y', strtotime($event['date'])) ?></p>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
</section>
<?php endif; ?>

<section class="max-w-7xl mx-auto px-4 py-12 reveal">
    <div class="grid lg:grid-cols-2 gap-8">
        <div class="space-y-6">
            <h3 class="text-2xl font-bold text-green-900">Get in Touch</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="contact-card p-6 rounded-3xl">
                    <i class="fa-solid fa-phone text-green-500 text-xl mb-3"></i>
                    <p class="text-xs uppercase font-black tracking-widest text-slate-400">Call Us</p>
                    <p class="font-bold text-lg"><?= htmlspecialchars($business['phone'] ?? 'N/A') ?></p>
                </div>
                <div class="contact-card p-6 rounded-3xl">
                    <i class="fa-solid fa-envelope text-green-500 text-xl mb-3"></i>
                    <p class="text-xs uppercase font-black tracking-widest text-slate-400">Email Us</p>
                    <p class="font-bold text-sm truncate"><?= htmlspecialchars($business['email'] ?? 'N/A') ?></p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-3xl border border-green-50 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-black tracking-widest text-slate-400 mb-1">Social Media</p>
                    <p class="font-bold text-green-900">Follow our story</p>
                </div>
                <div class="flex gap-4">
                    <a href="<?= htmlspecialchars($business['instagram'] ?? '#') ?>" target="_blank" class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-green-600 hover:bg-green-600 hover:text-white transition-all">
                        <i class="fa-brands fa-instagram text-xl"></i>
                    </a>
                    <a href="<?= htmlspecialchars($business['tiktok'] ?? '#') ?>" target="_blank" class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-green-600 hover:bg-green-600 hover:text-white transition-all">
                        <i class="fa-brands fa-tiktok text-xl"></i>
                    </a>
                </div>
            </div>

            <div class="bg-green-900 p-8 rounded-[40px] text-white text-center">
                <p class="text-yellow-400 text-sm mb-2">
                    <?php for($i=0;$i<5;$i++) echo '<i class="fa-solid fa-star"></i>'; ?>
                </p>
                <h4 class="text-xl font-bold mb-4">Loved your experience?</h4>
                <a href="add_review.php?business_id=<?= $id ?>" class="inline-block px-8 py-3 bg-white text-green-900 rounded-full font-bold hover:bg-green-50 transition-all">Leave a Review</a>
            </div>
        </div>

        <div class="h-full min-h-[400px] rounded-[40px] overflow-hidden border-8 border-white shadow-2xl relative">
            <iframe 
                src="https://maps.google.com/maps?q=<?= urlencode($business['location'] ?? 'Lebanon') ?>&t=&z=13&ie=UTF8&iwloc=&output=embed" 
                width="100%" height="100%" style="border:0; filter: contrast(1.1) grayscale(0.2);">
            </iframe>
        </div>
    </div>
</section>

<footer class="py-10 text-center text-slate-400 text-xs tracking-widest uppercase">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($business['name'] ?? 'Lebanon Vibes') ?> — All Rights Reserved.
</footer>

<script>
function openFullscreen(el){
    const fsDiv=document.createElement('div');
    fsDiv.style.position='fixed'; fsDiv.style.top='0'; fsDiv.style.left='0';
    fsDiv.style.width='100vw'; fsDiv.style.height='100vh';
    fsDiv.style.background='rgba(6, 78, 59, 0.98)'; fsDiv.style.display='flex';
    fsDiv.style.alignItems='center'; fsDiv.style.justifyContent='center'; fsDiv.style.zIndex='9999';

    let innerHTML = `<img src="${el.src}" style="max-width:90%; max-height:85%; border-radius:20px; box-shadow: 0 20px 50px rgba(0,0,0,0.5);">`;
    innerHTML += `<span style="position:absolute;top:30px;right:40px;font-size:3rem;color:white;cursor:pointer;line-height:1;" onclick="this.parentElement.remove()">×</span>`;
    
    fsDiv.innerHTML = innerHTML;
    document.body.appendChild(fsDiv);
    fsDiv.onclick = (e) => { if(e.target === fsDiv) fsDiv.remove(); };
}


gsap.registerPlugin(ScrollTrigger);
gsap.utils.toArray('.reveal').forEach(el => {
    gsap.to(el, {
        scrollTrigger: { trigger: el, start: "top 92%" },
        y: 0, 
        opacity: 1, 
        duration: 1.2, 
        ease: "power4.out"
    });
});
</script>

</body>
</html>