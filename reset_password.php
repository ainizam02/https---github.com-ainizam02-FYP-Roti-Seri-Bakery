<?php
session_start();
require_once 'config/db_connection.php';

$message = '';
$error_message = '';

// First step: Email validation
if (isset($_POST['email']) && !isset($_GET['token'])) {
    $email = $_POST['email'];
    
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE user_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $stmt = $conn->prepare("UPDATE tbl_users SET reset_token = ?, token_expiry = ? WHERE user_email = ?");
        $stmt->execute([$token, $expiry, $email]);
        
        // Send reset link (you can implement actual email sending here)
        $resetLink = "http://localhost/production/reset_password.php?token=" . $token;
        $_SESSION['success'] = "Password reset link has been sent to your email.";
        
        // For development, display the reset link
        $message = "Reset link (for development): <a href='$resetLink'>$resetLink</a>";
    } else {
        $error_message = "No account found with this email address.";
    }
}

// Validate token and expiry time
$stmt = $conn->prepare("SELECT * FROM tbl_users WHERE reset_token = ?");
$stmt->execute([$_GET['token']]);
$user = $stmt->fetch();

if ($user) {
    $expiryTime = strtotime($user['token_expiry']);
    $currentTime = time();

    if ($currentTime > $expiryTime) {
        $message = "Invalid or expired reset token.";
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Server-side validation
            $errors = [];
            
            // Check password length
            if (strlen($new_password) < 8) {
                $errors[] = "Password must be at least 8 characters long";
            }
            
            // Check for uppercase
            if (!preg_match('/[A-Z]/', $new_password)) {
                $errors[] = "Password must contain at least one uppercase letter";
            }
            
            // Check for lowercase
            if (!preg_match('/[a-z]/', $new_password)) {
                $errors[] = "Password must contain at least one lowercase letter";
            }
            
            // Check for numbers
            if (!preg_match('/[0-9]/', $new_password)) {
                $errors[] = "Password must contain at least one number";
            }
            
            // Check for special characters
            if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
                $errors[] = "Password must contain at least one special character";
            }
            
            // Check if passwords match
            if ($new_password !== $confirm_password) {
                $errors[] = "Passwords do not match";
            }
            
            if (empty($errors)) {
                // Hash the password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                try {
                    // Update the password in the database
                    $stmt = $conn->prepare("UPDATE tbl_users SET user_password = ? WHERE user_id = ?");
                    $stmt->execute([$hashed_password, $user['user_id']]);
                    
                    $_SESSION['success'] = "Password has been reset successfully";
                    header("Location: login.php");
                    exit();
                } catch(PDOException $e) {
                    $error_message = "Error updating password: " . $e->getMessage();
                }
            } else {
                $error_message = implode("<br>", $errors);
            }
        }
    }
} else {
    $message = "Invalid or expired reset token.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <main>
        <div class="forgot-password-container">
            <h2>Reset Password</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (!isset($_GET['token'])): ?>
                <!-- Email Form -->
                <form method="POST" class="reset-email-form">
                    <div class="form-group">
                        <label for="email">Enter your email address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <button type="submit" class="login-submit-btn">Send Reset Link</button>
                    <div class="form-links">
                        <a href="login.php" class="back-to-login">Back to Login</a>
                    </div>
                </form>
            <?php else: ?>
                <!-- Password Reset Form (your existing form) -->
                <?php if ($user): ?>
                    <form class="reset-password-form" method="POST" action="">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="password-field">
                                <input type="password" id="new_password" name="new_password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-error" class="error-message" style="display: none;"></div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="password-field">
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="confirm-error" class="error-message" style="display: none;"></div>
                        </div>
                        <button type="submit" class="login-submit-btn">Reset Password</button>
                        <div class="form-links">
                            <a href="login.php" class="back-to-login">Back to Login</a>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
    <script src="js/reset_password.js"></script>
</body>
</html>
