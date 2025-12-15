<?php
session_start(); // Start the session

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// If session cookies are being used, clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to another page (e.g., the homepage or login page)
header("Location: lol.php");
exit();
?>
