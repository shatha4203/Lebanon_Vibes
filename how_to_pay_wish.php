<?php
include 'header.php';
session_start();
?>

<div class="container py-5">
  <h2 class="fw-bold mb-3">How to Pay via Wish Money Transfer</h2>
  <p class="text-muted mb-4">Follow the steps below to complete your event booking securely.</p>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="fw-bold">📌 Step-by-Step Guide</h5>
      <ol class="mt-3">
        <li>Go to a <strong>Wish Money Transfer</strong> location near you.</li>
        <li>Ask to send the payment to the organizer.</li>
        <li>Use the phone/WhatsApp number provided when booking.</li>
        <li>Send us a screenshot of the receipt via WhatsApp.</li>
        <li>After confirmation, your booking will be activated ✅</li>
      </ol>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="fw-bold">📞 Contact for Payment Details</h5>
      <p class="mt-3 mb-1">Phone / WhatsApp:</p>
      <p class="fw-bold text-primary fs-5">+961-12345678</p>
      <a href="https://wa.me/96112345678" target="_blank" class="btn btn-success">💬 Chat on WhatsApp</a>
    </div>
  </div>

  <div class="alert alert-info">
    ⚠️ <strong>Note:</strong> We do <strong>not</strong> handle your money. All transfers are done directly via Wish.
  </div>

  <a href="manage_events.php" class="btn btn-secondary mt-3">⬅ Back</a>
</div>

<?php include 'footer.php'; ?>
