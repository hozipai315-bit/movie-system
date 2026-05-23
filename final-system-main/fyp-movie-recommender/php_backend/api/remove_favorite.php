<?php
// api/remove_favorite.php
// API Endpoint to remove a movie from user favorites

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
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$movie_id = $_POST['movie_id'] ?? null;

if (!$movie_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing movie_id.']);
    exit();
}

// 3. Database Interaction
try {
    $stmt = $pdo->prepare("DELETE FROM user_favorites WHERE user_id = ? AND tmdb_movie_id = ?");
    $stmt->execute([$user_id, $movie_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Movie removed from favorites.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Movie not found in favorites.']);
    }
} catch (PDOException $e) {
    error_log("Database Error in remove_favorite: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
