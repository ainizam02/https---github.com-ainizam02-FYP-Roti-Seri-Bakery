<?php
require_once 'config/db_connection.php';

if (isset($_GET['recipe_id'])) {
    try {
        $stmt = $conn->prepare("SELECT recipe_instructions FROM tbl_recipe WHERE recipe_id = ?");
        $stmt->execute([$_GET['recipe_id']]);
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'instructions' => $recipe['recipe_instructions']
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Recipe ID not provided'
    ]);
}
?> 