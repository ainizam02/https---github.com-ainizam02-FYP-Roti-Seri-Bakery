<?php
// Initialize user session for authentication state
session_start();
// Include database configuration and connection
require_once 'config/db_connection.php';

// Security Measures:
// 1. Check if user is authenticated (session exists)
// 2. Verify recipe_id parameter is provided
// This prevents unauthorized access and invalid requests
if (!isset($_SESSION['user_id']) || !isset($_GET['recipe_id'])) {
    http_response_code(403);    // Return forbidden status code
    exit();
}

try {
    // Database Security:
    // 1. Use prepared statement to prevent SQL injection attacks
    // 2. Parameter binding handles escaping special characters
    $stmt = $conn->prepare("SELECT * FROM tbl_ingredients WHERE recipe_id = ?");
    $stmt->execute([$_GET['recipe_id']]);
    
    // Fetch all ingredients as associative array
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // API Response:
    // 1. Set proper JSON content type header
    // 2. Convert PHP array to JSON format
    header('Content-Type: application/json');
    echo json_encode($ingredients);

} catch(PDOException $e) {
    // Exception Handling:
    // 1. Set HTTP status code to 500 (Internal Server Error)
    // 2. Return error message in JSON format
    // 3. Note: In production, consider not exposing detailed error messages
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 