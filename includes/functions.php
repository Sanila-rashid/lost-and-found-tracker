<?php
// includes/functions.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Smart Matching Algorithm
// Triggers when a new item is added
function findMatches($pdo, $item_id, $type) {
    if ($type === 'lost') {
        $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $target = $stmt->fetch();
        
        $searchTable = 'found_items';
        $lost_id = $item_id;
    } else {
        $stmt = $pdo->prepare("SELECT * FROM found_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $target = $stmt->fetch();
        
        $searchTable = 'lost_items';
        $found_id = $item_id;
    }

    if (!$target) return;

    $category = $target['category'];
    $location = $target['location'];
    
    // We look for items in the opposite table with the same category
    // And use LIKE for location similarity
    $stmt = $pdo->prepare("SELECT * FROM $searchTable WHERE category = ? AND status IN ('pending', 'approved')");
    $stmt->execute([$category]);
    $candidates = $stmt->fetchAll();

    foreach ($candidates as $candidate) {
        $score = 0;
        
        // Exact category match gives 50 points (already filtered by category, so base score is 50)
        $score += 50;

        // Location similarity
        similar_text(strtolower($location), strtolower($candidate['location']), $loc_percent);
        $score += ($loc_percent / 2); // Up to 50 points for location

        // Title similarity (optional bonus)
        similar_text(strtolower($target['title']), strtolower($candidate['title']), $title_percent);
        $score += ($title_percent / 5); // Up to 20 points

        if ($score > 70) { // Threshold for a match
            if ($type === 'lost') {
                $found_id = $candidate['id'];
            } else {
                $lost_id = $candidate['id'];
            }

            // Check if match already exists
            $check = $pdo->prepare("SELECT id FROM matches WHERE lost_item_id = ? AND found_item_id = ?");
            $check->execute([$lost_id, $found_id]);
            if ($check->rowCount() == 0) {
                $insert = $pdo->prepare("INSERT INTO matches (lost_item_id, found_item_id, match_score) VALUES (?, ?, ?)");
                $insert->execute([$lost_id, $found_id, round($score)]);
            }
        }
    }
}
?>
