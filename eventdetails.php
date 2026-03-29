 <?php
include 'db_connection.php';
include 'header.php' ;

$event_id = (int)($_GET['id'] ?? 0);
if ($event_id <= 0) {
    echo "<p>Invalid event.</p>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND approves = 1");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    echo "<p>Event not found or not approved.</p>";
    exit;
}

// PHP function to format simple markdown-like text into HTML
function formatMarkdown($markdownText) {
    // Work with the raw text
    $text = (string)$markdownText;

    // Escape HTML first to avoid XSS, then we'll allow safe tags we add ourselves
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // Convert "## Heading" to h4
    $text = preg_replace('/^##\s*(.+)$/m', '<h4 class="text-lg font-bold mt-3 mb-2 text-gray-900">$1</h4>', $text);

    // Convert **bold** (allow multiline inside bold)
    $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);

    // Convert list items starting with "- "
    if (preg_match('/^- /m', $text)) {
        // convert each "- item" line to <li>item</li>
        $text = preg_replace('/^- (.+)$/m', '<li>$1</li>', $text);
        // wrap resulting list items in a ul (there may be surrounding non-list text)
        // But avoid wrapping all content multiple times: find the block of contiguous <li> and wrap them
        $text = preg_replace_callback('/(?:<li>.*?<\/li>)(?:\s*<li>.*?<\/li>)*/s', function($m){
            return '<ul>' . $m[0] . '</ul>';
        }, $text);
    }

    // Wrap leftover lines (that are not tags we've added) into <p>
    // We consider a "line" that does not start with our allowed tags.
    $text = preg_replace_callback('/(^|(?!<h4|<ul|<li|<strong|<p))(?:[^\r\n]+)(?=$|\r|\n)/m', function($m){
        $line = trim($m[0]);
        if ($line === '') return '';
        // If the line already starts with an HTML tag we created, keep it
        if (preg_match('/^\s*<(h4|ul|li|strong|p)/i', $line)) {
            return $line;
        }
        return '<p>' . $line . '</p>';
    }, $text);

    return $text;
}

// Placeholder for AI-generated tips (example)
$ai_tips = "## Event Tips
* **Dress Code**: Smart Casual or Beachwear, depending on location.
* **Parking**: Ample parking is usually available at the main lot.
* **Food/Drink**: Food and beverages are available for purchase on site.";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Detail - <?= htmlspecialchars($event['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script>
        // Tailwind Configuration to define the cedar theme
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'cedar': '#38761d',
                        'cedar-light': '#52b126',
                        'cedar-outline': '#f3f9f1',
                    },
                }
            }
        }
    </script>
    
    <style>
        /* General Styles for the body */
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f3f9f1; /* Use cedar-outline */
        }
    </style>
</head>

<body>
    <header class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto p-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-extrabold text-cedar">Lebanon Vibes</a>
            <nav class="space-x-4">
                <a href="event_list.php" class="text-gray-600 hover:text-cedar font-medium">Events</a>
                <a href="business_list.php" class="text-gray-600 hover:text-cedar font-medium">Businesses</a>
                <a href="login.php" class="bg-cedar hover:bg-cedar-light text-white px-4 py-2 rounded-full text-sm font-medium transition duration-200">Log In</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-4 md:p-8">
        <a href="event_list.php" class="text-cedar hover:text-cedar-light font-medium mb-4 block">
            <i class="fas fa-arrow-left mr-2"></i>Back to Events
        </a>

        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="h-96 bg-gray-300 flex items-center justify-center text-gray-600 text-lg font-semibold bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($event['media_url'] ?? 'placeholder.jpg') ?>');">
                <?php if (empty($event['media_url'])): ?>
                    Event Media Placeholder
                <?php endif; ?>
            </div>

            <div class="p-6 md:p-10">
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2"><?= htmlspecialchars($event['title']) ?></h1>
                <p class="text-xl text-cedar-light font-semibold mb-6">
                    <i class="fas fa-map-marker-alt mr-2"></i><?= htmlspecialchars($event['location']) ?>
                </p>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <h2 class="text-2xl font-bold text-cedar border-b pb-2 mb-4">Description</h2>
                        <p class="text-gray-700 leading-relaxed mb-6">
                            <?= nl2br(htmlspecialchars($event['description'])) ?>
                        </p>
                        
                        <h2 class="text-2xl font-bold text-cedar border-b pb-2 mb-4 mt-8">Event Info</h2>
                        <div class="space-y-3 text-gray-700">
                            <p><strong class="font-semibold text-gray-900"><i class="fas fa-calendar-day w-5 mr-2 text-cedar-light"></i>Date:</strong> 
                                <?= htmlspecialchars($event['date_from']) ?> 
                                <?php if ($event['date_to']): ?>
                                    to <?= htmlspecialchars($event['date_to']) ?>
                                <?php endif; ?>
                            </p>
                            <p><strong class="font-semibold text-gray-900"><i class="fas fa-clock w-5 mr-2 text-cedar-light"></i>Time:</strong> 
                                <?= htmlspecialchars(date('h:i A', strtotime($event['time_from']))) ?>
                                <?php if ($event['time_to']): ?>
                                    - <?= htmlspecialchars(date('h:i A', strtotime($event['time_to']))) ?>
                                <?php endif; ?>
                            </p>
                            <p><strong class="font-semibold text-gray-900"><i class="fas fa-tag w-5 mr-2 text-cedar-light"></i>Type:</strong> 
                                <?= htmlspecialchars($event['event_type']) ?>
                            </p>
                            <p><strong class="font-semibold text-gray-900"><i class="fas fa-users w-5 mr-2 text-cedar-light"></i>Attendees:</strong> 
                                <?php if ($event['max_attendees'] > 0): ?>
                                    Up to <?= htmlspecialchars($event['max_attendees']) ?>
                                <?php else: ?>
                                    Unlimited
                                <?php endif; ?>
                            </p>
                            <p><strong class="font-semibold text-gray-900"><i class="fas fa-pound-sign w-5 mr-2 text-cedar-light"></i>Starting Price:</strong> 
                                $<?= number_format($event['base_price'], 2) ?>
                            </p>
                        </div>

                    </div>
                    
                    <div class="lg:col-span-1">
                        <div class="bg-cedar-outline p-6 rounded-lg shadow-inner mb-8">
                            <h3 class="text-xl font-bold text-cedar mb-4">Ready to Go?</h3>
                            <a href="book_event.php?id=<?= $event_id ?>" class="w-full block text-center bg-cedar hover:bg-cedar-light text-white font-bold py-3 px-4 rounded-full transition duration-200">
                                <i class="fas fa-ticket-alt mr-2"></i>Get Tickets Now
                            </a>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg shadow-md border border-cedar-outline">
                            <h3 class="text-xl font-bold text-cedar mb-4 flex items-center">
                                <i class="fas fa-brain mr-2"></i>Lebanon Vibes AI Tips
                            </h3>
                            <div class="text-gray-700 text-sm">
                                <?php
                                    // Output the formatted AI tips safely (server-side)
                                    echo formatMarkdown($ai_tips);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </main>
</body>
</html>
