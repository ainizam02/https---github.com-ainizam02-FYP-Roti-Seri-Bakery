<?php
// Start the session and include database connection
session_start();
require_once 'config/db_connection.php';

// Initialize error and success messages
$error_message = '';
$success_message = '';

// Initialize input variables
$fullname = $contact = $email = $address = $password = $confirm_password = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // CSRF token validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token.');
        }

        // Retrieve and sanitize inputs
        $fullname = htmlspecialchars(trim($_POST['fullname']));
        $contact = htmlspecialchars(trim($_POST['contact']));

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        if (!$email) {
            throw new Exception('Invalid email format.');
        }

        $address = htmlspecialchars(trim($_POST['address']));
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $date_register = date('Y-m-d H:i:s');

        // Validate passwords
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long.');
        }
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match.');
        }

        // Google reCAPTCHA secret key
        // $secretKey = '6LfD3LYqAAAAABQo-V5Bca3XxsZvkBnzIgwxZ0DR'; // Replace with your secret key
        // Zie punya key
        $secretKey = '6Ldilx4rAAAAAAXXbbXmxksKf1Gge-67dxaqcLGF'; // Replace with your secret key
        $captchaResponse = $_POST['g-recaptcha-response'];

        // Verify CAPTCHA response with Google's API
        if (empty($captchaResponse)) {
            throw new Exception("Please complete the CAPTCHA verification.");
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $captchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        // Send POST request to Google's API
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === false) {
            throw new Exception("Failed to verify CAPTCHA. Please try again.");
        }

        $responseKeys = json_decode($result, true);

        if (!$responseKeys["success"]) {
            throw new Exception("CAPTCHA verification failed. Please try again.");
        }     

        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_email FROM tbl_users WHERE user_email = ?");
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement.");
        }

        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already registered!");
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user data into the database
        $stmt = $conn->prepare("INSERT INTO tbl_users (user_fullName, user_contact, user_address, user_email, user_password, user_dateRegister) VALUES (?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement.");
        }

        $stmt->execute([$fullname, $contact, $address, $email, $hashed_password, $date_register]);

        // Show success message and redirect
        echo "<script>
            alert('Registration successful! Please login.');
            window.location.href = 'login.php';
        </script>";
    } catch (Exception $e) {
        // Show validation or general error as an alert
        echo "<script>
            alert('" . addslashes($e->getMessage()) . "');
        </script>";
    }
}
// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - YSLProduction</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/register.css">
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
        <!-- Registration Form -->
        <div class="register-container">
            <h2>Create Account</h2>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" action="" class="register-form">
                <!-- Full Name -->
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                </div>

                <!-- Contact Number -->
                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="tel" id="contact" name="contact" value="<?php echo htmlspecialchars($contact); ?>" required>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <!-- Address -->
                <div class='form-group'>
                    <label for='address'>Address</label>
                    <textarea id='address' name='address' required><?php echo htmlspecialchars($address); ?></textarea>
                </div>

                <!-- Password -->
                <div class='form-group'>
                    <label for='password'>Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" 
                            pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}" 
                            title="Password must contain at least 8 characters, including uppercase, lowercase, a number, and a special character." 
                            required minlength="8">
                        <button type="button" class="toggle-password" data-target="password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="toggle-password2" data-target="confirm_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Google reCAPTCHA -->
                <!-- <div class='g-recaptcha' data-sitekey='6LfD3LYqAAAAAOO5DyPIIoFrLU35hz1_2ZKCKDbD'></div> -->
                <!-- Zie punya key -->
                <div class='g-recaptcha' data-sitekey='6Ldilx4rAAAAANWaqDZd6BxHHqWHgACFx4HW36kK'></div>

                <!-- Submit Button -->
                <button type='submit' id='register-button' class='register-submit-btn'>Register</button>
            </form>

            <!-- Include Google reCAPTCHA JavaScript -->
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <script src="js/register.js"></script>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 YSLProduction | Production System</p>
        <img src='assets/images/footer.png' alt='YSL Production Logo'>
    </footer>

</body>

</html>
