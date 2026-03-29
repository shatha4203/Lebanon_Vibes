<?php
// 1. Start session and include database connection
session_start();
include 'db_connection.php';

// 2. Security Check: Only allow logged-in admins
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

// 3. Check if 'type' and 'id' are provided in the URL
if (isset($_GET['type']) && isset($_GET['id'])) {
    
    $item_type = $_GET['type'];
    $item_id = intval($_GET['id']); // Securely convert ID to an integer

    // --- HANDLE EVENT APPROVAL ---
    if ($item_type === 'event' && $item_id > 0) {
        
        // Update 'approves' column to 1 (making it live)
        $sql = "UPDATE events SET approves = 1 WHERE event_id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $item_id);
            
            if ($stmt->execute()) {
                $_SESSION['approval_message'] = "The event was successfully approved! :)";
            } else {
                $_SESSION['approval_message'] = "Error: Could not update the database.";
            }
            $stmt->close();
        }
        // Redirect back to the event approval list
        header('Location: admin_approve_events.php');
        exit;
    } 

    // --- HANDLE BUSINESS APPROVAL (Optional Add-on) ---
    elseif ($item_type === 'business' && $item_id > 0) {
        
        $sql = "UPDATE businesses SET is_approved = 1 WHERE business_id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $item_id);
            if ($stmt->execute()) {
                $_SESSION['approval_message'] = "The business was successfully approved!";
            }
            $stmt->close();
        }
        header('Location: admin_business.php');
        exit;
    }

    else {
        $_SESSION['approval_message'] = "Invalid request type or ID.";
        header('Location: admin_dashboard.php');
        exit;
    }

} else {
    // If someone tries to open this page without clicking a button
    $_SESSION['approval_message'] = "Please select an item to approve.";
    header('Location: admin_dashboard.php');
    exit;
}

$conn->close();
?>