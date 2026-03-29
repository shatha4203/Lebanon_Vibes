 <?php

session_start();
require_once'db_connection.php';
require_once'header.php';
$sql = "SELECT u.name AS user_name, r.rating,r.comment AS review_text, r.created_at FROM reviews r JOIN users u ON r.user_id = u.user_id ORDER BY r.created_at DESC";

$result = $conn->query($sql);

$message = "";

if ($result) {
    if ($result->num_rows == 0) {
        $message = "NO reviews yet be the first to add a review!";
    }
} else {
    $message = "Error fetching reviews" . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users Reviews  </title>
    <style>
        body { font-family: Tahoma, , sans-serif; background-color: #f4f4f9; padding: 20px; text-align: right; }
        .reviews-container { max-width: 800px; margin: 0 auto; }
        .review-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .review-header {
            display: flex;
            justify-content: space-between; 
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .user-name {
            font-weight: bold;
            color: #333;
            font-size: 1.1em;
        }
        .rating {
            color: gold;
            font-size: 1.2em;
        }
        .review-text {
            color: #555;
            line-height: 1.6;
        }
        .no-reviews {
            padding: 20px;
            text-align: center;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <div class="reviews-container">
        <h1>Users Reviews</h1>
        
        <?php 
        if (!empty($message)) {
            echo '<div class="no-reviews">' . $message . '</div>';
        } 
        
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                ?>
                <div class="review-card">
                    <div class="review-header">
                        <span class="user-name">👤 <?php echo htmlspecialchars($row['user_name']); ?></span>
                        <span class="rating">
                            <?php 
                            $stars = '';
                            for ($i = 0; $i < $row['rating']; $i++) {
                                $stars .= '★';  
                            }
                            for ($i = $row['rating']; $i < 5; $i++) {
                                $stars .= '☆';  
                            }
                            echo $stars;
                            ?>
                        </span>
                    </div>
                    <p class="review-text"><?php echo nl2br(htmlspecialchars($row['review_text'])); ?></p>
                    <?php if (isset($row['created_at'])): ?>
                        <small style="color: #999;"> Date of addition: <?php echo date("Y-m-d", strtotime($row['created_at'])); ?></small>
                    <?php endif; ?>
                </div>
                <?php
            }
        }
        ?>
    </div>
    
</body>
</html>