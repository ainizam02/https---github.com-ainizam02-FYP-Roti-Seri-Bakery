<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Get schedules data with related information
    $query = "SELECT s.*, r.recipe_name, 
              GROUP_CONCAT(DISTINCT CONCAT(u.user_fullName, ' (', u.user_role, ')') SEPARATOR ', ') as assigned_users,
              GROUP_CONCAT(DISTINCT e.equipment_name SEPARATOR ', ') as assigned_equipment
              FROM tbl_schedule s
              LEFT JOIN tbl_recipe r ON s.recipe_id = r.recipe_id
              LEFT JOIN tbl_schedule_assignments sa ON s.schedule_id = sa.schedule_id
              LEFT JOIN tbl_users u ON sa.user_id = u.user_id
              LEFT JOIN tbl_schedule_equipment se ON s.schedule_id = se.schedule_id
              LEFT JOIN tbl_equipments e ON se.equipment_id = e.equipment_id
              GROUP BY s.schedule_id
              ORDER BY s.schedule_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="production_schedules_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');

    // Start output buffering
    ob_start();

    // Create Excel content
    echo "Production Schedule Report\n";
    echo "Generated on: " . date('Y-m-d H:i:s') . "\n\n";

    // Table headers
    echo "Schedule ID\tRecipe Name\tDate\tQuantity\tNumber of Batch\tAssigned Users\tStatus\tOrder Volume\tEquipment\n";

    // Table data
    foreach ($schedules as $schedule) {
        // Escape special characters and wrap in quotes to handle commas and tabs
        $schedule_id = '"' . str_replace('"', '""', $schedule['schedule_id']) . '"';
        $recipe_name = '"' . str_replace('"', '""', $schedule['recipe_name']) . '"';
        $date = '"' . date('M d, Y', strtotime($schedule['schedule_date'])) . '"';
        $quantity = '"' . str_replace('"', '""', $schedule['schedule_quantityToProduce']) . '"';
        $batch_num = '"' . str_replace('"', '""', $schedule['schedule_batchNum']) . '"';
        $assigned_users = '"' . str_replace('"', '""', $schedule['assigned_users'] ?? 'No users assigned') . '"';
        $status = '"' . str_replace('"', '""', $schedule['schedule_status']) . '"';
        $order_volume = '"' . str_replace('"', '""', $schedule['schedule_orderVolumn'] . ' units') . '"';
        $equipment = '"' . str_replace('"', '""', $schedule['assigned_equipment'] ?? 'No equipment assigned') . '"';

        echo $schedule_id . "\t" .
             $recipe_name . "\t" .
             $date . "\t" .
             $quantity . "\t" .
             $batch_num . "\t" .
             $assigned_users . "\t" .
             $status . "\t" .
             $order_volume . "\t" .
             $equipment . "\n";
    }

    // Get the content and clean the buffer
    $content = ob_get_clean();

    // Output the content
    echo $content;
    exit();

} catch (PDOException $e) {
    // Log error and redirect back to schedules page
    error_log("Export Error: " . $e->getMessage());
    header("Location: view_schedules.php?error=export_failed");
    exit();
} 