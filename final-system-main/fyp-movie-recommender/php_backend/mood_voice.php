<?php
// mood_voice.php - Premium Vocal Frequency Unit
session_start();

require_once 'includes/config.php';
require_once 'database/connection.php';
require_once 'includes/header.php';
set_page_title("MoodAI | Vocal Frequency Unit");

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['username'] ?? 'Operative';

if (!$user_id) {
    header("Location: login.php");
    exit();
}
?>
<link rel="stylesheet" href="assets/css/mood_voice.css">

<main class="container pb-5 fade-in-section">

    <!-- Hero Section (AI Command Center) -->
    <section class="hero-section dashboard-hero-section mb-4">
        <div class="container px-0">
            <div class="hero-card" data-aos="zoom-in">
                <div class="row align-items-center p-4">
                    <div class="col-lg-5 text-center position-relative mb-5 mb-lg-0" data-aos="fade-right">
                        <!-- Decorative small icons -->
                        <i class="bi bi-mic-fill decorative-icon icon-1"></i>
                        <i class="bi bi-soundwave decorative-icon icon-2"></i>
                        <i class="bi bi-cpu-fill decorative-icon icon-3"></i>
                        <i class="bi bi-broadcast-pin decorative-icon icon-4"></i>

                        <!-- Large Main Tech Icon -->
                        <div class="large-hero-icon">
                            <i class="bi bi-mic"></i>
                        </div>
                    </div>
                    <div class="col-lg-7 hero-content ps-lg-5" data-aos="fade-left">
                        <span class="section-tag hero-tag">System: Vocal Frequency Unit</span>
                        <h1 class="hero-title-text">Mood by Voice Analysis.</h1>
                        <p class="lead hero-lead mb-5">
                            Our AI will analyze the frequency and patterns of your voice to identify your current emotional state with high precision.
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
                    <p class="obj-text">Turn on your microphone to start the link.</p>
                </div>
                <div class="objective-item">
                    <span class="obj-number">02</span>
                    <p class="obj-text">Speak clearly into the mic for 5-10 seconds.</p>
                </div>
                <div class="objective-item">
                    <span class="obj-number">03</span>
                    <p class="obj-text">Our AI will find your mood from your voice patterns.</p>
                </div>

                <div class="mt-4 pt-3 border-top border-dark text-center">
                    <i class="bi bi-soundwave display-4 mb-2 d-block" style="color: var(--accent-red); opacity: 0.5;"></i>
                    <p class="small mb-0" style="font-size: 0.65rem; color: var(--text-white); opacity: 0.7;">AI Voice Tool: ON<br>Mic Quality: Good</p>
                </div>
            </aside>
        </div>

        <!-- Main Content: Diagnostic -->
        <div class="col-lg-6">
            <div class="interface-card text-center" data-aos="fade-up" data-aos-delay="200">
                <span class="card-label">Voice Analysis [ LIVE ]</span>
                
                <div class="acoustic-chamber mb-4">
                    <i id="micIcon" class="bi bi-mic-fill mic-diagnostic"></i>
                    
                    <div id="audioVisualizer" class="audio-visualizer-premium">
                        <!-- JS will populate bars here -->
                    </div>
                </div>

                <div id="recordingStatus" class="mb-4 small fw-bold text-muted text-uppercase">Ready to record.</div>

                <div class="row g-2 justify-content-center mb-3">
                    <div class="col-6">
                        <button id="startRecordButton" class="btn btn-protocol btn-primary-protocol w-100">
                            <i class="bi bi-play-fill me-1"></i> Start Recording
                        </button>
                    </div>
                    <div class="col-6">
                        <button id="stopRecordButton" class="btn btn-protocol btn-danger-protocol w-100" disabled>
                            <i class="bi bi-stop-fill me-1"></i> Stop
                        </button>
                    </div>
                </div>

                <div class="d-grid">
                    <button id="analyzeButton" class="btn btn-protocol w-100" disabled>
                        <i class="bi bi-cpu me-2"></i> Find My Mood
                    </button>
                </div>
            </div>

        </div>

        <!-- Sidebar: Frequency Metrics -->
        <div class="col-lg-3">
            <aside class="interface-card" data-aos="fade-left" data-aos-delay="300">
                <span class="card-label">Voice Data</span>
                
                <div class="diag-grid">
                    <div class="diag-item">
                        <span class="diag-label">Pitch Range</span>
                        <div id="d-range" class="diag-value">0Hz - 0Hz</div>
                    </div>
                    <div class="diag-item">
                        <span class="diag-label">Volume Level</span>
                        <div id="d-gain" class="diag-value">AWAITING_INPUT</div>
                    </div>
                    <div class="diag-item">
                        <span class="diag-label">Mic Status</span>
                        <div id="d-calibration" class="diag-value">Waiting</div>
                    </div>
                    <div class="diag-item">
                        <span class="diag-label">Quality</span>
                        <div class="diag-value">44.1 kHz</div>
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
<script src="assets/js/mood_voice.js"></script>
