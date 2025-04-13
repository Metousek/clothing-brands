<?php
// Initialize or resume session
session_start();

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Delete remember cookie if exists
if (isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to the homepage
header('Location: index.php');
exit;
?>