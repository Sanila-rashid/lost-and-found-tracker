<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Mark all incoming messages as read when visiting the messages page
$updateStmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ?");
$updateStmt->execute([$user_id]);

// If trying to send a new message from details/dashboard
if (isset($_GET['to'])) {
    $receiver_id = (int)$_GET['to'];
    
    // Ensure receiver exists
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    $receiver = $stmt->fetch();
    
    if (!$receiver) {
        redirect('dashboard.php');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
        $msg = sanitize($_POST['message']);
        if (!empty($msg)) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $receiver_id, $msg]);
            // Redirect to remove POST data
            redirect("messages.php?to=$receiver_id");
        }
    }
}

// Fetch all conversations for the user
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.name 
    FROM users u 
    JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id) 
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
");
$stmt->execute([$user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();

?>

<div class="grid" style="grid-template-columns: 1fr 3fr; gap: 20px;">
    <!-- Sidebar: Conversations -->
    <div class="card">
        <h3>Conversations</h3>
        <ul style="list-style: none; padding: 0; margin-top: 15px;">
            <?php foreach ($conversations as $conv): ?>
                <li style="margin-bottom: 10px;">
                    <a href="messages.php?to=<?php echo $conv['id']; ?>" class="btn btn-outline" style="width: 100%; text-align: left; <?php echo (isset($_GET['to']) && $_GET['to'] == $conv['id']) ? 'background: var(--primary); color: white !important;' : ''; ?>">
                        <?php echo htmlspecialchars($conv['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <?php if(empty($conversations) && !isset($_GET['to'])): ?>
                <p>No active conversations.</p>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Main: Chat Box -->
    <div class="card" style="display: flex; flex-direction: column; height: 600px;">
        <?php if (isset($_GET['to']) && isset($receiver)): ?>
            <h3 style="padding-bottom: 15px; border-bottom: 1px solid #eee;">Chat with <?php echo htmlspecialchars($receiver['name']); ?></h3>
            
            <div style="flex: 1; overflow-y: auto; padding: 15px 0; display: flex; flex-direction: column; gap: 10px;" id="chat-box">
                <?php
                // Fetch messages between user and receiver
                $stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp ASC");
                $stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
                $messages = $stmt->fetchAll();

                foreach ($messages as $m) {
                    $isMine = $m['sender_id'] == $user_id;
                    $align = $isMine ? 'align-self: flex-end; background: var(--primary); color: white;' : 'align-self: flex-start; background: #F3F4F6; color: var(--text-main);';
                    echo "<div style='max-width: 70%; padding: 10px 15px; border-radius: 12px; $align'>";
                    echo htmlspecialchars($m['message']);
                    echo "<div style='font-size: 0.75rem; opacity: 0.7; margin-top: 5px;'>" . date('H:i, M d', strtotime($m['timestamp'])) . "</div>";
                    echo "</div>";
                }
                ?>
            </div>

            <form method="POST" action="" style="display: flex; gap: 10px; margin-top: 15px;">
                <input type="text" name="message" class="form-control" placeholder="Type a message..." required autofocus>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
            <script>
                // Auto scroll to bottom
                const chatBox = document.getElementById('chat-box');
                chatBox.scrollTop = chatBox.scrollHeight;
            </script>
        <?php else: ?>
            <div style="display: flex; justify-content: center; align-items: center; height: 100%; color: var(--text-muted);">
                Select a conversation or contact someone from an item details page.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
