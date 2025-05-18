<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Include Composer's autoloader

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

session_start();
require_once 'config/db_connection.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT user_email FROM tbl_users WHERE user_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate a secure token
            $token = bin2hex(random_bytes(50));
            $expiryTime = date("Y-m-d H:i:s", time() + 900);

            // Store token and expiry time in tbl_users
            $stmt = $conn->prepare("UPDATE tbl_users SET reset_token = ?, token_expiry = ? WHERE user_email = ?");
            $stmt->execute([$token, $expiryTime, $email]);

            $resetLink = "http://localhost:3000/production/reset_password.php?token=$token";

            // Send the reset link via PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['EMAIL_USERNAME'];
                $mail->Password = $_ENV['EMAIL_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('rotibakery@gmail.com', 'Roti Sri Bakery');
                $mail->addAddress($email); // The recipient's email address

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    Hello,<br><br>
                    Click the link below to reset your password:<br>
                    <a href='$resetLink'>$resetLink</a><br><br>
                    If you did not request this, please ignore this email.";

                $mail->send();
                $message = "A password reset link has been sent to your email.";
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Email address not found.";
        }
    } catch (Exception $e) {
        $message = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <main>
        <div class="forgot-password-container">
            <h2>Forgot Password</h2>
            <?php if ($message): ?>
                <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form method="POST" class="forgot-password-form">
                <div class="form-group">
                    <label for="email">Enter your email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" class="login-submit-btn">Submit</button>
                <div class="form-links">
                    <a href="login.php" class="forgot-password">Back to Login</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
