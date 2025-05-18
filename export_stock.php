<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Get stock items data
    $stmt = $conn->query("SELECT b.batch_id, r.recipe_name, r.recipe_category, r.recipe_batchSize
                         FROM tbl_batches b
                         LEFT JOIN tbl_recipe r ON b.recipe_id = r.recipe_id
                         WHERE b.batch_status = 'Completed'
                         ORDER BY b.batch_id DESC");
    $stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="stock_items_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');

    // Start output buffering
    ob_start();

    // Create Excel content
    echo "Stock Items Report\n";
    echo "Generated on: " . date('Y-m-d H:i:s') . "\n\n";

    // Table headers
    echo "Batch ID\tRecipe Name\tCategory\tStock Level\n";

    // Table data
    foreach ($stock_items as $item) {
        echo "#" . $item['batch_id'] . "\t";
        echo $item['recipe_name'] . "\t";
        echo $item['recipe_category'] . "\t";
        echo $item['recipe_batchSize'] . "\n";
    }

    // Get the content and clean the buffer
    $content = ob_get_clean();

    // Output the content
    echo $content;
    exit();

} catch (PDOException $e) {
    // Log error and redirect back to dashboard
    error_log("Export Error: " . $e->getMessage());
    header("Location: dashboard.php?error=export_failed");
    exit();
}