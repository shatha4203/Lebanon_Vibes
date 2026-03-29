 <?php
session_start();

require_once'db_connection.php';
require_once'header.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['user_name'] ?? ''; 


$success_message ="";
$error_message = "";

$business_id_value = $_GET['business_id'] ?? null;

// Convert to integer and validate
$business_id_value = is_numeric($business_id_value) ? (int)$business_id_value : null;

//CHECK: Ensure Business ID is present and valid
if ($business_id_value === null || $business_id_value <= 0) {
    $error_message = "Error: Invalid or missing Business ID in the URL. Cannot add review.";
    // If the error message is set here, the POST logic below won't run.
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error_message)) {

    if (!$user_id) {

        $error_message = "You must be logged in to submit a review.";
    }

    $rating = (int)($_POST['rating'] ?? 0);
    $reviewText = htmlspecialchars(trim($_POST['reviewText'] ?? '')); // Maps to 'comment'

    if (
        empty($reviewText) || 
        $rating < 1 || 
        $rating > 5
    ) {
        $error_message = "Please select a rating and fill in the review content."; 
    } else {
       
        $user_id_insert = $user_id;

        
        $sql = "INSERT INTO reviews(user_id, rating, comment, business_id) VALUES(?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            
            $stmt->bind_param("iisi", $user_id_insert, $rating, $reviewText, $business_id_value);
            
            if ($stmt->execute()) {
                $success_message = "Your review was added successfully! Thank you. ❤";
            } else {
                $error_message = "An error occurred while executing the review: " . $stmt->error; 
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing SQL statement: " . $conn->error;
        }
        $conn->close();
    }
}
?>

<body class="body">
<div class="review-box">

    <?php 
    // Display Success or Error Messages
    if (!empty($success_message)) {
        echo '<div class="alert-message success-message">' . $success_message . '</div>';
    } elseif (!empty($error_message)) {
        echo '<div class="alert-message error-message">' . $error_message . '</div>';
    }
    ?>

    <?php if ($user_id && !isset($error_message[0])): // Only show form if logged in AND no fatal errors ?>
        
        <h3>Please Add Your Review Now</h3>
        
        <form method="POST">
            <label for="rating">Rating (5-1):</label>
            <input type="number" name="rating" id="rating" min="1" max="5" required>
            
            <label for="reviewText">Your Review Content:</label>
            <textarea name="reviewText" id="reviewText" rows="5" required></textarea>
            
            <button type="submit" class="submit-btn">Submit Review</button>
        </form>

    <?php elseif (!$user_id): // If the user is NOT logged in ?>
    
        <div class="alert-message warning-message">
            <p><strong>Sorry!</strong> You must <a href="login.php">log in</a> to participate and add a review.</p>
            <p>If you don't have an account, you can <a href="register.php">register here</a>.</p>
        </div>

    <?php endif; ?>
    
</div>
</body>
</html>
<style>
   .body{

    background: linear-gradient(to bottom, #72AECB 0%, #3079A3 100%); 

    min-height: 100vh;
    margin: 0;
    padding: 0;

   }

    .review-box {
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        max-width: 450px; 
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: right; 
    }

    .review-box h3 {
        color: #333;
        border-bottom: 2px solid #00A651;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .review-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #4a4a4a;
    }

    .review-form input[type="text"],
    .review-form input[type="number"],
    .review-form textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box; 
    }

    .review-form textarea {
        resize: vertical; 
    }

    .submit-btn {
        background-color: #8bb337ff;
        color: #FFFFFF; 
    padding: 14px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 1.05em;
    transition: background-color 0.3s, transform 0.2s; 
}

.submit-btn:hover {
    background-color: #8D9B69;      
    transform: translateY(-1px);    
}
    
    
    .message { padding: 10px; margin-bottom: 15px; border-radius: 5px;  }
    .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>