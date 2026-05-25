<?php
// mood_text.php - Premium Semantic Analysis Unit
session_start();

require_once 'includes/config.php';
require_once 'database/connection.php';
require_once 'includes/header.php';
set_page_title("MoodAI | Semantic Analysis Unit");

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['username'] ?? 'Operative';

if (!$user_id) {
    header("Location: login.php");
    exit();
}
?>
<link rel="stylesheet" href="assets/css/mood_text.css">

<main class="container pb-5 fade-in-section">

    <!-- Hero Section (AI Command Center) -->
    <section class="hero-section dashboard-hero-section mb-4">
        <div class="container px-0">
            <div class="hero-card" data-aos="zoom-in">
                <div class="row align-items-center p-4">
                    <div class="col-lg-5 text-center position-relative mb-5 mb-lg-0" data-aos="fade-right">
                        <!-- Decorative small icons -->
                        <i class="bi bi-pencil-fill decorative-icon icon-1"></i>
                        <i class="bi bi-chat-left-text-fill decorative-icon icon-2"></i>
                        <i class="bi bi-cpu-fill decorative-icon icon-3"></i>
                        <i class="bi bi-file-earmark-text-fill decorative-icon icon-4"></i>

                        <!-- Large Main Tech Icon -->
                        <div class="large-hero-icon">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                    </div>
                    <div class="col-lg-7 hero-content ps-lg-5" data-aos="fade-left">
                        <span class="section-tag hero-tag">System: Semantic Analysis Unit</span>
                        <h1 class="hero-title-text">Mood by Text Analysis.</h1>
                        <p class="lead hero-lead mb-5">
                            Our AI will analyze your written thoughts and feelings to determine your emotional state and suggest movies that match.
                        </p>
                        <div class="d-flex">
                            <a href="dashboard.php" class="btn btn-hero-outline btn-lg">Back to Center</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4 align-items-stretch">
        
        <!-- Sidebar: Objectives -->
        <div class="col-lg-3">
            <aside class="interface-card" data-aos="fade-right" data-aos-delay="100">
                <span class="card-label">How to use</span>
                
                <div class="objective-item">
                    <span class="obj-number">01</span>
                    <p class="obj-text">Type how you are feeling in the box below.</p>
                </div>
                <div class="objective-item">
                    <span class="obj-number">02</span>
                    <p class="obj-text">Our AI will read your text to find your mood.</p>
                </div>
                <div class="objective-item">
                    <span class="obj-number">03</span>
                    <p class="obj-text">We will suggest movies based on your words.</p>
                </div>

                <div class="mt-4 pt-3 border-top border-dark text-center">
                    <i class="bi bi-robot display-4 mb-2 d-block" style="color: var(--accent-red); opacity: 0.5;"></i>
                    <p class="small mb-0" style="font-size: 0.65rem; color: var(--text-white); opacity: 0.7;">AI Text Tool: ON<br>Text Analysis: Ready</p>
                </div>
            </aside>
        </div>

        <!-- Main Content: Terminal Input -->
        <div class="col-lg-6">
            <div class="interface-card" data-aos="fade-up" data-aos-delay="200">
                <span class="card-label">Tell us how you feel</span>
                
                <form id="moodForm">
                    <div class="mb-4">
                        <textarea class="form-control terminal-textarea" 
                                  id="userTextarea" 
                                  name="user_text" 
                                  rows="8" 
                                  placeholder="Write at least 2 or 3 sentences here..." 
                                  required></textarea>
                    </div>

                    <div class="d-grid">
                        <button id="analyzeButton" class="btn btn-protocol btn-primary-protocol" type="submit">
                            <i class="bi bi-cpu me-2"></i> Find My Mood
                        </button>
                    </div>
                </form>

                <div id="statusMessage" class="mt-3 small fw-bold text-muted text-uppercase tracking-wider text-center d-none"></div>
            </div>

        </div>

        <!-- Sidebar: NLP Metrics -->
        <div class="col-lg-3">
            <aside class="interface-card" data-aos="fade-left" data-aos-delay="300">
                <span class="card-label">Text Data</span>
                
                <div class="metrics-grid">
                    <div class="metric-item">
                        <span class="metric-label">Mood Strength</span>
                        <div id="m-sentiment" class="metric-value">Waiting...</div>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Word Count</span>
                        <div id="m-tokens" class="metric-value">0</div>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Text Quality</span>
                        <div id="m-integrity" class="metric-value">Waiting...</div>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Speed</span>
                        <div id="m-latency" class="metric-value">12ms</div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-dark text-center">
                    <div class="small fw-bold animate-flicker" style="color: #444; font-size: 0.6rem;">Connection: Stable</div>
                </div>
            </aside>
        </div>

    </div>
</main>

<?php
require_once 'includes/footer.php';
?>
<script src="assets/js/mood_text.js"></script>