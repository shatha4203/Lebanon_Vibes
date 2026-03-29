 <?php 
include 'header.php';  
?>

<div class="row justify-content-center py-5">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg">
            <div class="card-header bg-cedar text-white fw-bold fs-5">
                Quick Navigation
            </div>
            <div class="list-group list-group-flush">
                <a href="business_list.php" class="list-group-item list-group-item-action p-3"><i class="fas fa-store me-3 text-cedar-dark"></i> Businesses</a>
                <a href="event_list.php" class="list-group-item list-group-item-action p-3"><i class="fas fa-calendar-alt me-3 text-cedar-dark"></i> Events</a>
                <a href="favorites.php" class="list-group-item list-group-item-action p-3"><i class="fas fa-heart me-3 text-cedar-dark"></i> Favorites</a>
                <a href="profile.php" class="list-group-item list-group-item-action p-3"><i class="fas fa-user-circle me-3 text-cedar-dark"></i> Profile</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>