<?php
require_once 'includes/header.php';

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'all'; // all, lost, found

$where = ["status = 'approved'"];
$params = [];

if ($search) {
    $where[] = "(title LIKE ? OR location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where[] = "category = ?";
    $params[] = $category;
}

$whereClause = implode(" AND ", $where);

// Queries
$items = [];
if ($type === 'all' || $type === 'lost') {
    $sqlLost = "SELECT id, title, category, location, image, date_lost AS date_reported, 'lost' AS type FROM lost_items WHERE $whereClause";
    $stmt = $pdo->prepare($sqlLost);
    $stmt->execute($params);
    $items = array_merge($items, $stmt->fetchAll());
}

if ($type === 'all' || $type === 'found') {
    $sqlFound = "SELECT id, title, category, location, image, date_found AS date_reported, 'found' AS type FROM found_items WHERE $whereClause";
    $stmt = $pdo->prepare($sqlFound);
    $stmt->execute($params);
    $items = array_merge($items, $stmt->fetchAll());
}

// Sort by date descending
usort($items, function($a, $b) {
    return strtotime($b['date_reported']) - strtotime($a['date_reported']);
});
?>

<div class="card" style="margin-bottom: 30px;">
    <form method="GET" action="search.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 2; min-width: 200px;">
            <label for="q">Keyword / Location</label>
            <input type="text" id="q" name="q" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search...">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label for="category">Category</label>
            <select id="category" name="category" class="form-control">
                <option value="">All Categories</option>
                <option value="Electronics" <?php echo $category == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                <option value="Wallets & Cards" <?php echo $category == 'Wallets & Cards' ? 'selected' : ''; ?>>Wallets & Cards</option>
                <option value="Keys" <?php echo $category == 'Keys' ? 'selected' : ''; ?>>Keys</option>
                <option value="Bags & Luggage" <?php echo $category == 'Bags & Luggage' ? 'selected' : ''; ?>>Bags & Luggage</option>
                <option value="Clothing" <?php echo $category == 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
                <option value="Pets" <?php echo $category == 'Pets' ? 'selected' : ''; ?>>Pets</option>
                <option value="Other" <?php echo $category == 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label for="type">Type</label>
            <select id="type" name="type" class="form-control">
                <option value="all" <?php echo $type == 'all' ? 'selected' : ''; ?>>All</option>
                <option value="lost" <?php echo $type == 'lost' ? 'selected' : ''; ?>>Lost Items</option>
                <option value="found" <?php echo $type == 'found' ? 'selected' : ''; ?>>Found Items</option>
            </select>
        </div>
        <div style="min-width: 120px;">
            <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px;">Filter</button>
        </div>
    </form>
</div>

<h2 style="margin-bottom: 20px;">Search Results (<?php echo count($items); ?>)</h2>
<div class="grid">
    <?php
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
                <p style='color: var(--text-muted); margin-bottom: 5px;'>Category: {$item['category']}</p>
                <p style='color: var(--text-muted); margin-bottom: 15px;'>Location: ".htmlspecialchars($item['location'])."</p>
                <a href='details.php?id={$item['id']}&type={$item['type']}' class='btn btn-outline btn-sm' style='width: 100%; text-align: center;'>View Details</a>
            </div>
            ";
        }
    } else {
        echo "<div style='grid-column: 1 / -1; text-align: center; padding: 40px;' class='card'>No items match your search criteria.</div>";
    }
    ?>
</div>

<?php require_once 'includes/footer.php'; ?>
