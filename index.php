<?php require_once 'includes/header.php'; ?>
<style>

</style>

<div class="hero" style="text-align: center; padding: 60px 20px; background: linear-gradient(135deg, #3B82F6 0%, #10B981 100%); color: white; border-radius: 16px; margin-bottom: 40px; box-shadow: var(--card-shadow);">
    <h1 style="font-size: 3rem; margin-bottom: 20px;">Lost Something? Found Something?</h1>
    <p style="font-size: 1.25rem; margin-bottom: 30px; opacity: 0.9;">Join our community to connect lost items with their rightful owners.</p>
    <div style="display: flex; gap: 20px; justify-content: center;">
        <a href="search.php" class="btn btn-primary" style="background: white; color: var(--primary) !important; font-weight: bold; font-size: 1.1rem; padding: 12px 30px;">Search Items</a>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-outline" style="border-color: white; color: white !important; font-size: 1.1rem; padding: 12px 30px;">Join Now</a>
        <?php else: ?>
            <a href="add_lost.php" class="btn btn-outline" style="border-color: white; color: white !important; font-size: 1.1rem; padding: 12px 30px;">🚨 Report Lost</a>
            <a href="add_found.php" class="btn btn-outline" style="border-color: white; color: white !important; font-size: 1.1rem; padding: 12px 30px;">⚠️ Report Found</a>
        <?php endif; ?>
    </div>
</div>

<h2 style="text-align: center; margin-bottom: 30px;">Recently Reported Items</h2>
<div class="grid">
    <?php
    // Fetch recent lost items (approved)
    $stmt = $pdo->query("SELECT id, title, category, location, image, date_lost AS date_reported, 'lost' AS type FROM lost_items WHERE status = 'approved' UNION SELECT id, title, category, location, image, date_found AS date_reported, 'found' AS type FROM found_items WHERE status = 'approved' ORDER BY date_reported DESC LIMIT 6");
    $items = $stmt->fetchAll();

    if (count($items) > 0) {
        foreach ($items as $item) {
            $img = $item['image'] ? 'uploads/' . htmlspecialchars($item['image']) : 'assets/images/placeholder.jpg';
            $badgeClass = $item['type'] === 'lost' ? 'badge-rejected' : 'badge-approved';
            $typeLabel = ucfirst($item['type']);
            
            echo "
            <div class='card'>
                <img src='{$img}' class='item-img' alt='Item Image'>
                <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;'>
                    <span class='badge {$badgeClass}'>{$typeLabel}</span>
                    <small style='color: var(--text-muted);'>{$item['date_reported']}</small>
                </div>
                <h3 class='card-title'>".htmlspecialchars($item['title'])."</h3>
                <p style='color: var(--text-muted); margin-bottom: 15px;'>Location: ".htmlspecialchars($item['location'])."</p>
                <a href='details.php?id={$item['id']}&type={$item['type']}' class='btn btn-outline btn-sm' style='width: 100%; text-align: center;'>View Details</a>
            </div>
            ";
        }
    } else {
        echo "<p style='text-align: center; width: 100%;'>No items reported yet.</p>";
    }
    ?>
</div>

<?php require_once 'includes/footer.php'; ?>