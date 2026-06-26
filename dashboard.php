<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Handle Deletions
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $del_id = (int)$_GET['delete'];
    $del_type = $_GET['type'] === 'lost' ? 'lost_items' : 'found_items';
    
    // Check ownership
    $stmt = $pdo->prepare("SELECT id FROM $del_type WHERE id = ? AND user_id = ?");
    $stmt->execute([$del_id, $user_id]);
    if ($stmt->rowCount() > 0) {
        $pdo->prepare("DELETE FROM $del_type WHERE id = ?")->execute([$del_id]);
        $success_msg = "Item deleted successfully.";
    }
}

// Fetch user's lost items
$stmtLost = $pdo->prepare("SELECT * FROM lost_items WHERE user_id = ? ORDER BY created_at DESC");
$stmtLost->execute([$user_id]);
$myLost = $stmtLost->fetchAll();

// Fetch user's found items
$stmtFound = $pdo->prepare("SELECT * FROM found_items WHERE user_id = ? ORDER BY created_at DESC");
$stmtFound->execute([$user_id]);
$myFound = $stmtFound->fetchAll();

// Fetch potential matches for user's items
$matchesQuery = "
    SELECT m.id as match_id, m.match_score, m.status,
           l.title as lost_title, l.id as lost_id, l.user_id as lost_owner,
           f.title as found_title, f.id as found_id, f.user_id as found_owner
    FROM matches m
    JOIN lost_items l ON m.lost_item_id = l.id
    JOIN found_items f ON m.found_item_id = f.id
    WHERE l.user_id = ? OR f.user_id = ?
    ORDER BY m.match_score DESC
";
$stmtMatches = $pdo->prepare($matchesQuery);
$stmtMatches->execute([$user_id, $user_id]);
$matches = $stmtMatches->fetchAll();

?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h2>Dashboard - Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
    <div>
        <a href="add_lost.php" class="btn btn-outline">🚨 Report Lost</a>
        <a href="add_found.php" class="btn btn-secondary">⚠️ Report Found</a>
    </div>
</div>

<?php if (isset($success_msg)): ?>
    <div class="alert alert-success"><?php echo $success_msg; ?></div>
<?php endif; ?>

<!-- Matches Section -->
<div class="card" style="margin-bottom: 40px; border-left: 4px solid var(--primary);">
    <h3 class="card-title">Smart Matches (<?php echo count($matches); ?>)</h3>
    <?php if (count($matches) > 0): ?>
        <ul style="list-style: none; padding: 0;">
            <?php foreach ($matches as $match): ?>
                <li style="padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>Match Score: <?php echo $match['match_score']; ?>%</strong><br>
                        Lost: <a href="details.php?id=<?php echo $match['lost_id']; ?>&type=lost"><?php echo htmlspecialchars($match['lost_title']); ?></a> 
                        | Found: <a href="details.php?id=<?php echo $match['found_id']; ?>&type=found"><?php echo htmlspecialchars($match['found_title']); ?></a>
                    </div>
                    <?php 
                        // Determine who to contact
                        if ($match['lost_owner'] == $user_id) {
                            $contact_id = $match['found_owner'];
                            $contact_item = $match['found_id'];
                            $contact_type = 'found';
                        } else {
                            $contact_id = $match['lost_owner'];
                            $contact_item = $match['lost_id'];
                            $contact_type = 'lost';
                        }
                    ?>
                    <a href="messages.php?to=<?php echo $contact_id; ?>&item=<?php echo $contact_item; ?>&type=<?php echo $contact_type; ?>" class="btn btn-primary btn-sm">Contact</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No matches found yet. We'll notify you if something turns up!</p>
    <?php endif; ?>
</div>

<div class="grid" style="grid-template-columns: 1fr 1fr; gap: 40px;">
    <!-- My Lost Items -->
    <div>
        <h3 style="margin-bottom: 20px;">My Lost Items</h3>
        <?php if (count($myLost) > 0): ?>
            <?php foreach ($myLost as $item): ?>
                <div class="card" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between;">
                        <h4 style="margin-bottom: 10px;"><?php echo htmlspecialchars($item['title']); ?></h4>
                        <span class="badge badge-<?php echo $item['status'] == 'pending' ? 'pending' : ($item['status'] == 'approved' ? 'approved' : 'rejected'); ?>">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">
                        Date: <?php echo $item['date_lost']; ?> | Location: <?php echo htmlspecialchars($item['location']); ?>
                    </p>
                    <a href="dashboard.php?delete=<?php echo $item['id']; ?>&type=lost" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this?');">Delete</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card"><p>You haven't reported any lost items.</p></div>
        <?php endif; ?>
    </div>

    <!-- My Found Items -->
    <div>
        <h3 style="margin-bottom: 20px;">My Found Items</h3>
        <?php if (count($myFound) > 0): ?>
            <?php foreach ($myFound as $item): ?>
                <div class="card" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between;">
                        <h4 style="margin-bottom: 10px;"><?php echo htmlspecialchars($item['title']); ?></h4>
                        <span class="badge badge-<?php echo $item['status'] == 'pending' ? 'pending' : ($item['status'] == 'approved' ? 'approved' : 'rejected'); ?>">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">
                        Date: <?php echo $item['date_found']; ?> | Location: <?php echo htmlspecialchars($item['location']); ?>
                    </p>
                    <a href="dashboard.php?delete=<?php echo $item['id']; ?>&type=found" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this?');">Delete</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card"><p>You haven't reported any found items.</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
