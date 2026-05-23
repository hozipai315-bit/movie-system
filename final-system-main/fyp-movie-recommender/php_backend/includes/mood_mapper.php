<?php
/**
 * mood_mapper.php - Centralized Mood-to-Genre Mapping Protocol
 *
 * Provides a unified way to map detected moods to TMDB Genre IDs.
 * Priority: 1. Database (Admin Settings), 2. AI Service Sync, 3. Local JSON Fallback.
 */

if (!function_exists('get_genre_id_for_mood')) {
    function get_genre_id_for_mood($mood) {

        // --- 1. DATABASE PROTOCOL (PRIMARY) ---
        // Check dynamic weighted mappings defined in admin panel first
        global $pdo;

        // Ensure PDO is available even if not globalized correctly
        if (!isset($pdo) && file_exists(__DIR__ . '/../database/connection.php')) {
            try {
                @include_once __DIR__ . '/../database/connection.php';
            } catch (Throwable $t) {}
        }

        if (isset($pdo)) {
            try {
                // Fetch the highest weighted genre for this mood
                $stmt = $pdo->prepare("SELECT genre_id FROM mood_genre_mapping WHERE mood_name = ? ORDER BY weight DESC LIMIT 1");
                $stmt->execute([ucfirst(strtolower($mood))]);
                $db_genre = $stmt->fetchColumn();
                if ($db_genre) return (int)$db_genre;
            } catch (Exception $e) {}
        }

        // --- 2. AI SYNC PROTOCOL (SECONDARY) ---
        // Fallback to Python AI service if DB mapping is missing
        $python_api_url = 'http://127.0.0.1:5000/genre?mood=' . urlencode($mood);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $python_api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Quick timeout for seamless fallback

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['genre_id'])) {
                return (int)$data['genre_id'];
            }
        }

        // --- 3. LOCAL FALLBACK PROTOCOL (TERTIARY) ---
        // Centralized fallback mapping from JSON
        $fallback_json = __DIR__ . '/../../database/mood_genre_fallback.json';
        $fallback_map = [
            'Happy' => 35, 'Sad' => 18, 'Angry' => 28, 'Excited' => 10751,
            'Neutral' => 10752, 'Default' => 35
        ];

        if (file_exists($fallback_json)) {
            $json_data = json_decode(file_get_contents($fallback_json), true);
            if ($json_data) $fallback_map = $json_data;
        }

        $mood_key = ucfirst(strtolower($mood));
        return $fallback_map[$mood_key] ?? $fallback_map['Default'];
    }
}
?>
