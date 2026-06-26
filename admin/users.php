<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'delete' && $id != $_SESSION['user_id']) { // prevent self-deletion
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "User deleted successfully.";
    }
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
            <li><a href="items.php?type=lost">Manage Lost Items</a></li>
            <li><a href="items.php?type=found">Manage Found Items</a></li>
            <li><a href="users.php" class="active">Manage Users</a></li>
            <li><a href="../index.php">Back to Main Site</a></li>
        </ul>
    </div>
    <div class="admin-main">
        <h1 style="margin-bottom: 30px;">Manage Users</h1>
        
        <?php if(isset($msg)): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] == 'admin' ? 'badge-approved' : 'badge-pending'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                                <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user? All their items will be lost.');">Delete User</a>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
