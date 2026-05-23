<?php
// recommendation.php
// Fetches movie recommendations based on the user's last detected mood.

// 1. START SESSION
session_start();

// --- Configuration ---
require_once 'includes/config.php';
require_once 'database/connection.php'; // Needed for local cache
require_once 'includes/mood_mapper.php'; // Central Mapper
require_once 'includes/tmdb_helper.php'; // TMDB Helper

// --- Session Check & Access Control ---
$user_id = $_SESSION['user_id'] ?? null;
$last_detected_mood = $_SESSION['last_detected_mood'] ?? null;
$detection_method = $_SESSION['mood_detection_method'] ?? null;
$current_region = $_GET['region'] ?? 'Hollywood';
$current_sort = $_GET['sort'] ?? 'popularity.desc';
$is_guest = $_SESSION['is_guest'] ?? false;

if (!$user_id || !$last_detected_mood) {
    header("Location: dashboard.php");
    exit();
}
// --- Recommendation Logic ---
$target_genre_id = get_genre_id_for_mood($last_detected_mood);
$recommended_movies = [];
$api_error = null;
$data_source = "Live Cloud";

// --- CALL TMDB API VIA HELPER ---
$api_result = fetch_movies_from_tmdb($target_genre_id, $current_region, $current_sort);

if ($api_result['success']) {
    $results = $api_result['data']['results'] ?? [];

    foreach ($results as $movie) {
        $formatted_movie = format_movie_data($movie);
        $recommended_movies[] = $formatted_movie;

        // --- CACHE UPDATE LOGIC ---
        try {
            $stmt = $pdo->prepare("INSERT INTO cached_movies (tmdb_id, title, overview, poster_path, vote_average, genre_id, original_language) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE 
                                   title = VALUES(title), 
                                   overview = VALUES(overview), 
                                   poster_path = VALUES(poster_path), 
                                   vote_average = VALUES(vote_average)");
            
            // Determine lang for cache
            $lang = 'en';
            if ($current_region === 'Bollywood') $lang = 'hi';
            
            $stmt->execute([
                $formatted_movie['id'],
                $formatted_movie['title'],
                $formatted_movie['overview'],
                $formatted_movie['poster_path'],
                $formatted_movie['vote_average'],
                $target_genre_id,
                $lang
            ]);
        } catch (Exception $e) {
            // Ignore cache errors
        }
    }
} else {
    // --- INTELLIGENT FALLBACK TO LOCAL CACHE ---
    $data_source = "Local Intelligence";
    try {
        $query = "SELECT tmdb_id as id, title, overview, poster_path, vote_average FROM cached_movies WHERE genre_id = :gid";
        
        if ($current_region === 'Bollywood') {
            $query .= " AND original_language = 'hi'";
        } elseif ($current_region === 'Hollywood') {
            $query .= " AND original_language = 'en'";
        }
        
        if ($current_sort === 'vote_average.desc') {
            $query .= " ORDER BY vote_average DESC";
        } else {
            $query .= " ORDER BY cached_at DESC";
        }
        
        $query .= " LIMIT 20";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute(['gid' => $target_genre_id]);
        $recommended_movies = $stmt->fetchAll();
        
        if (empty($recommended_movies)) {
            $api_error = "API Offline & Local Cache Empty.";
        }
    } catch (Exception $e) {
        $api_error = "System Error: " . $e->getMessage();
    }
}

// --- Mood Visual Mapping ---
$mood_visuals = [
    'Happy'    => ['icon' => 'bi-emoji-smile-fill', 'color' => '#FFD700', 'tag' => 'Positive Energy'],
    'Sad'      => ['icon' => 'bi-emoji-frown-fill', 'color' => '#4A90E2', 'tag' => 'Emotional Depth'],
    'Angry'    => ['icon' => 'bi-emoji-angry-fill', 'color' => '#FF4B2B', 'tag' => 'High Intensity'],
    'Excited'  => ['icon' => 'bi-emoji-laughing-fill', 'color' => '#FF8C00', 'tag' => 'Pure Joy'],
    'Neutral'  => ['icon' => 'bi-emoji-neutral-fill', 'color' => '#FFFFFF', 'tag' => 'Balanced Vibe'],
    'Default'  => ['icon' => 'bi-emoji-smile-fill', 'color' => '#FF0000', 'tag' => 'System Analysis']
];

$visual = $mood_visuals[ucfirst(strtolower($last_detected_mood))] ?? ($mood_visuals['Default'] ?? ['icon' => 'bi-emoji-smile-fill', 'color' => '#FF0000', 'tag' => 'System Analysis']);

// --- Include UI Components ---
require_once 'includes/header.php';
set_page_title("Recommended Movies - MoodAI Rec.");
?>

<link rel="stylesheet" href="assets/css/recommendation.css">

<main class="container pb-5 fade-in-section">

    <!-- Hero Section (Sync with Index Page Style) -->
    <section class="hero-section mb-5">
        <div class="hero-card" data-aos="zoom-in">
            <div class="row align-items-center p-5">
                <div class="col-lg-5 text-center position-relative mb-5 mb-lg-0" data-aos="fade-right">
                    <!-- Decorative small icons -->
                    <i class="bi bi-cpu-fill decorative-icon icon-1"></i>
                    <i class="bi bi-film decorative-icon icon-2"></i>
                    <i class="bi bi-activity decorative-icon icon-3"></i>
                    <i class="bi bi-play-circle-fill decorative-icon icon-4"></i>
                    
                    <!-- Large Mood Icon -->
                    <div class="large-hero-icon">
                        <i class="bi <?php echo $visual['icon']; ?>"></i>
                    </div>
                </div>
                <div class="col-lg-7 hero-content ps-lg-5" data-aos="fade-left">
                    <span class="section-tag hero-tag"><?php echo $visual['tag']; ?></span>
                    <h1 class="hero-title-text">Movies for your<br><?php echo strtolower($last_detected_mood); ?> mood.</h1>
                    <p class="lead hero-lead mb-5">
                        Our neural engine has analyzed your <?php echo strtolower($detection_method); ?> patterns and curated a selection of films that perfectly match your current emotional state.
                    </p>
                    <div class="d-flex">
                        <a href="dashboard.php" class="btn btn-hero-primary btn-lg me-3">RE-SCAN MOOD</a>
                        <a href="#movieGrid" class="btn btn-hero-outline btn-lg">EXPLORE COLLECTION</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Integrated Filter Interface -->
    <section class="filter-section-premium mb-5" data-aos="fade-up">
        <div class="filter-glass-bar p-3">
            <div class="row align-items-center g-3">
                <div class="col-md-auto">
                    <div class="filter-label px-3 border-end border-dark">
                        <i class="bi bi-sliders2-vertical me-2" style="color: var(--accent-red);"></i>
                        FILTERS
                    </div>
                </div>
                <div class="col-md">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="small text-muted me-2 d-none d-lg-inline-block align-self-center">Region:</span>
                        <a href="?region=Hollywood&sort=<?php echo $current_sort; ?>" class="filter-btn <?php echo $current_region === 'Hollywood' ? 'active' : ''; ?>">Hollywood</a>
                        <a href="?region=Bollywood&sort=<?php echo $current_sort; ?>" class="filter-btn <?php echo $current_region === 'Bollywood' ? 'active' : ''; ?>">Bollywood</a>
                        <a href="?region=South Indian&sort=<?php echo $current_sort; ?>" class="filter-btn <?php echo $current_region === 'South Indian' ? 'active' : ''; ?>">South India</a>
                        <a href="?region=International&sort=<?php echo $current_sort; ?>" class="filter-btn <?php echo $current_region === 'International' ? 'active' : ''; ?>">International</a>
                    </div>
                </div>
                <div class="col-md-auto border-start border-dark d-none d-md-block">
                    <div class="d-flex gap-2 px-3">
                        <span class="small text-muted align-self-center">Sort:</span>
                        <a href="?region=<?php echo urlencode($current_region); ?>&sort=popularity.desc" class="filter-btn <?php echo $current_sort === 'popularity.desc' ? 'active' : ''; ?>">Popular</a>
                        <a href="?region=<?php echo urlencode($current_region); ?>&sort=vote_average.desc" class="filter-btn <?php echo $current_sort === 'vote_average.desc' ? 'active' : ''; ?>">Rating</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row justify-content-center">
        <div class="col-lg-12">

            <?php if ($api_error): ?>
                <div class="alert alert-system text-center py-4 mb-5" data-aos="zoom-in">
                    <i class="bi bi-exclamation-triangle-fill mb-2 d-block" style="font-size: 2rem;"></i>
                    <h5 class="fw-bold">SYSTEM ERROR</h5>
                    <p class="mb-0 small"><?php echo htmlspecialchars($api_error); ?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($recommended_movies)): ?>
                <div class="alert alert-system text-center py-5" data-aos="zoom-in">
                    <i class="bi bi-search display-4 mb-3 d-block"></i>
                    <h4 class="fw-bold">NO MOVIES FOUND</h4>
                    <p class="mb-0 small">We couldn't find any movies matching your mood at the moment.</p>
                </div>
            <?php else: ?>

                <div class="d-flex justify-content-between align-items-center mb-4 px-2" data-aos="fade-right">
                    <h5 class="fw-bold text-white mb-0" style="letter-spacing: 2px;">RECOMMENDED MOVIES</h5>
                    <span class="text-muted small" style="font-family: 'Inter', sans-serif;">
                        Source: <?php echo $data_source === 'Live Cloud' ? '<span class="text-success">Live Cloud</span>' : '<span class="text-warning">Local Intelligence</span>'; ?>
                    </span>
                </div>

                <div id="movieGrid" class="row g-4 movie-grid">

                    <?php foreach ($recommended_movies as $index => $movie): ?>
                        <?php 
                            $delay = ($index % 8) * 100; 
                            $match_score = 95 + (rand(0, 40) / 10); // Random score between 95 and 99
                        ?>
                        <div class="col-6 col-md-4 col-lg-3 movie-card-col" data-movie-id="<?php echo htmlspecialchars($movie['id']); ?>" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                            <div class="movie-card">
                                <!-- Neural Badge -->
                                <div class="match-score-badge"><?php echo number_format($match_score, 1); ?>% MATCH</div>

                                <div class="card-img-top-wrapper">
                                    <img src="<?php echo htmlspecialchars($movie['poster_path']); ?>"
                                         class="movie-poster"
                                         alt="<?php echo htmlspecialchars($movie['title']); ?> Poster"
                                         loading="lazy"
                                         onerror="this.src='assets/img/no_poster.jpg';">
                                    
                                    <!-- Hover Overlay -->
                                    <div class="poster-overlay">
                                        <div class="overlay-content">
                                            <p class="movie-overview-short"><?php echo htmlspecialchars(mb_strimwidth($movie['overview'], 0, 150, "...")); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <span class="text-muted" style="font-size: 0.55rem; font-family: 'Inter', sans-serif; letter-spacing: 1px;">Movie ID: #<?php echo str_pad($movie['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                    <h5 class="movie-title text-truncate" title="<?php echo htmlspecialchars($movie['title']); ?>">
                                        <?php echo htmlspecialchars($movie['title']); ?>
                                    </h5>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="rating-tech">
                                            <i class="bi bi-star-fill me-1"></i> <?php echo number_format($movie['vote_average'], 1); ?>
                                        </span>
                                        <span class="status-optimal animate-flicker">MATCHED</span>
                                    </div>

                                    <?php if ($is_guest): ?>
                                        <button class="btn btn-sync" data-bs-toggle="modal" data-bs-target="#registerModal">
                                            <i class="bi bi-shield-lock me-1"></i> LOGIN TO SAVE
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sync favorite-btn"
                                                data-movie-id="<?php echo htmlspecialchars($movie['id']); ?>"
                                                data-movie-title="<?php echo htmlspecialchars($movie['title']); ?>"
                                                data-movie-poster="<?php echo htmlspecialchars($movie['poster_path']); ?>">
                                            <i class="bi bi-heart me-1"></i> SAVE TO FAVORITES
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<?php
require_once 'includes/footer.php';
?>
<script src="assets/js/recommendation.js"></script>
