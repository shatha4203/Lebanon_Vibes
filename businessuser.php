<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<h2>Register Your Business</h2>
<form action="add-business.php" method="POST">
    <label>Business Name:</label>
    <input type="text" name="business_name" required><br>

    <label>Category:</label>
    <input type="text" name="category" required><br>

    <label>Location:</label>
    <input type="text" name="location" required><br>

    <button type="submit">Next: Add Business Details</button>
</form>