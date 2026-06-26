<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$type = isset($_GET['type']) && $_GET['type'] == 'found' ? 'found_items' : 'lost_items';
$pageTitle = $type === 'found_items' ? 'Manage Found Items' : 'Manage Lost Items';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if (in_array($action, ['approved', 'rejected', 'resolved'])) {
        $stmt = $pdo->prepare("UPDATE $type SET status = ? WHERE id = ?");
        $stmt->execute([$action, $id]);
        $msg = "Item status updated to $action.";
    } elseif ($action == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM $type WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Item deleted successfully.";
    }
}

$stmt = $pdo->query("SELECT i.*, u.name as user_name FROM $type i JOIN users u ON i.user_id = u.id ORDER BY i.created_at DESC");
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .admin-sidebar { width: 250px; background: white; height: 100vh; position: fixed; border-right: 1px solid #eee; padding: 20px; }
        .admin-main { margin-left: 250px; padding: 40px; }
        .admin-nav { list-style: none; padding: 0; }
        .admin-nav li { margin-bottom: 10px; }
        .admin-nav a { display: block; padding: 10px; border-radius: 8px; color: var(--text-main); }
        .admin-nav a:hover, .admin-nav a.active { background: var(--primary); color: white; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: var(--card-shadow); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #F9FAFB; font-weight: 600; }
    </style>
</head>
<body style="background: var(--bg-color);">
    <div class="admin-sidebar">
        <h2 style="margin-bottom: 30px; font-size: 1.25rem;">Admin Panel</h2>
        <ul class="admin-nav">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="items.php?type=lost" class="<?php echo $type == 'lost_items' ? 'active' : ''; ?>">Manage Lost Items</a></li>
            <li><a href="items.php?type=found" class="<?php echo $type == 'found_items' ? 'active' : ''; ?>">Manage Found Items</a></li>
            <li><a href="users.php">Manage Users</a></li>
            <li><a href="../index.php">Back to Main Site</a></li>
        </ul>
    </div>
    <div class="admin-main">
        <h1 style="margin-bottom: 30px;"><?php echo $pageTitle; ?></h1>
        
        <?php if(isset($msg)): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td>
                            <?php if($item['image']): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><?php echo htmlspecialchars($item['user_name']); ?></td>
                        <td>
                            <span class="badge <?php echo $item['status'] == 'pending' ? 'badge-pending' : ($item['status'] == 'approved' ? 'badge-approved' : 'badge-rejected'); ?>">
                                <?php echo ucfirst($item['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="items.php?type=<?php echo $_GET['type'] ?? 'lost'; ?>&action=approved&id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm">Approve</a>
                            <a href="items.php?type=<?php echo $_GET['type'] ?? 'lost'; ?>&action=rejected&id=<?php echo $item['id']; ?>" class="btn btn-outline btn-sm">Reject</a>
                            <a href="items.php?type=<?php echo $_GET['type'] ?? 'lost'; ?>&action=delete&id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($items)): ?>
                    <tr><td colspan="6" style="text-align: center;">No items found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
