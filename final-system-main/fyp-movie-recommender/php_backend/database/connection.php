<?php
// Ensure config.php is included first
require_once __DIR__ . '/../includes/config.php';

$pdo = null;
$db_error = null;

try {
    // Create a new PDO instance (Database Management: Storing user profiles [cite: 40])
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    $pdo = null;
    $db_error = $e->getMessage();
}
?>