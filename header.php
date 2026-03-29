 <?php
 include "db_connection.php";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Lebanon Vibes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/theme-cedar.css">
</head>
<body>
  <div class="weather-particles"></div>

<nav class="navbar navbar-expand-lg navbar-cedar">
  <div class="container">
    <a class="navbar-brand" href="index.php">🇱🇧 Lebanon Vibes</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
        <li class="nav-item"><a href="event_list.php" class="nav-link">Events</a></li>
        <li class="nav-item"><a href="business_list.php" class="nav-link">Businesses</a></li>
        <li class="nav-item"><a href="about.php" class="nav-link">About</a></li>
        <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>

        <?php if(!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a href="user_dashboard.php" class="nav-link">Dashboard</a></li>
          <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a href="login.php" class="nav-link">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div id="weatherDisplay" class="text-center py-1 small" style="color:var(--cedar); font-weight:600;"></div>

<div class="container my-4">

 