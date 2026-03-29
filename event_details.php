 <?php
include 'db_connection.php';
include 'header.php';

$event_id = (int)($_GET['id'] ?? 0);
if ($event_id <= 0) {
    echo "<p>Invalid event.</p>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND approved = 1");
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
?>

<div class="container">
<h2><?= htmlspecialchars($event['title']) ?></h2>

<p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
<p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
<p><strong>Date:</strong> <?= htmlspecialchars($event['date_from']) ?> 
<?php if($event['date_to']) echo " → ".htmlspecialchars($event['date_to']); ?></p>
<p><strong>Time:</strong> <?= htmlspecialchars($event['time_from']) ?> → <?= htmlspecialchars($event['time_to']) ?></p>

<h3>Prices</h3>
<p><strong>Base Price:</strong> $<?= htmlspecialchars($event['base_price']) ?></p>

<?php if ($event['vip_price']) : ?>
<p><strong>VIP:</strong> $<?= htmlspecialchars($event['vip_price']) ?></p>
<?php endif; ?>

<?php if ($event['second_price']) : ?>
<p><strong>Second Tier:</strong> $<?= htmlspecialchars($event['second_price']) ?></p>
<?php endif; ?>

<?php if ($event['third_price']) : ?>
<p><strong>Third Tier:</strong> $<?= htmlspecialchars($event['third_price']) ?></p>
<?php endif; ?>

<?php if (!empty($_SESSION['user_id'])): ?>
  <a href="join_event.php?id=<?= $event_id ?>" class="btn">Join Event</a>
<?php else: ?>
  <p><a href="login.php">Login</a> to join this event.</p>
<?php endif; ?>

</div>
<div class="row">
  <div class="col-md-8">
    <div class="card card-cedar">
      <img src=" event-sample.jpg" class="card-img-top">
      <div class="card-body">
        <h2 class="card-title"><?=htmlspecialchars($event['title'] ?? 'Event')?></h2>
        <p class="text-muted small"><?=htmlspecialchars($event['location'] ?? '')?> • <?=htmlspecialchars($event['date_from'] ?? '')?></p>
        <p><?=nl2br(htmlspecialchars($event['description'] ?? ''))?></p>
        <h5>Prices</h5>
        <p><strong>Base:</strong> $<?=htmlspecialchars($event['base_price'] ?? '')?></p>
        <?php if (!empty($event['vip_price'])): ?><p><strong>VIP:</strong> $<?=htmlspecialchars($event['vip_price'])?></p><?php endif; ?>
        <?php if (!empty($event['second_price'])): ?><p><strong>Second:</strong> $<?=htmlspecialchars($event['second_price'])?></p><?php endif; ?>
      </div>
    </div>
  </div>

  <aside class="col-md-4">
    <div class="card card-cedar p-3">
      <h6>Organizer</h6>
      <p class="small"><?=htmlspecialchars($business_name ?? '')?></p>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="/join_event.php?id=<?=$event['event_id']?>" class="btn btn-cedar btn-sm">Join Event</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-cedar btn-sm">Login to join</a>
      <?php endif; ?>
    </div>
  </aside>
</div>


<?php include 'footer.php'; ?>
