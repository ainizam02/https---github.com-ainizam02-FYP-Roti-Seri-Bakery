<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Get recipes data with ingredients
    $stmt = $conn->prepare("SELECT r.*, 
                           GROUP_CONCAT(CONCAT(i.ingredient_name, ' (', i.ingredient_quantity, ' ', i.ingredient_unitOfMeasure, ')') 
                           SEPARATOR ', ') as ingredients_list
                           FROM tbl_recipe r 
                           LEFT JOIN tbl_ingredients i ON r.recipe_id = i.recipe_id
                           GROUP BY r.recipe_id
                           ORDER BY r.recipe_dateCreated DESC");
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // var_dump($recipes);
    // die();

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
    echo "Recipe Name\tCategory\tBatch Size\tIngredients\tInstructions\tDate Created\tLast Updated\n";

    // Table data
    foreach ($recipes as $recipe) {
        // Escape special characters and wrap in quotes to handle commas and tabs
        $recipe_name = '"' . str_replace('"', '""', $recipe['recipe_name']) . '"';
        $category = '"' . str_replace('"', '""', $recipe['recipe_category']) . '"';
        $batch_size = '"' . str_replace('"', '""', $recipe['recipe_batchSize'] . ' ' . $recipe['recipe_unitOfMeasure']) . '"';
        $ingredients = '"' . str_replace('"', '""', $recipe['ingredients_list'] ?: 'No ingredients') . '"';
        $instructions = '"' . str_replace('"', '""', $recipe['recipe_instructions'] ?: 'No instructions') . '"';
        $date_created = '"' . date('M d, Y', strtotime($recipe['recipe_dateCreated'])) . '"';
        $date_updated = '"' . date('M d, Y', strtotime($recipe['recipe_dateUpdated'])) . '"';

        echo $recipe_name . "\t" .
             $category . "\t" .
             $batch_size . "\t" .
             $ingredients . "\t" .
             $instructions . "\t" .
             $date_created . "\t" .
             $date_updated . "\n";
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