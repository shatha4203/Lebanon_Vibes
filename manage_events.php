 <?php
include 'db_connection.php';
include 'header.php';
session_start();

if (!isset($_SESSION['business_id'])) {
  header("Location: login.php");
  exit();
}
$business_id = $_SESSION['business_id'];
 
$bizStmt = $conn->prepare("SELECT phone FROM businesses WHERE business_id = ?");
$bizStmt->bind_param("i", $business_id);
$bizStmt->execute();
$bizPhone = $bizStmt->get_result()->fetch_assoc()['phone'] ?? 'N/A';

$sql = "SELECT * FROM events WHERE business_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $business_id);
$stmt->execute();
$events = $stmt->get_result();
?>

<div class="container py-4">
  <?php if(isset($_SESSION['event_pending_message'])): ?>
    <div class="alert alert-info mb-3">
      <?= $_SESSION['event_pending_message']; unset($_SESSION['event_pending_message']); ?>
    </div>
  <?php endif; ?>

  <div class="alert alert-warning mb-3">
  💰 <strong>Booking & Payments:</strong> All event bookings are completed via <strong>Wish Money Transfer</strong>. <br>
  📎 <a href="how_to_pay_wish.php" class="fw-bold">How to pay via Wish</a><br>
   📞 Contact us at: <strong><?= htmlspecialchars($bizPhone) ?></strong>

</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Manage My Events</h2>
    <a href="add_event.php" class="btn btn-success">➕ Add New Event</a>
  </div>

  <div class="row">

    <!-- Sidebar -->
    <aside class="col-md-3 mb-4">
      <div class="list-group shadow-sm">
        <a href="add_event.php" class="list-group-item list-group-item-action">➕ Add Event</a>
        <a href="manage_events.php" class="list-group-item list-group-item-action active">📅 My Events</a>
        <a href="my_business.php" class="list-group-item list-group-item-action">🏢 My Business</a>
      </div>
    </aside>

    <!-- Events List -->
    <div class="col-md-9">
      <?php while ($ev = $events->fetch_assoc()): ?>
        <div class="card mb-3 shadow-sm event-item">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <h5 class="fw-bold mb-1"><?= htmlspecialchars($ev['title']) ?></h5>
              <div class="text-muted small mb-2">
                <?= htmlspecialchars($ev['date_from']) ?> → <?= htmlspecialchars($ev['date_to']) ?>
              </div>
              <span class="badge <?= $ev['approved'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                <?= $ev['approved'] ? 'Approved ✅' : 'Pending ⏳' ?>
              </span>
            </div>

            <div>
              <a class="btn btn-outline-secondary btn-sm" href="edit_event.php?id=<?= $ev['event_id'] ?>">✏️ Edit</a>
              <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $ev['event_id'] ?>">🗑 Delete</button>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Are you sure you want to delete this event?</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
      </div>
    </div>
  </div>
</div>

<script>
// Pass event ID to modal
const deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget;
  const id = button.getAttribute('data-id');
  document.getElementById('confirmDeleteBtn').href = `delete_event.php?id=${id}`;
});
</script>

<style>
.event-item:hover {
  transform: scale(1.01);
  transition: 0.2s;
}
</style>

<?php include 'footer.php'; ?>
