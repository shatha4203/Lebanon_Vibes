 <?php include 'header.php'; ?>
 
<section class="hero-cedar mb-5 text-center card-cedar">
  <div class="container py-5">
    <img src=" cedar_logo.svg" style="height:120px; margin-bottom:20px;" alt="Cedar Logo">

    <h1 class="fw-bold display-6" style="color:var(--cedar);">
      Discover Lebanon by Vibe, Weather & Experiences
    </h1>

    <p class="lead mt-3 text-muted mx-auto" style="max-width:650px;">
      Explore local spots, authentic vibes, and events tailored to the weather 🌿🇱🇧
    </p>

    <div class="mt-4 d-flex justify-content-center gap-3 flex-wrap">
      <a href="event_list.php" class="btn btn-cedar btn-lg px-4">Explore Events</a>
      <a href="business_list.php" class="btn btn-cedar-outline btn-lg px-4">Discover Businesses</a>
    </div>
  </div>
</section>

<!-- Search Bar -->
<div class="container mb-5" style="max-width:800px;">
  <div class="card card-cedar p-4">
    <form action="event_list.php" method="GET" class="input-group">
      <input type="text" name="search" class="form-control form-control-lg"
             placeholder="Search events, locations, vibes...">
      <button class="btn btn-cedar btn-lg">Search</button>
    </form>
  </div>
 <div  class="mt-3 d-flex gap-2 flex-wrap justify-content-center">
      <div class="categories">
        <button>Adventure</button>
        <button>Relax</button>
        <button>Romantic</button>
        <button>Party</button>
        <button>Culture</button>
      </div>
         
</div>
  
<!-- Featured Events -->
<div class="container text-center mb-4">
  <h3 class="fw-bold" style="color:var(--cedar)">Popular Vibes This Week</h3>
  <p class="text-muted">Handpicked recommendations for you</p>
</div>

<div class="container">
  <div class="row g-4 justify-content-center">

    <!-- Event Card -->
    <div class="col-md-4">
      <div class="card card-cedar h-100 text-center">
        <img src=" event-sample.jpg" class="card-img-top" style="height:210px; object-fit:cover;">
        <div class="card-body">
          <h5 class="fw-bold">Beirut Jazz Night</h5>
          <p class="small text-muted">Beirut • Sat, Feb 25</p>
          <p>Cozy indoor jazz ✨</p>
          <span class="badge badge-cedar">From $15</span>
        </div>
        <div class="card-footer bg-transparent">
          <a href="event_details.php?id=1" class="btn btn-cedar btn-sm px-3">View Details</a>
        </div>
      </div>
    </div>

    <!-- Event Card -->
    <div class="col-md-4">
      <div class="card card-cedar h-100 text-center">
        <img src=" event-sample.jpg" class="card-img-top" style="height:210px; object-fit:cover;">
        <div class="card-body">
          <h5 class="fw-bold">Batroun Sunset Yoga</h5>
          <p class="small text-muted">Batroun • Sun, Feb 26</p>
          <p>Wellness by the sea 🌅</p>
          <span class="badge badge-cedar">Free</span>
        </div>
        <div class="card-footer bg-transparent">
          <a href="event_details.php?id=2" class="btn btn-cedar btn-sm px-3">View Details</a>
        </div>
      </div>
    </div>

    <!-- Event Card -->
    <div class="col-md-4">
      <div class="card card-cedar h-100 text-center">
        <img src=" event-sample.jpg" class="card-img-top" style="height:210px; object-fit:cover;">
        <div class="card-body">
          <h5 class="fw-bold">Aley Winter Brunch</h5>
          <p class="small text-muted">Aley • Sat, Feb 26</p>
          <p>Mountain food & chill vibes ❄️</p>
          <span class="badge badge-cedar">From $12</span>
        </div>
        <div class="card-footer bg-transparent">
          <a href="event_details.php?id=3" class="btn btn-cedar btn-sm px-3">View Details</a>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Chat Bubble -->
<div id="chat-bubble">💬</div>

<div id="chat-box">
  <div id="chat-header">
    Lebanon Vibes AI 🤖
    <span id="chat-close">✖</span>
  </div>
  <div id="chat-messages"></div>
  <div id="chat-input">
    <input type="text" id="user-message" placeholder="Ask me anything..." />
    <button onclick="sendMessage()">Send</button>
  </div>
</div>

<style>
#chat-bubble {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #2e7d32;
  color: #fff;
  width: 55px;
  height: 55px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 24px;
  cursor: pointer;
  z-index: 9999;
}

#chat-box {
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: 300px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 5px 20px rgba(0,0,0,.2);
  display: none;
  flex-direction: column;
  z-index: 9999;
}

#chat-header {
  background: #2e7d32;
  color: white;
  padding: 10px;
  font-weight: bold;
  display: flex;
  justify-content: space-between;
}

#chat-messages {
  padding: 10px;
  height: 220px;
  overflow-y: auto;
  font-size: 14px;
}

#chat-input {
  display: flex;
  border-top: 1px solid #ddd;
}

#chat-input input {
  flex: 1;
  border: none;
  padding: 8px;
}

#chat-input button {
  border: none;
  background: #2e7d32;
  color: white;
  padding: 8px 12px;
}
</style>

<script>
const bubble = document.getElementById("chat-bubble");
const box = document.getElementById("chat-box");
const closeBtn = document.getElementById("chat-close");

bubble.onclick = () => box.style.display = "flex";
closeBtn.onclick = () => box.style.display = "none";

function sendMessage() {
  const input = document.getElementById("user-message");
  const msg = input.value.trim();
  if (!msg) return;

  const messages = document.getElementById("chat-messages");
  messages.innerHTML += "<div><b>You:</b> " + msg + "</div>";

  fetch("ai_assistant.php", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({message: msg})
  })
  .then(res => res.json())
  .then(data => {
    messages.innerHTML += "<div><b>AI:</b> " + data.reply + "</div>";
    messages.scrollTop = messages.scrollHeight;
  });

  input.value = "";
}
</script>

<?php include 'footer.php'; ?>
