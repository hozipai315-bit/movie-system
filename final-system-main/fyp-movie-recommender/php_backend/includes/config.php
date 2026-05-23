<?php
// Database configuration constants (Change these to your actual settings)
define('DB_HOST', 'localhost');
define('DB_NAME', 'mood_recommender_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Load Dynamic Settings from Database if possible
$dynamic_settings = [];
try {
    // Attempt a temporary connection to fetch settings
    $temp_pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $res = $temp_pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    if ($res) {
        $dynamic_settings = $res;
    }
} catch (Throwable $t) {
    // Fallback to defaults if DB is not ready or table missing
}

// Site Brand Configuration
define('SITE_NAME', $dynamic_settings['site_name'] ?? 'MoodAI');

// TMDB API Configuration
define('TMDB_API_KEY', $dynamic_settings['tmdb_api_key'] ?? '');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3/');
?>
