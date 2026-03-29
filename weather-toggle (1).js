 (async function () {
  try {
    const lat = 33.8938, lon = 35.5018; // Beirut default
    const r = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`);
    const d = await r.json();

    const c = d?.current_weather?.weathercode;
    const hour = new Date().getHours();
    const isNight = hour >= 19 || hour <= 5;

    // Map conditions
    let icon = "☁";
    if (c === 0) icon = isNight ? "🌙" : "☀";
    else if ((c >= 51 && c <= 67) || (c >= 80 && c <= 82)) icon = isNight ? "🌧🌙" : "🌧";
    else if ((c >= 71 && c <= 77) || (c >= 85 && c <= 86)) icon = "❄";

    // Add body class
    if (isNight) document.body.classList.add("night");
    if (c === 0) document.body.classList.add("sunny");
    else if ((c >= 51 && c <= 67) || (c >= 80 && c <= 82)) document.body.classList.add("rainy");
    else if ((c >= 71 && c <= 77) || (c >= 85 && c <= 86)) document.body.classList.add("snow");
    else document.body.classList.add("cloudy");

    // Display
    const box = document.getElementById("weatherDisplay");
    if (box) {
      const temp = Math.round(d.current_weather.temperature);
      box.innerHTML = `${icon} &nbsp; ${temp}°C`;
    }

  } catch (e) {
    console.warn("Weather error", e);
  }
})();
