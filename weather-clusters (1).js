const map = L.map('map').setView([33.85, 35.86], 9);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

const cluster = L.markerClusterGroup();
map.addLayer(cluster);

let activeWeather = ['sunny','cloudy','rainy','snowy'];
let activeTime = 'all';

function validTime(d) {
  const date = new Date(d);
  const now = new Date();

  if (activeTime === 'today')
    return date.toDateString() === now.toDateString();

  if (activeTime === 'weekend')
    return [0,6].includes(date.getDay());

  return true;
}

function rebuild() {
  cluster.clearLayers();

  EVENTS.forEach(e => {
    if (!activeWeather.includes(e.weather_tag)) return;
    if (!validTime(e.start_date)) return;

    cluster.addLayer(
      L.marker([e.lat, e.lon])
       .bindPopup(`<b>${e.title}</b><br>${e.location}`)
    );
  });
}

document.querySelectorAll('input[type=checkbox]').forEach(cb =>
  cb.onchange = () => {
    activeWeather = [...document.querySelectorAll('input:checked')].map(x=>x.value);
    rebuild();
  }
);

document.querySelectorAll('[data-time]').forEach(b =>
  b.onclick = () => { activeTime = b.dataset.time; rebuild(); }
);

rebuild();
