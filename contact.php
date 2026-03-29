<?php
 require_once 'db_connection.php';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = htmlspecialchars($_POST["name"]);
  $email = htmlspecialchars($_POST["email"]);
  $subject = htmlspecialchars($_POST["subject"]);
  $message = htmlspecialchars($_POST["message"]);

   
  $to = " mirasharrouf0@gmail.com";  
  $headers = "From: $name <$email>\r\n";
  $headers .= "Reply-To: $email\r\n";
  $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

  $body = "New message from Lebanon Vibes contact form:\n\n";
  $body .= "Name: $name\n";
  $body .= "Email: $email\n";
  $body .= "Subject: $subject\n";
  $body .= "Message:\n$message\n";

  if (mail($to, $subject, $body, $headers)) {
    $success = "✅ Your message has been sent successfully!";
  } else {
    $error = "❌ Something went wrong. Please try again.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact – Lebanon Vibes</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <!-- ===== Header ===== -->
  <header class="main-header">
    <div class="logo">
      <span class="short">LV</span>
      <h1>Lebanon Vibes</h1>
    </div>

    <nav id="nav-links">
      <a href="index.php">Home</a>
      <a href="about.php">About</a>
      <a href="contact.php" class="active">Contact</a>
      <a href="login.php">Login</a>
    </nav>

    <div id="menu-toggle" class="menu-icon">☰</div>
  </header>

  <!-- ===== Contact Section ===== -->
  <main class="contact-page">
    <section class="contact-hero">
      <h2>Get in Touch</h2>
      <p>We’d love to hear from you! Send us your feedback, questions, or partnership ideas.</p>
    </section>

    <section class="contact-content">
      <div class="contact-form">
        <?php if (!empty($success)) echo "<p class='success-msg'>$success</p>"; ?>
        <?php if (!empty($error)) echo "<p class='error-msg'>$error</p>"; ?>

        <form action="contact.php" method="POST">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" required />

          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required />

          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" required />

          <label for="message">Message</label>
          <textarea id="message" name="message" rows="5" required></textarea>

          <button type="submit" class="btn">Send Message</button>
        </form>
      </div>

      <div class="contact-info">
        <h3>📍 Our Office</h3>
        <p>Beirut, Lebanon</p>

        <h3>📞 Call Us</h3>
        <p>+961  76787933</p>

        <h3>✉️ Email</h3>
        <p>support@lebanonvibes.com</p>

        <h3>🕓 Hours</h3>
        <p>Monday – Friday: 9:00 AM – 6:00 PM</p>
      </div>
    </section>
  </main>

  <footer>
    <p>© 2025 Lebanon Vibes — Explore. Feel. Connect.</p>
  </footer>

  <script src="script.js"></script>
</body>
</html>
