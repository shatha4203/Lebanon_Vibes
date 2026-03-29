 // weather-toggle.js - small Open-Meteo based body class toggler
(async function(){
  try{
    // Beirut fallback coords; you can change to user-specific
    const lat = 33.8938, lon = 35.5018;
    const resp = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`);
    const data = await resp.json();
    const code = data?.current_weather?.weathercode ?? null;
    if (code === null) return;
    // mapping (simplified)
    if (code === 0) document.body.classList.add('sunny');
    else if ((code >= 51 && code <= 67) || (code >= 80 && code <= 82)) document.body.classList.add('rainy');
    else if ((code >= 71 && code <= 77) || (code >= 85 && code <= 86)) document.body.classList.add('snow');
    else document.body.classList.add('cloudy');
  }catch(e){
    console.warn('Weather toggle failed', e);
  }
})();
