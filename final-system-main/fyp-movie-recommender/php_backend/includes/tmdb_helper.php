<?php
/**
 * tmdb_helper.php - Unified TMDB API interaction layer
 */

require_once __DIR__ . '/config.php';

/**
 * Fetches movies from TMDB based on genre and other filters
 */
function fetch_movies_from_tmdb($genre_id, $region = 'Hollywood', $sort = 'popularity.desc', $page = 1) {
    $params = [
        'api_key' => TMDB_API_KEY,
        'with_genres' => $genre_id,
        'sort_by' => $sort,
        'language' => 'en-US',
        'page' => $page,
        'include_adult' => 'false'
    ];

    // Apply Region Filtering
    if ($region === 'Bollywood') {
        $params['with_original_language'] = 'hi';
    } elseif ($region === 'South Indian') {
        $params['with_original_language'] = 'te|ta|kn|ml';
    } elseif ($region === 'International') {
        $params['without_original_language'] = 'en|hi';
    } else {
        $params['with_original_language'] = 'en';
    }

    $url = TMDB_BASE_URL . 'discover/movie?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && $response) {
        return [
            'success' => true,
            'data' => json_decode($response, true)
        ];
    }

    return [
        'success' => false,
        'code' => $http_code,
        'error' => 'TMDB API Connection Failed'
    ];
}

/**
 * Formats a single movie result with full poster path
 */
function format_movie_data($movie) {
    return [
        'id' => $movie['id'],
        'title' => $movie['title'],
        'vote_average' => $movie['vote_average'],
        'overview' => $movie['overview'],
        'poster_path' => !empty($movie['poster_path'])
            ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path']
            : 'assets/img/no_poster.jpg'
    ];
}
?>
