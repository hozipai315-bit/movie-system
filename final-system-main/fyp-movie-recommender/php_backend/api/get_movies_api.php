<?php
// api/get_movies_api.php
// Returns list of movies based on Mood or Genre ID
// Uses TMDB API via Helper

header('Content-Type: application/json');
session_start();

// --- Configuration ---
require_once '../includes/config.php';
require_once '../database/connection.php';
require_once '../includes/mood_mapper.php'; // Central Mapper
require_once '../includes/tmdb_helper.php'; // Unified Helper

// 2. Validate Request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Use GET.']);
    exit();
}

$mood = $_GET['mood'] ?? null;
$genre_id = $_GET['genre'] ?? null;
$page = $_GET['page'] ?? 1;

if (!$mood && !$genre_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing "mood" or "genre" parameter.']);
    exit();
}

// Determine Genre ID
if ($mood) {
    $target_genre_id = get_genre_id_for_mood($mood);
} else {
    $target_genre_id = intval($genre_id);
}

// 3. Fetch from TMDB using unified helper
$api_result = fetch_movies_from_tmdb($target_genre_id, 'Hollywood', 'popularity.desc', $page);

// 4. Handle Response
if ($api_result['success']) {
    $data = $api_result['data'];
    $results = $data['results'] ?? [];

    // Append full image path using helper logic
    foreach ($results as &$movie) {
        $formatted = format_movie_data($movie);
        $movie['poster_full_path'] = $formatted['poster_path'];
    }

    echo json_encode([
        'status' => 'success',
        'page' => $page,
        'results' => $results,
        'mood_used' => $mood,
        'genre_id_used' => $target_genre_id
    ]);

} else {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch from TMDB.',
        'tmdb_code' => $api_result['code'],
        'debug' => $api_result['error']
    ]);
}
?>
