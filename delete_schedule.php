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
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST) && empty(file_get_contents('php://input'))) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get schedule ID from POST data or JSON input
$input = json_decode(file_get_contents('php://input'), true);
$schedule_id = $input['schedule_id'] ?? null;

if (!$schedule_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Schedule ID is required']);
    exit();
}

try {
    $conn->beginTransaction();

    // Check if schedule exists
    $stmt = $conn->prepare("SELECT schedule_id FROM tbl_schedule WHERE schedule_id = ?");
    $stmt->execute([$schedule_id]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Schedule not found');
    }

    // Delete assignments first (should cascade automatically, but let's be explicit)
    $stmt = $conn->prepare("DELETE FROM tbl_schedule_assignments WHERE schedule_id = ?");
    $stmt->execute([$schedule_id]);

    // Delete the schedule
    $stmt = $conn->prepare("DELETE FROM tbl_schedule WHERE schedule_id = ?");
    $stmt->execute([$schedule_id]);

    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Schedule deleted successfully'
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting schedule: ' . $e->getMessage()
    ]);
} 