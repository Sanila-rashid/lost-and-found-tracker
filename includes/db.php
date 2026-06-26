<?php
// includes/db.php
$host = 'localhost';
$dbname = 'lost_and_found_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Auto-migrate: Add is_read to messages table if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE messages ADD COLUMN is_read TINYINT(1) DEFAULT 0");
    } catch (PDOException $e) {
        // Ignore duplicate column error
    }
} catch (PDOException $e) {
    die("Database connection failed. Please ensure MySQL is running and the database 'lost_and_found_db' exists. If not, run init_db.php first. Error: " . $e->getMessage());
}
?>
