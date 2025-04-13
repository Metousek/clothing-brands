<?php
// Start session at the very beginning
session_start();

require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Verify password using the function from config.php
    if (verifyAdminPassword($password)) {
        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        
        // If remember me is checked, set a cookie (30 days)
        if ($remember) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            
            // Store token in session for verification
            $_SESSION['remember_token'] = $token;
            
            // Set cookie with the token (valid for 30 days)
            setcookie('admin_remember', $token, time() + (86400 * 30), '/', '', false, true);
        }
        
        // Redirect to homepage
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Quality Clothing Database</title>
    <link rel="stylesheet" href="styles.css?v=1.0">
</head>
<body>
    <div class="container">
        <div class="header-container">
            <h1>Admin Login</h1>
            <a href="index.php" class="cancel-btn">Back to Home</a>
        </div>
        
        <div class="form-container">
            <?php if ($error): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">Admin Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" class="form-control" required>
                        <button type="button" id="togglePassword" class="password-toggle">Show</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label remember-me">
                        <input type="checkbox" name="remember">
                        Keep me logged in
                    </label>
                </div>
                
                <div class="button-group">
                    <a href="index.php" class="cancel-btn">Cancel</a>
                    <button type="submit" class="submit-btn">Login</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'Show';
            }
        });
    </script>
</body>
</html>