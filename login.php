<?php
session_start();
require_once 'config/db_connection.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new Exception('Invalid email format.');
        }

        $password = $_POST['password'];
        if (empty($password) || strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long.');
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token.');
        }

        $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE user_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['user_password'])) {
            $_SESSION['user_id'] = htmlspecialchars($user['user_id']);
            $_SESSION['user_fullName'] = htmlspecialchars($user['user_fullName']);
            $_SESSION['user_email'] = htmlspecialchars($user['user_email']);
            $_SESSION['user_role'] = htmlspecialchars($user['user_role']);

            echo "<script>
                alert('Login successful!');
                window.location.href='dashboard.php';
            </script>";
            exit();
        } else {
            throw new Exception('Invalid email or password.');
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Roti Seri Production</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header>
        <nav class="nav-container">
            <a href="homepage.php" class="logo">
                <img src="assets/images/logo_name_w.png" alt="YSL Logo">
            </a>
            <ul class="nav-links">
                <li><a href="homepage.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="login-container">
            <h2>Welcome Back</h2>

            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password" data-target="password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <button type="submit" class="login-submit-btn">Login</button>
                
                <div class="form-links">
                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                    <!-- <div class="register-link">
                        Haven't registered? <a href="register.php">Register here</a>
                    </div> -->
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p class="footer-text">&copy; 2024 Roti Seri Production | Production System</p>
        <img src="assets/images/footer.png" alt="YSL Production Logo" class="footer-logo">
    </footer>
    <script src="js/login.js"></script>
</body>
</html> 