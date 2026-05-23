<?php
// PHP file for site header, included at the beginning of every page.
// NOTE: This file must be included AFTER all core PHP logic (like session_start() and headers)
// and before page content begins.

if (!function_exists('set_page_title')) {
    function set_page_title($title) {
        global $page_title;
        $page_title = $title;
    }
}

$default_title = SITE_NAME . " | Neural Recommendation System";
$page_title = $page_title ?? $default_title;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php
$is_guest = $_SESSION['is_guest'] ?? false;
$current_page = basename($_SERVER['PHP_SELF']);

$nav_links = [
    'Dashboard'  => ['url' => 'dashboard.php', 'icon' => 'bi-grid-1x2'],
    'Face'       => ['url' => 'mood_face.php', 'icon' => 'bi-webcam'],
    'Text'       => ['url' => 'mood_text.php', 'icon' => 'bi-chat-left-text'],
    'Voice'      => ['url' => 'mood_voice.php', 'icon' => 'bi-mic'],
];

if (!$is_guest) {
    $nav_links['History'] = ['url' => 'history.php', 'icon' => 'bi-clock-history'];
    $nav_links['Favorites'] = ['url' => 'favorites.php', 'icon' => 'bi-heart'];
}
?>
<header>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <?php echo htmlspecialchars(SITE_NAME); ?>.
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <?php foreach ($nav_links as $label => $details): ?>
                        <?php $is_active = ($current_page === $details['url']) ? 'active' : ''; ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $is_active; ?>" href="<?php echo htmlspecialchars($details['url']); ?>">
                                <i class="bi <?php echo $details['icon']; ?> me-1"></i>
                                <?php echo $label; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="d-flex">
                    <a class="btn btn-logout" href="logout.php">
                        <?php echo $is_guest ? 'Exit Guest' : 'Logout'; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Universal Mood Result Modal -->
<div class="modal fade" id="moodResultModal" tabindex="-1" aria-labelledby="moodResultModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content mood-modal-content">
            <div class="modal-header border-0 pb-0">
                <span class="modal-protocol-tag">Protocol: Analysis Result</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="modalCloseBtn"></button>
            </div>
            <div class="modal-body text-center p-5">
                <div class="mood-icon-wrapper mb-4">
                    <div class="icon-ring"></div>
                    <i id="modalMoodIcon" class="display-1" style="color: var(--accent-red);"></i>
                </div>
                
                <h2 id="modalMoodText" class="fw-800 text-white mb-2" style="letter-spacing: 2px;"></h2>
                <p class="small text-muted mb-4 px-4">AI analysis complete. Neural patterns synchronized with cinematic database.</p>
                
                <div class="system-readout mb-4">
                    <div class="readout-item">
                        <span class="label">Confidence</span>
                        <span class="value" id="modalConfidence">98.4%</span>
                    </div>
                    <div class="readout-item">
                        <span class="label">Status</span>
                        <span class="value text-success">OPTIMAL</span>
                    </div>
                </div>

                <div class="d-grid gap-3">
                    <a id="modalProceedBtn" href="#" class="btn btn-protocol btn-primary-protocol py-3">
                        <i class="bi bi-film me-2"></i> See My Movies
                    </a>
                    <button type="button" class="btn btn-protocol py-2" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> Try Again
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 justify-content-center">
                <div class="small fw-bold animate-flicker" style="color: #444; font-size: 0.6rem; letter-spacing: 1px;">ENCRYPTED_LINK: STABLE</div>
            </div>
        </div>
    </div>
</div>
