<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$success_message = $error_message = "";
$fullName = $role = $email = $contact = $address = "";

// CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to sanitize user inputs and prevent XSS
function sanitize_input($data) {
    // Remove script tags or other harmful content
    $data = preg_replace('/<\b[^>]*>(.*?)<\/>/is', "", $data);  // Removes <script> tags
    $data = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/is', "", $data);  // Removes event handlers like onerror, onclick, etc.
    return htmlspecialchars(trim($data));  // HTML-encode the string to prevent XSS
}

// Function to check for harmful SQL commands in inputs
function contains_sql_injection($input) {
    $dangerous_patterns = [
        '/DROP/i', // Detect DROP statements
        '/DELETE/i', // Detect DELETE statements
        '/UPDATE/i', // Detect UPDATE statements
        '/INSERT/i', // Detect INSERT statements
        '/SELECT/i', // Detect SELECT statements
        '/ALTER/i', // Detect ALTER statements
        '/TRUNCATE/i', // Detect TRUNCATE statements
        '/--/i', // Detect SQL comments
        '/\#/i', // Detect SQL comments
        '/\;/i', // Detect semicolons used for ending SQL commands
        '/\/\*/i' // Detect multi-line comments in SQL
    ];
    
    // Check if any dangerous pattern exists in the input
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true; // Harmful SQL detected
        }
    }
    return false; // No harmful SQL found
}

// Get baker details
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE user_id = :edit_id");
        $stmt->execute([':edit_id' => $edit_id]);
        $baker = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($baker) {
            $fullName = sanitize_input($baker['user_fullName']);
            $role = sanitize_input($baker['user_role']);
            $email = sanitize_input($baker['user_email']);
            $contact = sanitize_input($baker['user_contact']);
            $address = sanitize_input($baker['user_address']);
        } else {
            $error_message = "Baker not found.";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF token check
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF attack detected!');
    }

    // Sanitize and validate form data
    $fullName = sanitize_input($_POST['fullName']);
    $role = sanitize_input($_POST['role']);
    $email = sanitize_input($_POST['email']);
    $contact = sanitize_input($_POST['contact']);
    $address = sanitize_input($_POST['address']);

    // Validate data
    if (empty($fullName) || empty($role) || empty($email) || empty($contact) || empty($address)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (!preg_match("/^[0-9]{10}$/", $contact)) {  // Adjust regex for valid contact number
        $error_message = "Invalid contact number.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $fullName)) {  // Check if full name contains only letters and spaces
        $error_message = "Full name can only contain letters and spaces.";
    } elseif (contains_sql_injection($address)) {  // Check for SQL injection in address
        $error_message = "Invalid input detected in address. Please remove any special characters or SQL commands.";
    } else {
        try {
            // Update baker details in the database
            $stmt = $conn->prepare("UPDATE tbl_users 
                                    SET user_fullName = :fullName, user_role = :role, user_email = :email, user_contact = :contact, user_address = :address 
                                    WHERE user_id = :edit_id");
            $stmt->execute([
                ':fullName' => $fullName,
                ':role' => $role,
                ':email' => $email,
                ':contact' => $contact,
                ':address' => $address,
                ':edit_id' => $edit_id
            ]);

            $success_message = "Baker details updated successfully!";
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Baker/Supervisor</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_baker.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/dashboard_navigation.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Edit Baker/Supervisor</h1>
            <div class="divider"></div>
        </div>

        <!-- Form Container -->
        <div class="baker-form-container">
            <!-- Display Messages -->
            <?php if ($success_message): ?>
                <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <!-- Edit Baker/Supervisor Form -->
            <form method="POST" action="">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="baker-form-section">
                    <h2>Personal Details</h2>
                    <div class="baker-form-group">
                        <label for="fullName">Full Name:</label>
                        <input type="text" id="fullName" name="fullName" placeholder="Enter full name" value="<?php echo htmlspecialchars($fullName); ?>" required>
                    </div>
                    <div class="baker-form-group">
                        <label for="role">Role:</label>
                        <select id="role" name="role" required>
                            <option value="Baker" <?php echo $role === 'Baker' ? 'selected' : ''; ?>>Baker</option>
                            <option value="Supervisor" <?php echo $role === 'Supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                        </select>
                    </div>
                </div>

                <div class="baker-form-section">
                    <h2>Contact Information</h2>
                    <div class="baker-form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Enter email address" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="baker-form-group">
                        <label for="contact">Contact:</label>
                        <input type="text" id="contact" name="contact" placeholder="Enter contact number" value="<?php echo htmlspecialchars($contact); ?>" required>
                    </div>
                </div>

                <div class="baker-form-section">
                    <h2>Address</h2>
                    <div class="baker-form-group">
                        <label for="address">Address:</label>
                        <textarea id="address" name="address" placeholder="Enter address" required><?php echo htmlspecialchars($address); ?></textarea>
                    </div>
                </div>

                <div class="baker-form-actions">
                    <button type="submit" class="submit-btn">Update Baker/Supervisor</button>
                    <a href="baker_info.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
