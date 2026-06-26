<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// Get stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalLost = $pdo->query("SELECT COUNT(*) FROM lost_items")->fetchColumn();
$totalFound = $pdo->query("SELECT COUNT(*) FROM found_items")->fetchColumn();
$pendingLost = $pdo->query("SELECT COUNT(*) FROM lost_items WHERE status = 'pending'")->fetchColumn();
$pendingFound = $pdo->query("SELECT COUNT(*) FROM found_items WHERE status = 'pending'")->fetchColumn();
$totalMatches = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .admin-sidebar { width: 250px; background: white; height: 100vh; position: fixed; border-right: 1px solid #eee; padding: 20px; }
        .admin-main { margin-left: 250px; padding: 40px; }
        .admin-nav { list-style: none; padding: 0; }
        .admin-nav li { margin-bottom: 10px; }
        .admin-nav a { display: block; padding: 10px; border-radius: 8px; color: var(--text-main); }
        .admin-nav a:hover, .admin-nav a.active { background: var(--primary); color: white; }
    </style>
</head>
<body style="background: var(--bg-color);">
    <div class="admin-sidebar">
        <h2 style="margin-bottom: 30px; font-size: 1.25rem;">Admin Panel</h2>
        <ul class="admin-nav">
            <li><a href="index.php" class="active">Dashboard</a></li>
            <li><a href="items.php?type=lost">Manage Lost Items</a></li>
            <li><a href="items.php?type=found">Manage Found Items</a></li>
            <li><a href="users.php">Manage Users</a></li>
            <li><a href="../index.php">Back to Main Site</a></li>
        </ul>
    </div>
    <div class="admin-main">
        <h1 style="margin-bottom: 30px;">Dashboard Overview</h1>
        
        <div class="grid">
            <div class="card" style="border-left: 4px solid var(--primary);">
                <h3>Total Users</h3>
                <p style="font-size: 2rem; font-weight: bold; margin-top: 10px;"><?php echo $totalUsers; ?></p>
            </div>
            <div class="card" style="border-left: 4px solid var(--danger);">
                <h3>Lost Items</h3>
                <p style="font-size: 2rem; font-weight: bold; margin-top: 10px;"><?php echo $totalLost; ?></p>
                <small style="color: var(--danger);"><?php echo $pendingLost; ?> pending approval</small>
            </div>
            <div class="card" style="border-left: 4px solid var(--secondary);">
                <h3>Found Items</h3>
                <p style="font-size: 2rem; font-weight: bold; margin-top: 10px;"><?php echo $totalFound; ?></p>
                <small style="color: var(--secondary);"><?php echo $pendingFound; ?> pending approval</small>
            </div>
            <div class="card" style="border-left: 4px solid #F59E0B;">
                <h3>Total Matches</h3>
                <p style="font-size: 2rem; font-weight: bold; margin-top: 10px;"><?php echo $totalMatches; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
