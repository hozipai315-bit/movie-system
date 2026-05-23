<?php
// guest_login.php - Initialize a Temporary Guest Session
session_start();

// Clear any existing user session
session_unset();

// Set Guest Identity
$_SESSION['user_id'] = 'guest_' . uniqid();
$_SESSION['username'] = 'Guest';
$_SESSION['is_guest'] = true;

// Redirect to Dashboard
header("Location: dashboard.php");
exit();
?>
