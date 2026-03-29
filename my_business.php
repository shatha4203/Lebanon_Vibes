<?php
session_start();
include "db_connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT business_id, name, category, created_at, location
    FROM businesses 
    WHERE added_by = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Businesses</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
body {
    background: linear-gradient(135deg, #e9f7ef, #f8fdfb);
}

.page-container {
    max-width: 1200px;
    margin: auto;
    padding: 2rem 1rem;
}

.business-card {
    border: none;
    border-radius: 1.25rem;
    box-shadow: 0 12px 30px rgba(0,0,0,0.06);
    transition: transform .2s ease, box-shadow .2s ease;
}

.business-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 45px rgba(0,0,0,0.1);
}

.action-btn {
    border-radius: 0.6rem;
    padding: .4rem .75rem;
    font-size: .85rem;
}

.badge-location {
    padding: .5em .75em;
    border-radius: .75rem;
    font-weight: 500;
}

.header-actions a {
    margin-left: .5rem;
}
</style>
</head>

<body>

<div class="page-container">

    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h1 class="fw-bold text-success mb-3 mb-md-0">
            🏢 My Businesses
        </h1>

        <div class="header-actions">
            <a href="my_events.php" class="btn btn-outline-success btn-lg">
                🎉 My Events
            </a>

            <a href="add_business.php" class="btn btn-success btn-lg shadow-sm">
                ➕ Add Business
            </a>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✅ Business deleted successfully
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>

        <div class="card p-5 text-center business-card">
            <div class="display-5 mb-3">🧐</div>
            <h4 class="fw-bold">No Businesses Yet</h4>
            <p class="text-muted">Start by adding your first business.</p>
            <a href="add_business.php" class="btn btn-success mt-3">
                Add Business
            </a>
        </div>

    <?php else: ?>

        <div class="row g-4">
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            $has_location = !empty($row['location']);
            if ($has_location) {
                [$lat, $lng] = explode(",", $row['location']);
                $map = "https://www.google.com/maps/search/?api=1&query=$lat,$lng";
                $dir = "https://www.google.com/maps/dir//{$lat},{$lng}";
            }
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card business-card h-100 p-4">

                    <h4 class="fw-bold text-success mb-1">
                        <?= htmlspecialchars($row['name']) ?>
                    </h4>

                    <div class="text-muted small mb-2">
                        <?= htmlspecialchars($row['category']) ?>
                    </div>

                    <div class="small text-muted mb-3">
                        Created: <?= date('M d, Y', strtotime($row['created_at'])) ?>
                    </div>

                    <?php if ($has_location): ?>
                        <span class="badge bg-success badge-location mb-3">
                            📍 Location Saved
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary badge-location mb-3">
                            No Location
                        </span>
                    <?php endif; ?>

                    <div class="d-flex flex-wrap gap-2 mt-auto">

                        <a href="business_details.php?id=<?= $row['business_id'] ?>"
                           class="btn btn-outline-info action-btn">
                           👁 View
                        </a>

                        <a href="edit_business.php?id=<?= $row['business_id'] ?>"
                           class="btn btn-outline-success action-btn">
                           ✏ Edit
                        </a>

                        <a href="add_event.php?business_id=<?= $row['business_id'] ?>"
                           class="btn btn-outline-warning action-btn">
                           📅 Event
                        </a>

                        <!-- ⭐ REVIEWS -->
                        <a href="view_reviews.php?business_id=<?= $row['business_id'] ?>"
                           class="btn btn-outline-primary action-btn">
                           ⭐ Reviews
                        </a>

                        <?php if ($has_location): ?>
                            <a href="<?= $map ?>" target="_blank"
                               class="btn btn-outline-dark action-btn">
                               📍 Map
                            </a>

                            <a href="<?= $dir ?>" target="_blank"
                               class="btn btn-outline-secondary action-btn">
                               🧭 Go
                            </a>
                        <?php endif; ?>

                        <a href="delete_business.php?id=<?= $row['business_id'] ?>"
                           onclick="return confirm('Delete <?= htmlspecialchars($row['name']) ?>?');"
                           class="btn btn-outline-danger action-btn">
                           🗑 Delete
                        </a>

                    </div>

                </div>
            </div>
        <?php endwhile; ?>
        </div>

    <?php endif; ?>

    <div class="mt-5">
        <a href="index.php" class="btn btn-outline-secondary">
            🏠 Home
        </a>
    </div>

    <?php include "footer.php"; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
