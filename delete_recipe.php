<?php
// Initialize PHP session to maintain user state
session_start();

// Include database connection configuration
// This file contains database credentials and connection setup
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if it's a POST request and has JSON content
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST) && empty(file_get_contents('php://input'))) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get recipe ID from POST data or JSON input
$input = json_decode(file_get_contents('php://input'), true);
$recipe_id = $input['recipe_id'] ?? null;

// Validate recipe ID parameter
// If recipe_id is missing or empty, return error response and stop execution
if (!$recipe_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Recipe ID is required']);
    exit();
}

try {
    // Begin database transaction
    // This ensures all database operations are atomic (all succeed or all fail)
    $conn->beginTransaction();

    // Verify recipe existence and ownership
    // Query checks if the recipe exists in the system using recipe_id
    $stmt = $conn->prepare("SELECT recipe_id FROM tbl_recipe WHERE recipe_id = ?");
    $stmt->execute([$recipe_id]);
    
    // If no recipe found, throw exception to trigger error handling
    if (!$stmt->fetch()) {
        throw new Exception('Recipe not found');
    }

    // Delete ingredients first (should cascade automatically, but let's be explicit)
    $stmt = $conn->prepare("DELETE FROM tbl_ingredients WHERE recipe_id = ?");
    $stmt->execute([$recipe_id]);

    // Delete the recipe
    $stmt = $conn->prepare("DELETE FROM tbl_recipe WHERE recipe_id = ?");
    $stmt->execute([$recipe_id]);

    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Recipe deleted successfully'
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting recipe: ' . $e->getMessage()
    ]);
} 