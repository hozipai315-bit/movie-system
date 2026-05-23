<?php
// Start the session
session_start();

// --- REQUIRED INCLUDES ---
$config_file = __DIR__ . '/includes/config.php'; 
$connection_file = __DIR__ . '/database/connection.php'; 

$pdo = null; 
$error_message = '';
$success_message = '';

if (!file_exists($config_file) || !file_exists($connection_file)) {
     $pdo = null;
     $error_message = "System error: Database configuration missing. Cannot proceed with registration.";
} else {
    require_once $config_file; 
    require_once $connection_file; 
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password_input_value = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'] ?? ''; 
    $confirm_password = $_POST['confirm_password'] ?? ''; 

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Password and Confirm Password do not match.";
    } elseif ($pdo === null) {
        $error_message = "Cannot connect to the database. Registration temporarily unavailable.";
    }

    if ($error_message && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit();
    }

    if (!$error_message) {
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                 $error_message = "This email is already registered. Try logging in.";
                 if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $error_message]);
                    exit();
                 }
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                
                $stmt->bindParam(':username', $name, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                $stmt->execute();
                
                $_SESSION['registration_success'] = 'Account created successfully! Please log in.';
                
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $_SESSION['registration_success']]);
                    exit();
                }

                header("Location: login.php");
                exit();
            }
        } catch (Exception $e) {
            error_log("Registration Database/PHP Error: " . $e->getMessage());
            $error_message = "A system error occurred during registration. Please try again.";
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
    <title>MoodAI | Secure Registration</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>

    <div class="register-card animate-slide-up">
        <div class="text-start mb-4">
            <h4 class="fw-bold mb-1 logo-text">MoodAI<span>.</span></h4>
        </div>
        
        <h2 class="register-title text-white">Join the Network</h2>
        <p class="register-subtitle">Initialize your profile for neural cinematic analysis.</p>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            
            <label class="form-label-custom">Full Name</label>
            <div class="input-group-premium">
                <span class="input-icon"><i class="bi bi-person-circle"></i></span>
                <input type="text" class="form-control" id="name" name="name" 
                        placeholder="Operative Name" required 
                        value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>">
            </div>
            
            <label class="form-label-custom">Email Address</label>
            <div class="input-group-premium">
                <span class="input-icon"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" 
                        placeholder="Network ID" required 
                        value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>">
            </div>

            <label class="form-label-custom">Security Key</label>
            <div class="input-group-premium">
                <span class="input-icon"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" 
                        placeholder="8+ characters" required>
                <button class="toggle-password-btn" type="button" data-target="password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <label class="form-label-custom">Confirm Key</label>
            <div class="input-group-premium">
                <span class="input-icon"><i class="bi bi-shield-lock"></i></span>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                        placeholder="Verify Security Key" required>
                <button class="toggle-password-btn" type="button" data-target="confirm_password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn btn-premium">
                Initialize Account
            </button>
        </form>

        <div class="auth-footer">
            Already registered? <a href="login.php" class="auth-link">Access Terminal</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/register.js"></script>
</body>
</html>