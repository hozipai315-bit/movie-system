<?php
// Start the session
session_start();

// --- Registration Success Message Handling ---
$registration_success_message = '';
if (isset($_SESSION['registration_success'])) {
    $registration_success_message = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']); 
}
// --- END Message Handling ---

// --- REQUIRED INCLUDES ---
require_once __DIR__ . '/includes/config.php'; 
require_once __DIR__ . '/database/connection.php'; 

$error_message = '';

// Check if the database connection object ($pdo) was successfully created
if (!isset($pdo) || !$pdo instanceof PDO) {
    $error_message = "Database connection object not initialized. Check your connection files.";
}

// Safely initialize variables
$email = $_POST['email'] ?? '';
$password_input_value = ''; 

// Check for form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'] ?? ''; 

    // Validation
    if (empty($email)) {
        $error_message = "Email is required.";
    } elseif (empty($password)) {
        $error_message = "Password is required.";
    } elseif ($error_message) {
        // Stop if a database initialization error occurred
    } else {
        // Database Query
        try {
            $stmt = $pdo->prepare("SELECT user_id, email, password, username FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
                 $error_message = "Invalid email or password. Please try again.";
                 if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $error_message]);
                    exit();
                 }
            } else {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
                    exit();
                }

                header("Location: dashboard.php");
                exit();
            }
        } catch (Exception $e) {
            error_log("Login Database Error: " . $e->getMessage());
            $error_message = "A system error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodAI | Secure Login</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <div class="login-card animate-slide-up">
        <div class="text-start mb-4">
            <h4 class="fw-bold mb-1 logo-text">MoodAI<span>.</span></h4>
        </div>
        
        <h2 class="login-title text-white">Welcome Back</h2>
        <p class="login-subtitle">Authenticate to access neural recommendations.</p>

        <?php if ($registration_success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo htmlspecialchars($registration_success_message); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            
            <label class="form-label-custom">Identity</label>
            <div class="input-group-premium">
                <span class="input-icon"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" 
                        placeholder="Email Address" required 
                        value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>">
            </div>

            <label class="form-label-custom">Secret Key</label>
            <div class="input-group-premium">
                <span class="input-icon"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" 
                        placeholder="Password" required>
                <button class="toggle-password-btn" type="button" id="togglePassword">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn btn-premium">
                Secure Login
            </button>
        </form>

        <div class="auth-footer">
            New operative? <a href="register.php" class="auth-link">Initialize Account</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/login.js"></script>
</body>
</html>