 <?php
session_start();
include 'db_connection.php';
include 'header.php';
 
$event_id = (int)($_GET['id'] ?? 0);
if ($event_id <= 0) {
    echo "<p>Invalid event.</p>";
    exit;
}
 
$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND approves = 1");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    echo "<p>Event not found or not approved.</p>";
    exit;
}
 
$business = null;
if (!empty($event['business_id'])) {
    $business_stmt = $conn->prepare("SELECT * FROM businesses WHERE business_id = ?");
    if ($business_stmt) {
        $business_stmt->bind_param("i", $event['business_id']);
        $business_stmt->execute();
        $bresult = $business_stmt->get_result();
        $business = $bresult->fetch_assoc();
        $business_stmt->close();
    }
}
if (!$business) {
    $business = ['name' => 'Unknown Organizer', 'phone' => 'N/A', 'media_url' => '', 'email' => ''];
} 
$seat_prices = [
    'third'  => isset($event['third_price'])  ? (float)$event['third_price']  : (float)$event['base_price'],
    'second' => isset($event['second_price']) ? (float)$event['second_price'] : (float)$event['base_price'],
    'vip'    => isset($event['vip_price'])    ? (float)$event['vip_price']    : (float)$event['base_price'],
];

// Remaining seats
$max_attendees = (int)($event['max_attendees'] ?? 0);
$booked_tickets = 0;
$check_stmt = $conn->prepare("SELECT SUM(nb_tickets) as total_booked FROM user_event WHERE event_id = ?");
$check_stmt->bind_param("i", $event_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$row = $check_result->fetch_assoc();
$booked_tickets = (int)($row['total_booked'] ?? 0);
$check_stmt->close();
$remaining_seats = $max_attendees > 0 ? max(0, $max_attendees - $booked_tickets) : 'Unlimited';

// Booking processing
$success = false;
$error = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = (int)$_SESSION['user_id'];
    $ticket_count = max(1, (int)($_POST['nb_tickets'] ?? 1));
    $seat_type = $_POST['seat_type'] ?? 'third';
    $total_amount = floatval(str_replace(',', '', ($_POST['total_amount'] ?? 0)));
    $payment_phone = trim($_POST['payment_phone'] ?? '');
    $transaction_id = trim($_POST['transaction_id'] ?? '');
    $recipient_phone = $business['phone'] ?? '';

    // Check remaining seats
    if ($max_attendees > 0 && ($booked_tickets + $ticket_count) > $max_attendees) {
        $error = true;
        $error_message = "Sorry, only " . ($max_attendees - $booked_tickets) . " tickets are remaining.";
    } elseif ($total_amount <= 0) {
        $error = true;
        $error_message = "Invalid total amount.";
    } elseif ($ticket_count <= 0) {
        $error = true;
        $error_message = "Please choose at least 1 ticket.";
    } elseif (empty($payment_phone) || empty($transaction_id)) {
        $error = true;
        $error_message = "Please provide your phone number and the Wish Money transaction ID.";
    } else {
        // Upload proof
        $proof_image_url = null;
        if (!empty($_FILES['proof_image_url']['name'])) {
            $uploadDir = __DIR__ . "/uploads/proofs/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

            $ext = pathinfo($_FILES['proof_image_url']['name'], PATHINFO_EXTENSION);
            $safeName = time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
            $targetFile = $uploadDir . $safeName;

            if (move_uploaded_file($_FILES['proof_image_url']['tmp_name'], $targetFile)) {
                $proof_image_url = "uploads/proofs/" . $safeName;
            }
        }

        // Insert booking
        $registration = 1;
        $payment_status = 'pending';
        $insert_sql = "INSERT INTO user_event 
            (user_id, event_id, nb_tickets, registration, seat_type, total_amount, payment_phone, recipient_phone, transaction_id, proof_image_url, payment_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $ins_stmt = $conn->prepare($insert_sql);

        if ($ins_stmt) {
            $ins_stmt->bind_param(
                "iiiisdsssss",
                $user_id,
                $event_id,
                $ticket_count,
                $registration,
                $seat_type,
                $total_amount,
                $payment_phone,
                $recipient_phone,
                $transaction_id,
                $proof_image_url,
                $payment_status
            );

            if ($ins_stmt->execute()) {
                $success = true;

                // Notify organizer via email
                if (!empty($business['email'])) {
                    $to = $business['email'];
                    $subject = "New Ticket Booking for {$event['title']}";
                    $message = "
Hello {$business['name']},

A new booking has been made for your event: {$event['title']}.

Details:
- User ID: {$user_id}
- Tickets: {$ticket_count}
- Seat type: {$seat_type}
- Total amount: \${$total_amount}
- Payment Phone: {$payment_phone}
- Transaction ID: {$transaction_id}

Please verify the payment and send a confirmation email to the user.

Thanks,
Lebanon Vibes
";
                    $headers = "From: no-reply@lebanonvibes.com\r\n";
                    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                    mail($to, $subject, $message, $headers);
                }

                // Insert notification into DB
                $notify_sql = "INSERT INTO notifications 
                    (user_id, event_id, type, message, status, created_at) 
                    VALUES (?, ?, 'booking', ?, 'unread', NOW())";
                $notify_stmt = $conn->prepare($notify_sql);
                if ($notify_stmt) {
                    $notify_msg = "New booking for event '{$event['title']}' by user {$user_id}. Total: \${$total_amount}";
                    $organizer_id = $business['added_by'] ?? 0;
                    $notify_stmt->bind_param("iis", $organizer_id, $event_id, $notify_msg);
                    $notify_stmt->execute();
                    $notify_stmt->close();
                }

            } else {
                $error = true;
                $error_message = "Database insert failed: " . $ins_stmt->error;
            }
            $ins_stmt->close();
        } else {
            $error = true;
            $error_message = "Prepare statement failed: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Event - <?= htmlspecialchars($event['title']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script>
tailwind.config = {
    theme: { extend: { colors: { 'cedar': '#38761d', 'cedar-light': '#52b126', 'cedar-outline': '#f3f9f1' } } }
}
</script>
<style>
body { font-family: 'Inter', sans-serif; background-color: #f3f9f1; }
input:focus, select:focus { box-shadow: 0 0 0 3px rgba(56,118,29,0.35); border-color: #38761d; }
.btn-cedar { background-color: #38761d; color: white; transition: background-color 0.2s, transform 0.1s; }
.btn-cedar:hover { background-color: #52b126; }
.modal { transition: opacity 0.3s ease-out, visibility 0.3s ease-out; }
</style>
</head>
<body>
<main class="max-w-7xl mx-auto p-4 md:p-8">

<!-- Event Header -->
<a href="eventdetails.php?id=<?= $event_id ?>" class="text-cedar hover:text-cedar-light font-medium mb-4 block">
<i class="fas fa-arrow-left mr-2"></i>Back to Event Details
</a>

<div class="bg-white rounded-xl shadow-2xl p-8 md:p-10">
<h1 class="text-4xl font-extrabold text-gray-900 mb-6">Book: <?= htmlspecialchars($event['title']) ?></h1>

<div class="grid md:grid-cols-3 gap-6">
<div class="md:col-span-2">
<div class="p-6 bg-gray-50 rounded-xl shadow-lg border border-gray-200 mb-6">
<h2 class="text-2xl font-bold text-gray-900 mb-2">Event details</h2>
<p class="text-gray-700 mb-1"><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
<p class="text-gray-700 mb-1"><strong>Date:</strong> <?= htmlspecialchars($event['date_from']) ?><?= !empty($event['date_to']) ? " → " . htmlspecialchars($event['date_to']) : "" ?></p>
<p class="text-gray-700 mb-1"><strong>Organizer:</strong> <?= htmlspecialchars($business['name']) ?></p>
<p class="text-gray-700"><strong>Organizer Phone:</strong> <?= htmlspecialchars($business['phone']) ?></p>
</div>

<div class="p-6 bg-gray-50 rounded-xl shadow-lg border border-gray-200">
<h2 class="text-2xl font-bold text-gray-900 mb-4">Secure Your Spot</h2>
<p class="text-lg text-gray-700 mb-6">
Ticket prices start at <strong>$<?= number_format((float)$event['base_price'], 2) ?></strong>.
</p>
<button onclick="openRegistrationModal()" class="btn-cedar w-full py-4 text-xl rounded-lg font-bold shadow-xl flex items-center justify-center">
<i class="fas fa-ticket-alt mr-3"></i> Get Tickets Now
</button>
</div>
</div>

<aside class="bg-white rounded-xl p-6 shadow text-sm">
<h3 class="font-semibold mb-3">Quick info</h3>
<p class="mb-2"><strong>Hosted by:</strong> <?= htmlspecialchars($business['name']) ?></p>
<p class="mb-2"><strong>Phone:</strong> <?= htmlspecialchars($business['phone']) ?></p>
<?php if (!empty($business['media_url'])): ?>
<img src="<?= htmlspecialchars($business['media_url']) ?>" alt="Business image" class="mt-4 w-full rounded-lg" style="object-fit:cover;">
<?php endif; ?>
</aside>
</div>
</div>

</main>

<!-- Registration Modal -->
<div id="registration-modal" class="modal fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center p-4 z-50 overflow-y-auto">
<div class="bg-white p-6 md:p-8 rounded-xl max-w-lg w-full shadow-2xl relative my-8">
<button onclick="closeRegistrationModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 transition"><i class="fas fa-times text-2xl"></i></button>
<h2 class="text-3xl font-bold cedar-text mb-6">Event Registration</h2>

<form id="registration-form" method="POST" enctype="multipart/form-data">

<input type="hidden" name="event_id" value="<?= $event_id ?>">

<div id="step-1">
<h3 class="text-xl font-semibold mb-4 text-gray-800">1. Select Tickets & Seating</h3>

<div class="mb-4">
<p class="text-sm font-medium text-gray-700 mb-1">
Seats Remaining: <?= $remaining_seats ?>
</p>
<label for="nb_tickets" class="block text-sm font-medium text-gray-700 mb-1">Number of Tickets</label>
<input type="number" id="nb_tickets" name="nb_tickets" min="1"
<?php if ($remaining_seats !== 'Unlimited'): ?> max="<?= $remaining_seats ?>" <?php endif; ?>
value="1" required onchange="calculateTotal()" class="w-full p-3 border border-gray-300 rounded-lg">
</div>

<div class="mb-6">
<label for="seat_type" class="block text-sm font-medium text-gray-700 mb-1">Seating Tier</label>
<select id="seat_type" name="seat_type" required onchange="calculateTotal()" class="w-full p-3 border border-gray-300 rounded-lg">
<option value="third" data-price="<?= number_format($seat_prices['third'], 2, '.', '') ?>">General (<?= '$' . number_format($seat_prices['third'], 2) ?>)</option>
<option value="second" data-price="<?= number_format($seat_prices['second'], 2, '.', '') ?>">Premium (<?= '$' . number_format($seat_prices['second'], 2) ?>)</option>
<option value="vip" data-price="<?= number_format($seat_prices['vip'], 2, '.', '') ?>">VIP (<?= '$' . number_format($seat_prices['vip'], 2) ?>)</option>
</select>
</div>

<div class="p-4 bg-cedar-outline rounded-lg flex justify-between items-center mb-6">
<span class="text-lg font-semibold text-gray-700">Estimated Total:</span>
<span id="total-amount-display" class="text-2xl font-extrabold cedar-text"><?= '$' . number_format($seat_prices['third'], 2) ?></span>
<input type="hidden" id="total_amount" name="total_amount" value="<?= number_format($seat_prices['third'], 2, '.', '') ?>">
</div>

<button type="button" onclick="nextStep(2)" class="btn-cedar w-full py-3 rounded-lg font-bold">Continue to Payment <i class="fas fa-arrow-right ml-2"></i></button>
</div>

<div id="step-2" class="hidden">
<h3 class="text-xl font-semibold mb-4 text-gray-800">2. Payment via Wish Money</h3>
<div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 text-sm mb-4">
<p class="font-semibold text-yellow-800">Important:</p>
<p class="text-yellow-700">Please send <strong id="payment-required-display">$<?= number_format($seat_prices['third'], 2) ?></strong> via Wish Money to the Recipient Phone shown below before completing the form.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="mb-4">
<label for="payment_phone" class="block text-sm font-medium text-gray-700 mb-1">Your (Sender) Phone #</label>
<input type="tel" id="payment_phone" name="payment_phone" placeholder="7x xxxxx xxx" required class="w-full p-3 border border-gray-300 rounded-lg">
</div>
<div class="mb-4">
<label for="recipient_phone_display" class="block text-sm font-medium text-gray-700 mb-1">Recipient Phone #</label>
<input type="tel" id="recipient_phone_display" name="recipient_phone_display" value="<?= htmlspecialchars($business['phone']) ?>" readonly required class="w-full p-3 border border-gray-300 bg-gray-100 rounded-lg">
<p class="text-xs text-gray-500 mt-1">Organizer: <?= htmlspecialchars($business['name']) ?></p>
</div>
</div>

<div class="mb-4">
<label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-1">Wish Money Transaction ID</label>
<input type="text" id="transaction_id" name="transaction_id" placeholder="Enter the transfer reference" required class="w-full p-3 border border-gray-300 rounded-lg">
</div>

<div class="mb-6">
<label for="proof_image_url" class="block text-sm font-medium text-gray-700 mb-1">Upload Payment Proof (Screenshot)</label>
<input type="file" id="proof_image_url" name="proof_image_url" accept="image/*" required class="w-full p-3 border border-gray-300 rounded-lg bg-white">
<p class="text-xs text-gray-500 mt-1">Helps speed up ticket verification.</p>
</div>

<div class="flex space-x-3">
<button type="button" onclick="nextStep(1)" class="w-1/3 py-3 rounded-lg font-bold text-gray-700 bg-gray-200 hover:bg-gray-300"><i class="fas fa-arrow-left mr-2"></i> Back</button>
<button type="submit" class="btn-cedar w-2/3 py-3 rounded-lg font-bold">Complete Registration</button>
</div>
</div>

</form>
</div>
</div>

<!-- Status Modal -->
<div id="status-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
<div class="bg-white p-6 rounded-xl max-w-sm w-full shadow-2xl text-center">
<div id="modal-icon" class="text-5xl mb-4"></div>
<h4 id="modal-title-status" class="text-2xl font-bold mb-3"></h4>
<p id="modal-message-status" class="text-gray-600 mb-4"></p>
<button onclick="closeStatusModal()" class="btn-cedar px-6 py-2 rounded-full font-medium">OK</button>
</div>
</div>

<script>
const modal = document.getElementById('registration-modal');
const step1 = document.getElementById('step-1');
const step2 = document.getElementById('step-2');
const totalAmountInput = document.getElementById('total_amount');
const totalAmountDisplay = document.getElementById('total-amount-display');
const paymentRequiredDisplay = document.getElementById('payment-required-display');

const SEAT_PRICES = <?= json_encode($seat_prices, JSON_HEX_TAG) ?>;
const CURRENCY = '<?= '$' ?>';

function openRegistrationModal() { nextStep(1); calculateTotal(); modal.classList.remove('hidden'); modal.classList.add('flex'); }
function closeRegistrationModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }
function nextStep(step) { step1.classList.add('hidden'); step2.classList.add('hidden'); if(step===1) step1.classList.remove('hidden'); else if(step===2){ const tickets=parseInt(document.getElementById('nb_tickets').value); if(!tickets||tickets<1){ showStatusModal('Input Required','Please select at least one ticket.','⚠️','text-yellow-600'); return; } calculateTotal(); step2.classList.remove('hidden'); } }

function calculateTotal(){
let numTickets = parseInt(document.getElementById('nb_tickets').value) || 0;
const seatType = document.getElementById('seat_type').value;
const basePrice = parseFloat(SEAT_PRICES[seatType]) || 0;
const remainingSeatsEl = document.getElementById('remaining-seats');
if(remainingSeatsEl){
    const remainingSeats = parseInt(remainingSeatsEl.textContent) || 0;
    if(numTickets>remainingSeats){ numTickets=remainingSeats; document.getElementById('nb_tickets').value=remainingSeats; }
}
const total=(numTickets*basePrice).toFixed(2);
totalAmountInput.value=total; totalAmountDisplay.textContent=`${CURRENCY}${total}`; paymentRequiredDisplay.textContent=`${CURRENCY}${total}`;
}

const statusModal = document.getElementById('status-modal');
const statusModalTitle = document.getElementById('modal-title-status');
const statusModalMessage = document.getElementById('modal-message-status');
const statusModalIcon = document.getElementById('modal-icon');

function showStatusModal(title,message,icon,iconClass){
statusModalTitle.textContent=title; statusModalMessage.innerHTML=message; statusModalIcon.textContent=icon; statusModalIcon.className='text-5xl mb-4 '+(iconClass||''); statusModal.classList.remove('hidden'); statusModal.classList.add('flex');
}

function closeStatusModal(){ statusModal.classList.add('hidden'); statusModal.classList.remove('flex'); }

window.addEventListener('load', calculateTotal);

<?php if($success): ?>
showStatusModal('Registration Submitted!','Your ticket request and payment proof have been sent. Your registration status is currently <strong>PENDING</strong>. The organizer (<?= addslashes(htmlspecialchars($business['name'])) ?>) will verify the payment shortly.','⏳','text-cedar');
<?php elseif($error): ?>
showStatusModal('Registration Failed','<?= addslashes(htmlspecialchars($error_message ?: "An unknown error occurred.")) ?>','❌','text-red-600');
<?php endif; ?>
</script>
</body>
</html>
