<?php
// admin/includes/auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
    // If it's an AJAX request, return JSON error
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit();
    }

    // Otherwise redirect to login
    header("Location: login.php");
    exit();
}
?>
