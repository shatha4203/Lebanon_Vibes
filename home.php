<?php include 'header.php'; ?>

<body class="<?= $weatherClass ?> <?= $theme ?>">

<div id="weather-overlay"></div>

<section class="hero">
  <h1>Discover Lebanon by Weather 🌿</h1>
  <form>
    <input name="city" placeholder="Search city">
    <button>Go</button>
  </form>

  <a href="events_weather_map.php" class="btn">
    View Events by Weather 🌦
  </a>
</section>

<div id="map"></div>

<script>
  window.WEATHER_EFFECT = "<?= $effect ?>";
</script>

<script src="weather-effects.js"></script>

<?php include 'footer.php'; ?>
