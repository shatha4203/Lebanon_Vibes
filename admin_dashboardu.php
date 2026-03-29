 <?php
session_start();
include 'db_connection.php';

//  Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    die("Access denied. Admins only.");
}

// Fetch all users (SECOND FILE DB)
$users = $conn->query("SELECT user_id, name, email, user_type FROM users");

// Fetch all events (SECOND FILE DB)
$events = $conn->query("SELECT event_id, event_name FROM events");

// Fetch all businesses (SECOND FILE DB)
$businesses = $conn->query("SELECT business_id, business_name FROM businesses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lebanon Vibes</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* Custom cedar theme */
        :root {
            --cedar: #38761d; 
            --cedar-light: #52b126;
            --cedar-outline: #f3f9f1;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--cedar-outline); 
        }
        .cedar-text { color: var(--cedar); }
        .card-cedar { border: 1px solid rgba(0,0,0,0.05); border-radius: 1.5rem; }

        /* Table Styling */
        .table-container { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #e5e7eb;
        }
        .data-table th { 
            background-color: var(--cedar); 
            color: white; 
            font-weight: 600; 
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .data-table tr:hover { background-color: #f9f9f9; }
    </style>
</head>

<body class="min-h-screen p-4 md:p-8">

<div class="max-w-7xl mx-auto">
    
    <header class="mb-10 text-center">
        <h1 class="text-4xl font-extrabold cedar-text mb-2">
            <i class="fas fa-tools mr-2"></i> Admin Dashboard
        </h1>
        <p class="text-gray-600">Overview of all users, events, and businesses.</p>
    </header>

    <!-- USERS -->
    <div class="section bg-white p-6 card-cedar shadow-xl mb-10">
        <h3 class="text-2xl font-bold mb-4 cedar-text border-b pb-2">
            <i class="fas fa-users mr-2"></i> All Users
        </h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th></tr>
                </thead>
                <tbody>
                    <?php while ($u = $users->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $u['user_id'] ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['user_type']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- EVENTS -->
    <div class="section bg-white p-6 card-cedar shadow-xl mb-10">
        <h3 class="text-2xl font-bold mb-4 cedar-text border-b pb-2">
            <i class="fas fa-calendar-alt mr-2"></i> All Events
        </h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Event Name</th></tr>
                </thead>
                <tbody>
                    <?php while ($e = $events->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $e['event_id'] ?></td>
                            <td><?= htmlspecialchars($e['event_name']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- BUSINESSES -->
    <div class="section bg-white p-6 card-cedar shadow-xl mb-10">
        <h3 class="text-2xl font-bold mb-4 cedar-text border-b pb-2">
            <i class="fas fa-store-alt mr-2"></i> All Businesses
        </h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Business Name</th></tr>
                </thead>
                <tbody>
                    <?php while ($b = $businesses->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $b['business_id'] ?></td>
                            <td><?= htmlspecialchars($b['business_name']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
