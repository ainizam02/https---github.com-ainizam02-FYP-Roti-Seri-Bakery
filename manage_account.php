<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

try {
    // Get user details
    $stmt = $conn->prepare("SELECT user_fullName, user_email, user_contact, user_address, user_role, user_dateRegister 
                           FROM tbl_users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle password change
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        $stmt = $conn->prepare("SELECT user_password FROM tbl_users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $stored_password = $stmt->fetchColumn();

        if (!password_verify($current_password, $stored_password)) {
            $error_message = "Current password is incorrect";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match";
        } elseif (strlen($new_password) < 8) {
            $error_message = "New password must be at least 8 characters long";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE tbl_users SET user_password = ? WHERE user_id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
            $success_message = "Password updated successfully";
        }
    }
} catch(PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Account - YSLProduction</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/dashboard_navigation.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Manage Account</h1>
            <div class="divider"></div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="account-container">
            <!-- User Details Section -->
            <div class="account-section">
                <h2>Account Details</h2>
                <div class="user-details">
                    <div class="detail-group">
                        <label>Full Name</label>
                        <p><?php echo htmlspecialchars($user['user_fullName']); ?></p>
                    </div>
                    <div class="detail-group">
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($user['user_email']); ?></p>
                    </div>
                    <div class="detail-group">
                        <label>Contact</label>
                        <p><?php echo htmlspecialchars($user['user_contact']); ?></p>
                    </div>
                    <div class="detail-group">
                        <label>Address</label>
                        <p><?php echo htmlspecialchars($user['user_address']); ?></p>
                    </div>
                    <div class="detail-group">
                        <label>Role</label>
                        <p><?php echo htmlspecialchars($user['user_role']); ?></p>
                    </div>
                    <div class="detail-group">
                        <label>Member Since</label>
                        <p><?php echo date('F d, Y', strtotime($user['user_dateRegister'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Change Password Section -->
            <div class="account-section">
                <h2>Change Password</h2>
                <form method="POST" class="password-form">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="password-input">
                            <input type="password" id="current_password" name="current_password" required>
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="password-input">
                            <input type="password" id="new_password" name="new_password" required 
                                   minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="password-hint">
                            Password must be at least 8 characters long and include uppercase, lowercase, and numbers
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-input">
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Update Password</button>
                </form>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
    <script>
        // Password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Password confirmation validation
        document.querySelector('.password-form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
            }
        });
    </script>
</body>
</html> 