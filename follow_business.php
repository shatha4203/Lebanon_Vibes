<?php

session_start();
require_once "db_connection.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];
$business_id = intval($_POST['business_id'] ?? 0);
if ($business_id <= 0) die('Invalid business id.');

 
$stmt = $conn->prepare("SELECT 1 FROM user_business WHERE user_id=? AND business_id=?");
$stmt->bind_param("ii", $user_id, $business_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exists) {
    $stmt = $conn->prepare("DELETE FROM user_business WHERE user_id=? AND business_id=?");
    $stmt->bind_param("ii", $user_id, $business_id);
    $stmt->execute();
    $stmt->close();
    header("Location:  business_details.php?id=$business_id");
    exit;
} else {
    $stmt = $conn->prepare("INSERT INTO user_business (user_id, business_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $business_id);
    $stmt->execute();
    $stmt->close();
    header("Location: business_details.php?id=$business_id");
    exit;
}