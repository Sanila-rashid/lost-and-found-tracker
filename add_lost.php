<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $location = sanitize($_POST['location']);
    $date_lost = $_POST['date_lost'];
    $user_id = $_SESSION['user_id'];
    
    $image = '';
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            $dest = 'uploads/' . $new_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $image = $new_name;
            }
        } else {
            $error = 'Invalid image format. Allowed: JPG, PNG, GIF.';
        }
    }

    if (empty($error)) {
        if (empty($title) || empty($description) || empty($category) || empty($location) || empty($date_lost)) {
            $error = 'All fields except image are required.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO lost_items (user_id, title, description, category, location, image, date_lost) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $title, $description, $category, $location, $image, $date_lost])) {
                $item_id = $pdo->lastInsertId();
                // Trigger Smart Match
                findMatches($pdo, $item_id, 'lost');
                $success = 'Lost item reported successfully. It is pending admin approval.';
                $_SESSION['flash'] = ['msg' => 'Lost item reported! Waiting for approval.', 'type' => 'success'];
            } else {
                $error = 'Failed to report item.';
            }
        }
    }
}
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="card">
        <h2 style="margin-bottom: 20px;">Report Lost Item</h2>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">What did you lose?</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Black Leather Wallet" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">Select Category</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Wallets & Cards">Wallets & Cards</option>
                    <option value="Keys">Keys</option>
                    <option value="Bags & Luggage">Bags & Luggage</option>
                    <option value="Clothing">Clothing</option>
                    <option value="Pets">Pets</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="location">Location Lost</label>
                <input type="text" id="location" name="location" class="form-control" placeholder="e.g. Central Park, near the fountain" required>
            </div>
            
            <div class="form-group">
                <label for="date_lost">Date Lost</label>
                <input type="date" id="date_lost" name="date_lost" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="description">Detailed Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Any specific markings, contents, etc." required></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Upload Image (Optional)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">🚨 Submit Report</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
