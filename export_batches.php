<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Get batches data with related information
    $query = "SELECT b.*, r.recipe_name, s.schedule_date, s.schedule_batchNum,
              GROUP_CONCAT(DISTINCT CONCAT(u.user_fullName, ' (', ba.ba_task, ')') SEPARATOR ', ') as assigned_users
              FROM tbl_batches b
              LEFT JOIN tbl_recipe r ON b.recipe_id = r.recipe_id
              LEFT JOIN tbl_schedule s ON b.schedule_id = s.schedule_id
              LEFT JOIN tbl_batch_assignments ba ON b.batch_id = ba.batch_id
              LEFT JOIN tbl_users u ON ba.user_id = u.user_id
              GROUP BY b.batch_id
              ORDER BY b.batch_startTime DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="production_batches_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');

    // Start output buffering
    ob_start();

    // Create Excel content
    echo "Production Batches Report\n";
    echo "Generated on: " . date('Y-m-d H:i:s') . "\n\n";

    // Table headers
    echo "Batch ID\tRecipe\tSchedule Date\tStart Time\tEnd Time\tAssigned Users\tStatus\tRemarks\n";

    // Table data
    foreach ($batches as $batch) {
        // Escape special characters and wrap in quotes to handle commas and tabs
        $batch_id = '"' . str_replace('"', '""', $batch['batch_id']) . '"';
        $recipe_name = '"' . str_replace('"', '""', $batch['recipe_name']) . '"';
        $schedule_date = '"' . date('M d, Y', strtotime($batch['schedule_date'])) . '"';
        $start_time = '"' . date('M d, Y H:i', strtotime($batch['batch_startTime'])) . '"';
        $end_time = '"' . date('M d, Y H:i', strtotime($batch['batch_endTime'])) . '"';
        $assigned_users = '"' . str_replace('"', '""', $batch['assigned_users'] ?? 'No assignments') . '"';
        $status = '"' . str_replace('"', '""', $batch['batch_status']) . '"';
        $remarks = '"' . str_replace('"', '""', $batch['batch_remarks'] ?? '-') . '"';

        echo $batch_id . "\t" .
             $recipe_name . "\t" .
             $schedule_date . "\t" .
             $start_time . "\t" .
             $end_time . "\t" .
             $assigned_users . "\t" .
             $status . "\t" .
             $remarks . "\n";
    }

    // Get the content and clean the buffer
    $content = ob_get_clean();

    // Output the content
    echo $content;
    exit();

} catch (PDOException $e) {
    // Log error and redirect back to batches page
    error_log("Export Error: " . $e->getMessage());
    header("Location: view_batches.php?error=export_failed");
    exit();
} 