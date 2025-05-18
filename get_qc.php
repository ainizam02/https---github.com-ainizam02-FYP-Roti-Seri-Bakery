<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in and batch_id is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['batch_id'])) {
    http_response_code(403); // Forbidden
    exit();
}

try {
    // Fetch quality check data for the given batch_id
    $stmt = $conn->prepare("SELECT * FROM tbl_quality_checks WHERE batch_id = ?");
    $stmt->execute([$_GET['batch_id']]);
    $quality_checks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($quality_checks);
} catch (PDOException $e) {
    // Handle errors
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>