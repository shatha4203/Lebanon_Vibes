 <?php

session_start();

require_once'db_connection.php'; 
require_once'header.php'; 

$message = ''; 
$error = '';   

$user_id = NULL; 

$message = ''; 
$error = '';   

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data from the hidden fields
    $item_type = filter_input(INPUT_POST, 'item_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_FULL_SPECIAL_CHARS); 

    // Initialize the ID columns to NULL
    $business_id = NULL;
    $event_id = NULL;
    $review_id = NULL;

    // Map the submitted item_type to the correct DB column
    if ($item_type === 'business') {
        $business_id = $item_id;
    } elseif ($item_type === 'event') {
        $event_id = $item_id;
    } elseif ($item_type === 'review') {
        $review_id = $item_id;
    }

    // Basic Validation
    if (empty($item_type) || empty($item_id) || empty($reason) || empty($details)) {
        $error = 'Please fill out all required fields.';
    } elseif (!$business_id && !$event_id && !$review_id) {
        $error = 'Invalid item type or ID submitted.';
    } else {
        // --- 3. Database Insertion (Prepared Statement) ---
        $sql = "INSERT INTO reports (user_id, business_id, event_id, review_id, reason, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())"; 

        if ($stmt = $conn->prepare($sql)) {
            // 'iiiis': i=NULL user_id, iii=business/event/review IDs, s=reason string
            $stmt->bind_param(
                'iiiis', 
                $user_id,
                $business_id,
                $event_id,
                $review_id,
                $details 
            );

            // Execute statement
            if ($stmt->execute()) {
                $message = '✅ Thank you! Your report has been submitted.';
                $_POST = array(); 
            } else {
                $error = '❌ Database error: Could not submit report. ' . $conn->error;
            }
            $stmt->close();
        } else {
            $error = '❌ Internal server error preparing statement: ' . $conn->error;
        }
    }
}

// Get required parameters from URL. استخدام ?? '' لحل مشكلة 'Deprecated' في htmlspecialchars
$initial_type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$initial_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validation check to ensure ID and type were passed via the URL
if (empty($initial_type) || empty($initial_id)) {
    // هذه رسالة تحذير مهمة للمطور (أنت)
    $error = '❌ Error: You must specify the item type and ID in the URL (e.g., ?type=business&id=1).';
}
?>

<div class="container mt-5">
    <h2>Report an Issue</h2>
    <p class="lead">Help us keep Lebanon Vibes safe and accurate. Please report any inappropriate content.</p>
    
    <?php 
    if ($message) { echo "<div class='alert alert-success'>{$message}</div>"; }
    // لا تُظهر رسالة الخطأ إذا كان سببها نقص ال ID في الرابط (التحقق أعلاه يكفي)
    if ($error && empty($message)) { echo "<div class='alert alert-danger'>{$error}</div>"; }
    ?>

    <form action="report_issue.php?type=<?= htmlspecialchars($initial_type ?? ''); ?>&id=<?= htmlspecialchars($initial_id ?? ''); ?>" method="POST" class="needs-validation" novalidate>
        
        <input type="hidden" name="item_type" value="<?= htmlspecialchars($initial_type ?? ''); ?>">
        
        <input type="hidden" name="item_id" value="<?= htmlspecialchars($initial_id ?? ''); ?>">

        <div class="alert alert-info">
            <strong>Reporting:</strong> 
            *<?= ucfirst(htmlspecialchars($initial_type ?? '')); ?>* with ID: *<?= htmlspecialchars($initial_id ?? ''); ?>*
        </div>
        
        <hr>

        <div class="mb-3">
            <label for="reason" class="form-label">Main Category for Reporting</label>
            <select class="form-select" id="reason" name="reason" required>
                <option value="">-- Select Category --</option>
                <option value="Inappropriate Content">Inappropriate Content/Hate Speech</option>
                <option value="False Information">False or Misleading Information</option>
                <option value="Spam/Advertisement">Spam or Unsolicited Advertisement</option>
                <option value="Copyright Violation">Copyright or Intellectual Property Violation</option>
                <option value="Other">Other</option>
            </select>
            <div class="invalid-feedback">Please select a reason category.</div>
        </div>

        <div class="mb-3">
            <label for="details" class="form-label">Detailed Explanation <span class="text-danger">*</span></label>
            <textarea class="form-control" id="details" name="details" rows="5" required></textarea>
            <div class="form-text">Please provide the details and specific reason for the report.</div>
            <div class="invalid-feedback">A detailed explanation is required.</div>
        </div>
        
        <button type="submit" class="btn btn-danger mt-3">Submit Report</button>
    </form>
</div>

<?php
require_once('footer.php');
?>