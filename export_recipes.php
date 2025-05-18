<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Get recipes data
    $stmt = $conn->prepare("SELECT r.*, 
                           (SELECT COUNT(*) FROM tbl_ingredients WHERE recipe_id = r.recipe_id) as ingredient_count
                           FROM tbl_recipe r 
                           ORDER BY r.recipe_dateCreated DESC");
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="recipes_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');

    // Start output buffering
    ob_start();

    // Create Excel content
    echo "Recipes Report\n";
    echo "Generated on: " . date('Y-m-d H:i:s') . "\n\n";

    // Table headers
    echo "Recipe Name\tCategory\tBatch Size\tIngredients Count\tDate Created\tLast Updated\n";

    // Table data
    foreach ($recipes as $recipe) {
        echo $recipe['recipe_name'] . "\t";
        echo $recipe['recipe_category'] . "\t";
        echo $recipe['recipe_batchSize'] . ' ' . $recipe['recipe_unitOfMeasure'] . "\t";
        echo $recipe['ingredient_count'] . "\t";
        echo date('M d, Y', strtotime($recipe['recipe_dateCreated'])) . "\t";
        echo date('M d, Y', strtotime($recipe['recipe_dateUpdated'])) . "\n";
    }

    // Get the content and clean the buffer
    $content = ob_get_clean();

    // Output the content
    echo $content;
    exit();

} catch (PDOException $e) {
    // Log error and redirect back to recipes page
    error_log("Export Error: " . $e->getMessage());
    header("Location: view_recipes.php?error=export_failed");
    exit();
} 