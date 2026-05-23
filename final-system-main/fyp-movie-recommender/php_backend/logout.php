<?php
// logout.php
// Securely handles the user logout process.

// 1. Start the session to access and manage session variables.
session_start();

// --- Security Step 1: Prevent Session Fixation and Ensure Immediate Invalidation ---
// Regenerate the session ID immediately. This forces the use of a new, clean session ID 
// for any subsequent requests, even before the old session data is destroyed.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Setting the lifetime to a past time forces the browser to discard the session cookie immediately.
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 2. Unset all session variables.
// This clears the $_SESSION array, removing all registered session variables.
$_SESSION = array();

// 3. Destroy the session completely.
// This deletes the session file on the server.
session_destroy();

// 4. Redirect the user to the login page.
// This is the final step, preventing unauthorized access to protected pages
// after the session has been terminated.
header("Location: index.php");
exit(); // Ensure no further code is executed after redirection.
?>