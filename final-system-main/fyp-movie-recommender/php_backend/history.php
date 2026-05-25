<?php
// history.php - Premium Neural Archives
session_start();

require_once 'includes/config.php';
require_once 'database/connection.php';
require_once 'includes/header.php';
set_page_title("MoodAI | Neural Archives");

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Fetch history
$mood_history = [];
try {
    $stmt = $pdo->prepare("SELECT id, mood, input_type as type, detected_at as timestamp FROM user_mood_history WHERE user_id = ? ORDER BY detected_at DESC");
    $stmt->execute([$user_id]);
    $mood_history = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database Error in history.php: " . $e->getMessage());
}

// Tech UI Helpers
function get_mood_icon_tech($mood) {
    $mood = strtolower($mood);
    $icons = [
        'happy' => 'bi-emoji-smile',
        'sad' => 'bi-emoji-frown',
        'angry' => 'bi-emoji-angry',
        'surprise' => 'bi-emoji-surprise',
        'neutral' => 'bi-emoji-neutral',
    ];
    $icon = $icons[$mood] ?? 'bi-emoji-expressionless';
    return '<i class="bi ' . $icon . ' mood-icon-premium"></i>';
}

function get_type_badge_tech($type) {
    return '<span class="badge-tech">' . htmlspecialchars(strtoupper($type)) . '</span>';
}
?>
<link rel="stylesheet" href="assets/css/history.css">

<main class="container pb-5 fade-in-section">

    <!-- Hero Section (Sync with Index Page Style) -->
    <section class="hero-section dashboard-hero-section mb-4">
        <div class="container px-0">
            <div class="hero-card" data-aos="zoom-in">
                <div class="row align-items-center p-4">
                    <div class="col-lg-5 text-center position-relative mb-5 mb-lg-0" data-aos="fade-right">
                    <!-- Decorative small icons -->
                    <i class="bi bi-clock-history decorative-icon icon-1"></i>
                    <i class="bi bi-shield-check decorative-icon icon-2"></i>
                    <i class="bi bi-cpu-fill decorative-icon icon-3"></i>
                    <i class="bi bi-activity decorative-icon icon-4"></i>
                    
                    <!-- Large Archive Icon -->
                    <div class="large-hero-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="col-lg-7 hero-content ps-lg-5" data-aos="fade-left">
                    <span class="section-tag hero-tag">Loading your history...</span>
                    <h1 class="hero-title-text">Your Mood<br>History Archives.</h1>
                    <p class="lead hero-lead mb-5">
                        Access your complete record of detected moods and neural patterns. A comprehensive timeline of your emotional journey.
                    </p>
                    <div class="d-flex">
                        <a href="dashboard.php" class="btn btn-hero-primary btn-lg me-3">NEW ANALYSIS</a>
                        <div class="dropdown animate-reveal">
                            <button class="btn btn-hero-outline btn-lg dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-funnel me-1"></i> FILTER BY METHOD
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-danger">
                                <li><a class="dropdown-item dropdown-item-tech active" href="#" data-filter="all">All Methods</a></li>
                                <li><a class="dropdown-item dropdown-item-tech" href="#" data-filter="face">Camera</a></li>
                                <li><a class="dropdown-item dropdown-item-tech" href="#" data-filter="text">Text</a></li>
                                <li><a class="dropdown-item dropdown-item-tech" href="#" data-filter="voice">Voice</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row justify-content-center">
        <div class="col-lg-11">

            <div id="historyContainer">
                <?php if (empty($mood_history)): ?>
                    <div class="empty-archive animate-reveal">
                        <i class="bi bi-hdd-network text-bright-red mb-3 d-block" style="font-size: 3rem;"></i>
                        <h4 class="fw-bold">No Records Found</h4>
                        <p class="text-muted small">Use our detection tools to see your history here.</p>
                        <a href="dashboard.php" class="btn btn-filter mt-3">Try it now</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($mood_history as $record): ?>
                        <div class="history-item animate-reveal" data-type="<?php echo strtolower($record['type']); ?>">
                            <div class="history-card">
                                <div class="card-body d-flex align-items-center flex-wrap flex-md-nowrap gap-3">
                                    
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <?php echo get_mood_icon_tech($record['mood']); ?>
                                        <div>
                                            <span class="archive-id">Log ID: #<?php echo str_pad($record['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                            <h4 class="fw-bold text-white mb-0"><?php echo strtoupper($record['mood']); ?></h4>
                                            <p class="small text-muted mb-0">Emotional state detected and verified.</p>
                                        </div>
                                    </div>

                                    <div class="text-start text-md-center px-md-4 border-start border-dark border-end d-none d-md-block">
                                        <?php echo get_type_badge_tech($record['type']); ?>
                                        <small class="d-block text-muted mt-2" style="font-size: 0.6rem;">Method</small>
                                    </div>

                                    <div class="text-md-end flex-shrink-0 ms-md-4">
                                        <div class="fw-bold text-white" style="font-size: 0.9rem; letter-spacing: 1px;">
                                            <?php echo date('d_M_Y', strtotime($record['timestamp'])); ?>
                                        </div>
                                        <div class="small text-muted" style="font-family: monospace;">
                                            Time: <?php echo date('H:i:s', strtotime($record['timestamp'])); ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
?>
<script src="assets/js/history.js"></script>
