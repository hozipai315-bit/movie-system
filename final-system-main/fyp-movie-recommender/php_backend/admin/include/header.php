<?php
// admin/includes/header.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../../database/connection.php';

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodAI | Admin Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Michroma&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/admin_style.css"> <!-- Base styles -->
    <?php
    $current_page = basename($_SERVER['PHP_SELF'], '.php');

    // Mapping for specific filenames to their CSS counterparts
    $css_mapping = [
        'dashboard' => 'admin_dashboard',
        'login' => 'admin_login',
        'logs' => 'admin_logs',
        'movies' => 'admin_movies',
        'settings' => 'admin_settings',
        'user' => 'admin_users',
        'user_details' => 'admin_user_details'
    ];

    if (isset($css_mapping[$current_page])) {
        $filename = $css_mapping[$current_page];
        $filepath = "./assets/css/{$filename}.css";
        echo '<link rel="stylesheet" href="' . $filepath . '">';
    }
    ?>

    <!-- Core Libraries (Loaded in Head for page scripts) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="./assets/js/admin_actions.js"></script>
</head>
<body class="admin-body">

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top py-3 admin-navbar-animated">
        <div class="container">
            <a class="navbar-brand fw-800 animate-brand" href="dashboard.php">
                MoodAI<span>.</span>ADMIN
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'user' || $current_page == 'user_details') ? 'active' : ''; ?>" href="user.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'logs' ? 'active' : ''; ?>" href="logs.php">Logs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'movies' ? 'active' : ''; ?>" href="movies.php">Movies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'settings' ? 'active' : ''; ?>" href="settings.php">Settings</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-white fw-600 small me-3 d-none d-lg-inline">
                        <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($admin_username); ?>
                    </span>
                    <a href="logout.php" class="btn btn-primary-admin btn-sm px-4">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
