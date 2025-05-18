<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if it's a POST request and has JSON content
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SERVER["CONTENT_TYPE"]) || $_SERVER["CONTENT_TYPE"] !== "application/json") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method or content type']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate batch_id
if (!isset($data['batch_id']) || !is_numeric($data['batch_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid batch ID']);
    exit();
}

$batch_id = $data['batch_id'];

try {
    $conn->beginTransaction();

    // First, delete all assignments for this batch
    $stmt = $conn->prepare("DELETE FROM tbl_batch_assignments WHERE batch_id = ?");
    $stmt->execute([$batch_id]);

    // Then, delete the batch itself
    $stmt = $conn->prepare("DELETE FROM tbl_batches WHERE batch_id = ?");
    $stmt->execute([$batch_id]);

    // Commit the transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Batch deleted successfully']);

} catch(PDOException $e) {
    // Rollback the transaction if something failed
    if (isset($conn)) {
        $conn->rollBack();
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Error deleting batch: ' . $e->getMessage()
    ]);
}
?> 