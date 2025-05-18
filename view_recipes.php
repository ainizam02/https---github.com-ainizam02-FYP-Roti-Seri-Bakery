<?php
// Initialize user session for authentication tracking
session_start();
// Include database configuration
require_once 'config/db_connection.php';

// Security Check:
// 1. Verify user authentication status
// 2. Redirect unauthorized users to login page
// 3. Exit script to prevent further execution
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database Operations and Error Handling
try {
    // Security: Use prepared statement for recipe retrieval
    // 1. Prevents SQL injection
    // 2. Orders by date to show newest recipes first
    $stmt = $conn->prepare("SELECT * FROM tbl_recipe ORDER BY recipe_dateCreated DESC");
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize array to store recipe ingredients
    $recipe_ingredients = [];

    // Security: Prepare statement once, reuse for each recipe
    // This is more efficient and maintains security
    $stmt_ing = $conn->prepare("SELECT * FROM tbl_ingredients WHERE recipe_id = ?");

    // Fetch ingredients for each recipe
    // Uses prepared statement with bound parameters for security
    foreach ($recipes as $recipe) {
        $stmt_ing->execute([$recipe['recipe_id']]);
        $recipe_ingredients[$recipe['recipe_id']] = $stmt_ing->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Exception Handling:
    // 1. Catch database-related errors
    // 2. Store error message for display
    // 3. Note: In production, consider logging errors instead of displaying them
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Recipes - Roti Seri Production</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/view_recipes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <?php include 'includes/dashboard_navigation.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Recipe List</h1>
            <div class="divider"></div>
        </div>

        <div class="recipes-container">
            <?php if (isset($error_message)): ?>
                <div class="alert error"><?php echo $error_message; ?></div>
            <?php else: ?>
                <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                    <div class="table-actions">
                        <a href="add_recipe.php" class="add-btn">
                            <i class="fas fa-plus"></i> Add New Recipe
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (empty($recipes)): ?>
                    <div class="no-recipes">
                        <p>No recipes found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="recipes-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Recipe Name</th>
                                    <th>Category</th>
                                    <th>Batch Size</th>
                                    <th>Ingredients</th>
                                    <th>Instructions</th>
                                    <th>Date Created</th>
                                    <th>Last Updated</th>
                                    <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recipes as $recipe): ?>
                                    <tr data-recipe-id="<?php echo $recipe['recipe_id']; ?>">
                                        <td><img src="<?php echo !empty($recipe['image_path']) ? $recipe['image_path'] : 'images/default_recipe_image.jpg'; ?>" alt="Recipe Image" width="100" height="100"></td>
                                        <td><?php echo htmlspecialchars($recipe['recipe_name']); ?></td>
                                        <td>
                                            <span class="category-badge <?php echo strtolower($recipe['recipe_category']); ?>">
                                                <?php echo htmlspecialchars($recipe['recipe_category']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $recipe['recipe_batchSize'] . ' ' . $recipe['recipe_unitOfMeasure']; ?></td>
                                        <td>
                                            <button class="view-ingredients" onclick="viewIngredients(<?php echo $recipe['recipe_id']; ?>)">
                                                View (<?php echo count($recipe_ingredients[$recipe['recipe_id']]); ?>)
                                            </button>
                                        </td>
                                        <td>
                                            <button class="view-instructions" onclick="viewInstructions(<?php echo $recipe['recipe_id']; ?>)">
                                                View Instructions
                                            </button>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($recipe['recipe_dateCreated'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($recipe['recipe_dateUpdated'])); ?></td>
                                        <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                                            <td class="actions">
                                                <a href="edit_recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="action-btn edit-btn" title="Edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button class="action-btn delete-btn" onclick="deleteRecipe(<?php echo $recipe['recipe_id']; ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Image Modal -->
        <div id="image-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Image Recipe</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <img id="modal-image" alt="Recipe Image" style="max-width: 100%; height: auto;">
                </div>
            </div>
        </div>

        <!-- Ingredients Modal -->
        <div id="ingredients-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Recipe Ingredients</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="ingredients-list"></div>
                </div>
            </div>
        </div>

        <!-- Instructions Modal -->
        <div id="instructions-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Recipe Instructions</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="instructions-content"></div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
    <script src="js/view_recipes.js"></script>
</body>

</html>