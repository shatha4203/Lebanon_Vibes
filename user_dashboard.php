<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Example: fetch username
$stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
?>
<h2>Welcome, <?= $username ?>!</h2>

<p>What would you like to do?</p>
<!-- Link to register business if the user is a business owner -->
<a href="register_business.php">Register Your Business</a>