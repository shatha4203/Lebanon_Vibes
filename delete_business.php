 <?php
// Always start the session before anything else
session_start();
require_once 'db_connection.php';
 
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

//  Get business ID safely
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid business ID.");
}

//  Check if business exists a nd who added it
$stmt = $conn->prepare("SELECT added_by FROM businesses WHERE business_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$business = $result->fetch_assoc();
$stmt->close();

if (!$business) {
    die("Business not found.");
}

//  Check authorization (owner or admin)
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? ''; // Support both names
if ($_SESSION['user_id'] != $business['added_by'] && $user_type !== 'admin') {
    die("Not authorized.");
}

// Use transaction for safe deletion
$conn->begin_transaction();

try {
    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM reviews WHERE business_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM user_business WHERE business_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM business_event WHERE business_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM businesses WHERE business_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $conn->commit();

    header("Location: my_businesses.php?deleted=1");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    die("Delete failed: " . $e->getMessage());
}
?>
