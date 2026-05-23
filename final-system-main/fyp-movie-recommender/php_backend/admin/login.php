<?php
// admin/login.php
session_start();

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once "../database/connection.php";

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = "All fields are required.";
    } else {
        try {
            $authenticated = false;
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT admin_id, username, email, password FROM admins WHERE email = :email");
                $stmt->execute(['email' => $email]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password'])) {
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $authenticated = true;
                }
            } else {
                $error_message = "Authentication service unavailable (Database offline).";
            }

            if ($authenticated) {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
                    exit();
                }
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Access Denied: Invalid Credentials";
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $error_message]);
                    exit();
                }
            }
        } catch (Exception $e) {
            $error_message = "System Error: " . $e->getMessage();
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error_message]);
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodAI | Admin Control Center</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Michroma&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/admin_login.css">
</head>
<body>

    <div class="restricted-banner">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> RESTRICTED ACCESS: AUTHORIZED PERSONNEL ONLY
    </div>

    <div class="split-login-container">
        
        <!-- Left Side: Login Form -->
        <div class="login-form-side" data-aos="fade-right" data-aos-duration="1000">
            <div class="form-wrapper">
                <div class="admin-logo">MoodAI<span>.</span>ADMIN</div>
                <h2 class="admin-login-title">Control Center</h2>
                <p class="admin-login-subtitle">Initialize administrative session.</p>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger" style="font-size: 0.8rem; border-radius: 6px;">
                        <i class="bi bi-shield-slash me-2"></i> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form id="adminLoginForm" action="login.php" method="POST">
                    
                    <label class="admin-form-label">Admin Identity</label>
                    <div class="admin-input-group">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="email" placeholder="Email Address" required>
                    </div>

                    <label class="admin-form-label">Command Key</label>
                    <div class="admin-input-group">
                        <i class="bi bi-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <button type="submit" id="submitBtn" class="btn-admin-login">
                        <span class="btn-text">Authenticate Access</span>
                        <div class="scan-overlay"></div>
                    </button>

                </form>
            </div>
        </div>

        <!-- Right Side: Lottie Animation -->
        <div class="login-visual-side" data-aos="fade-left" data-aos-duration="1000">
            <div class="lottie-container">
                <dotlottie-player src="https://lottie.host/e5b01910-53f6-45a7-9af4-e6d10c10196a/nSCKcr5vSy.lottie" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></dotlottie-player>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <script src="./assets/js/admin_login.js"></script>
</body>
</html>
