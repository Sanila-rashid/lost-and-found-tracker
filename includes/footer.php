    </main>
    <footer>
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Lost & Found System. Built with love</p>
        </div>
    </footer>
    
    <!-- Floating Messenger Icon -->
    <?php if (isLoggedIn()): ?>
        <?php
            // Check for new unread messages
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
            $stmt->execute([$_SESSION['user_id']]);
            $msgCount = $stmt->fetchColumn();
        ?>
        <a href="messages.php" class="messenger-fab" title="Messages">
            💬
            <?php if($msgCount > 0): ?>
                <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; font-size: 0.7rem; font-weight: bold; padding: 3px 6px; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><?php echo $msgCount; ?></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <!-- Dark Mode Toggle Button -->
    <div id="darkModeToggle" class="toggle-btn" title="Toggle Dark Mode">🌙</div>

    <!-- Hidden Flash Message for JS to read -->
    <?php if (isset($_SESSION['flash'])): ?>
        <input type="hidden" id="flash-message" value="<?php echo htmlspecialchars($_SESSION['flash']['msg']); ?>" data-type="<?php echo htmlspecialchars($_SESSION['flash']['type']); ?>">
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
