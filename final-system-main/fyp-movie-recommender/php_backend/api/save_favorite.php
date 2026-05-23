<?php
// api/save_favorite.php
// API Endpoint to save a movie to user favorites

header('Content-Type: application/json');
session_start();

require_once '../database/connection.php';

// 1. Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Use POST.']);
    exit();
}

// 2. Validate Session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit();
}

// 3. Get User ID
$user_id = $_SESSION['user_id'];

// 4. Validate Input
$movie_id = $_POST['movie_id'] ?? null;
$title = $_POST['title'] ?? null;
$poster = $_POST['poster'] ?? null;
$mood = $_POST['mood'] ?? $_SESSION['last_detected_mood'] ?? 'Unknown';

if (!$movie_id || !$title) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing movie_id or title.']);
    exit();
}

// 5. Database Interaction
try {
    // Check if already in favorites
    $check_stmt = $pdo->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND tmdb_movie_id = ?");
    $check_stmt->execute([$user_id, $movie_id]);

    if ($check_stmt->rowCount() > 0) {
        echo json_encode(['status' => 'info', 'message' => 'Movie is already in your favorites.']);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO user_favorites (user_id, tmdb_movie_id, movie_title, movie_poster, mood_tag) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $movie_id, $title, $poster, $mood]);

    echo json_encode(['status' => 'success', 'message' => 'Movie added to favorites!']);
} catch (PDOException $e) {
    error_log("Database Error in save_favorite: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
