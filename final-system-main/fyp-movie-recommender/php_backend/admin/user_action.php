<?php
// admin/user_actions.php
require_once 'include/auth_check.php';
require_once "../database/connection.php";

if (!$pdo) {
    die("Action failed: System connection error. " . ($db_error ?? ""));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? 0;
    
    // Validate redirect to prevent open redirect vulnerabilities
    $allowed_redirects = ['user.php', 'users.php', 'user_details.php'];
    $redirect = 'user.php';
    if (isset($_POST['redirect'])) {
        $parsed_url = parse_url($_POST['redirect']);
        $path = basename($parsed_url['path'] ?? '');
        if (in_array($path, $allowed_redirects)) {
            // Reconstruct the redirect URL with original query parameters
            $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
            $redirect = $path . $query;
        }
    }

    if ($action === 'toggle_status' && $user_id) {
        $new_status = $_POST['new_status'] ?? 'active';
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $stmt->execute([$new_status, $user_id]);
            header("Location: $redirect");
            exit();
        } catch (Exception $e) {
            die("Database error: " . $e->getMessage());
        }
    }
    
    if ($action === 'delete_user' && $user_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            header("Location: user.php");
            exit();
        } catch (Exception $e) {
            die("Database error: " . $e->getMessage());
        }
    }
}

header("Location: user.php");
exit();
?>
