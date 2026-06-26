<?php
require_once 'includes/header.php';

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    redirect('search.php');
}

$id = (int)$_GET['id'];
$type = $_GET['type'] === 'lost' ? 'lost_items' : 'found_items';

$stmt = $pdo->prepare("SELECT i.*, u.name as user_name FROM $type i JOIN users u ON i.user_id = u.id WHERE i.id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    echo "<div class='container'><div class='alert alert-error'>Item not found.</div></div>";
    require_once 'includes/footer.php';
    exit();
}

$img = $item['image'] ? 'uploads/' . htmlspecialchars($item['image']) : 'assets/images/placeholder.jpg';
$date_label = $type === 'lost_items' ? 'Date Lost' : 'Date Found';
$date_val = $type === 'lost_items' ? $item['date_lost'] : $item['date_found'];
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h2><?php echo htmlspecialchars($item['title']); ?></h2>
            <span class="badge <?php echo $item['status'] == 'pending' ? 'badge-pending' : ($item['status'] == 'approved' ? 'badge-approved' : 'badge-rejected'); ?>">
                <?php echo ucfirst($item['status']); ?>
            </span>
        </div>
        
        <img src="<?php echo $img; ?>" alt="Item Image" style="width: 100%; max-height: 400px; object-fit: contain; border-radius: 8px; margin-bottom: 20px; background: #f9fafb;">
        
        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                <p><strong><?php echo $date_label; ?>:</strong> <?php echo $date_val; ?></p>
            </div>
            <div>
                <p><strong>Reported By:</strong> <?php echo htmlspecialchars($item['user_name']); ?></p>
                <p><strong>Report Date:</strong> <?php echo date('Y-m-d', strtotime($item['created_at'])); ?></p>
            </div>
        </div>
        
        <div style="margin-bottom: 30px;">
            <h3>Description</h3>
            <p style="white-space: pre-wrap; margin-top: 10px; color: var(--text-main); background: #f9fafb; padding: 15px; border-radius: 8px;"><?php echo htmlspecialchars($item['description']); ?></p>
        </div>

        <?php if (isLoggedIn() && $_SESSION['user_id'] != $item['user_id']): ?>
            <a href="messages.php?to=<?php echo $item['user_id']; ?>&item=<?php echo $item['id']; ?>&type=<?php echo $_GET['type']; ?>" class="btn btn-primary">Contact Reporter</a>
        <?php elseif (!isLoggedIn()): ?>
            <p><a href="login.php">Login</a> to contact the person who reported this.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
