<?php
// File for authentication helper functions

function isAdminLoggedIn() {
    // Don't start sessions here - sessions should be started at the file level
    
    // Check if user is logged in via session
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }
    
    // Check if user has a remember_me cookie and validate it
    if (isset($_COOKIE['admin_remember']) && !empty($_COOKIE['admin_remember'])) {
        // If there's a session with a token to compare against
        if (isset($_SESSION['remember_token']) && $_SESSION['remember_token'] === $_COOKIE['admin_remember']) {
            // Re-authenticate the user
            $_SESSION['admin_logged_in'] = true;
            return true;
        }
    }
    
    // Ensure we return false by default
    return false;
}

// Function to require admin login for protected pages
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>