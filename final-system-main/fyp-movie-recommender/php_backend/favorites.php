<?php
// favorites.php - Premium Neural Favorites
session_start();

require_once 'includes/config.php';
require_once 'database/connection.php';
require_once 'includes/header.php';
set_page_title("MoodAI | Neural Favorites");

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

$favorite_movies = [];
try {
    $stmt = $pdo->prepare("SELECT id, tmdb_movie_id, movie_title, movie_poster, mood_tag FROM user_favorites WHERE user_id = ? ORDER BY saved_at DESC");
    $stmt->execute([$user_id]);
    $favorite_movies = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database Error in favorites.php: " . $e->getMessage());
}
?>
<link rel="stylesheet" href="assets/css/recommendation.css">
<link rel="stylesheet" href="assets/css/favorites.css">

<main class="container pb-5 fade-in-section">

    <!-- Hero Section (Sync with Index Page Style) -->
    <section class="hero-section dashboard-hero-section mb-4">
        <div class="container px-0">
            <div class="hero-card" data-aos="zoom-in">
                <div class="row align-items-center p-4">
                    <div class="col-lg-5 text-center position-relative mb-5 mb-lg-0" data-aos="fade-right">
                    <!-- Decorative small icons -->
                    <i class="bi bi-heart-fill decorative-icon icon-1"></i>
                    <i class="bi bi-star-fill decorative-icon icon-2"></i>
                    <i class="bi bi-film decorative-icon icon-3"></i>
                    <i class="bi bi-play-circle-fill decorative-icon icon-4"></i>
                    
                    <!-- Large Favorites Icon -->
                    <div class="large-hero-icon">
                        <i class="bi bi-heart-fill"></i>
                    </div>
                </div>
                <div class="col-lg-7 hero-content ps-lg-5" data-aos="fade-left">
                    <span class="section-tag hero-tag">Loading your saved movies...</span>
                    <h1 class="hero-title-text">Your Favorite<br>Movie Collection.</h1>
                    <p class="lead hero-lead mb-5">
                        Your personalized cinematic library, curated through emotional sync. Access all movies that matched your mood in one place.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="dashboard.php" class="btn btn-hero-primary btn-lg">FIND MORE</a>
                        <div class="dropdown">
                            <button class="btn btn-hero-outline btn-lg dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel me-1"></i> FILTER BY MOOD
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-danger">
                                <li><a class="dropdown-item dropdown-item-tech active" href="#" data-filter="all">All Protocols</a></li>
                                <li><a class="dropdown-item dropdown-item-tech" href="#" data-filter="happy">Happy</a></li>
                                <li><a class="dropdown-item dropdown-item-tech" href="#" data-filter="sad">Sad</a></li>
                                <li><a class="dropdown-item dropdown-item-tech" href="#" data-filter="angry">Angry</a></li>
                                <li><a class="dropdown-item dropdown-item-tech" href="#" data-filter="surprise">Surprise</a></li>
                                <li><a class="dropdown-item dropdown-item-tech" href="#" data-filter="neutral">Neutral</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row justify-content-center">
        <div class="col-lg-12">

            <?php if (empty($favorite_movies)): ?>
                <div id="favoritesContainer">
                    <div class="empty-archive animate-reveal">
                        <i class="bi bi-film text-bright-red mb-3 d-block" style="font-size: 3rem;"></i>
                        <h4 class="fw-bold text-white">No Favorites Yet</h4>
                        <p class="text-white small mb-4">You haven't saved any movies to your favorites list yet.</p>
                        <a href="dashboard.php" class="btn btn-initiate mt-2">Find Movies</a>
                    </div>
                </div>
            <?php else: ?>

                <div class="d-flex justify-content-between align-items-center mb-4 px-2" data-aos="fade-right">
                    <h5 class="fw-bold text-white mb-0" style="letter-spacing: 2px;">SAVED FAVORITES</h5>
                    <span id="recordCount" class="text-muted small" style="font-family: 'Inter', sans-serif;">
                        <?php echo count($favorite_movies); ?> MOVIES
                    </span>
                </div>

                <div id="favoritesContainer" class="row g-4 movie-grid">
                    <?php foreach ($favorite_movies as $index => $movie): ?>
                        <?php
                            $delay = ($index % 8) * 100;
                        ?>
                        <div class="col-6 col-md-4 col-lg-3 favorite-item animate-reveal"
                             data-movie-id="<?php echo htmlspecialchars($movie['tmdb_movie_id']); ?>"
                             data-mood="<?php echo strtolower($movie['mood_tag']); ?>"
                             data-aos="fade-up"
                             data-aos-delay="<?php echo $delay; ?>">
                            <div class="movie-card">
                                <!-- Mood Badge -->
                                <div class="match-score-badge"><?php echo strtoupper($movie['mood_tag']); ?> MOOD</div>

                                <div class="card-img-top-wrapper">
                                    <img src="<?php echo htmlspecialchars($movie['movie_poster']); ?>"
                                         class="movie-poster"
                                         alt="<?php echo htmlspecialchars($movie['movie_title']); ?> Poster"
                                         loading="lazy"
                                         onerror="this.src='assets/img/no_poster.jpg';">

                                    <!-- Hover Overlay -->
                                    <div class="poster-overlay">
                                        <div class="overlay-content text-center">
                                            <p class="movie-overview-short">Movie saved based on your <?php echo strtolower($movie['mood_tag']); ?> mood history.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <span class="text-muted" style="font-size: 0.55rem; font-family: 'Inter', sans-serif; letter-spacing: 1px;">Entry ID: #<?php echo str_pad($movie['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                    <h5 class="movie-title text-truncate" title="<?php echo htmlspecialchars($movie['movie_title']); ?>">
                                        <?php echo htmlspecialchars($movie['movie_title']); ?>
                                    </h5>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="rating-tech">
                                            <i class="bi bi-tag-fill me-1"></i> <?php echo strtoupper($movie['mood_tag']); ?>
                                        </span>
                                        <span class="status-optimal animate-flicker">SAVED</span>
                                    </div>

                                    <button class="btn btn-sync remove-btn"
                                            data-movie-id="<?php echo htmlspecialchars($movie['tmdb_movie_id']); ?>">
                                        <i class="bi bi-trash-fill me-1"></i> REMOVE FROM LIST
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
?>
<script src="assets/js/favorites.js"></script>
