<?php
// analyzing.php - Transition Screen for AI Analysis
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$last_detected_mood = $_SESSION['last_detected_mood'] ?? null;

// Basic safety redirect
if (!$user_id || !$last_detected_mood) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodAI | Analyzing Neural Signal...</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/analyzing.css">
    
    <!-- Automatic Redirect after 3.5 seconds -->
    <meta http-equiv="refresh" content="3.5;url=recommendation.php">
</head>
<body>

    <div class="analyzing-container">
        
        <div class="tech-loader">
            <div class="loader-ring"></div>
            <div class="loader-ring"></div>
            <div class="loader-center">
                <i class="bi bi-cpu"></i>
            </div>
        </div>

        <div class="status-text animate-reveal">Analyzing Signal</div>
        <div class="sub-status animate-flicker">MAPPING MOOD: <?php echo strtoupper(htmlspecialchars($last_detected_mood)); ?></div>

        <div class="progress-container">
            <div class="progress-bar-tech"></div>
        </div>

        <p class="small text-muted mt-4 opacity-75" style="letter-spacing: 1px;">
            Synchronizing with neural cinematic database...
        </p>

    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>
