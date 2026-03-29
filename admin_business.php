<?php
session_start();
include 'db_connection.php';

// 1. Check Admin Permissions
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Handle Actions
if (isset($_GET['action']) && isset($_GET['b_id'])) {
    $business_id = intval($_GET['b_id']);
    
    if ($_GET['action'] === 'approve') {
        $sql = "UPDATE businesses SET is_approved = 1 WHERE business_id = $business_id";
    } elseif ($_GET['action'] === 'reject') {
        $sql = "DELETE FROM businesses WHERE business_id = $business_id";
    }

    if (isset($sql) && $conn->query($sql) === TRUE) {
        $msg = "Success: Status updated!";
    } else {
        $msg = "Error updating database: " . $conn->error;
    }
}
?>

<tbody>
    <?php
    // We only show businesses where is_approved is 0
    $result = $conn->query("SELECT * FROM businesses WHERE is_approved = 0");

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td><strong>" . htmlspecialchars($row['name']) . "</strong></td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['phone']) . "</td>
                    <td>
                        <a href='admin_business.php?action=approve&b_id=" . $row['business_id'] . "' class='btn btn-approve'>Approve</a>
                        <a href='admin_business.php?action=reject&b_id=" . $row['business_id'] . "' class='btn btn-reject' onclick='return confirm(\"Reject this?\")'>Reject</a>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4' class='no-data'>No pending business requests found.</td></tr>";
    }
    ?>
</tbody>