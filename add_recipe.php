<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate batch size
        $recipe_batchSize = floatval($_POST['batch_size']);
        if ($recipe_batchSize < 0.01) {
            throw new Exception("Batch size cannot be less than 0.01");
        }

        // Validate ingredient quantities
        foreach ($_POST['ingredient_quantity'] as $quantity) {
            if (floatval($quantity) < 0.01) {
                throw new Exception("Ingredient quantity cannot be less than 0.01");
            }
        }

        $conn->beginTransaction();

        // Get recipe details
        $recipe_name = htmlspecialchars(trim($_POST['recipe_name']));
        $recipe_category = htmlspecialchars(trim($_POST['recipe_category']));
        //$recipe_batchSize = floatval($_POST['batch_size']);
        $recipe_unitOfMeasure = htmlspecialchars(trim($_POST['unit_of_measure']));
        $recipe_instructions = htmlspecialchars(trim($_POST['recipe_instructions']));

        // Handle image upload
        $image_path = null;
        if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] == 0) {
            $target_dir = "images/recipes/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $target_file = $target_dir . uniqid() . "_" . basename($_FILES['recipe_image']['name']);
            if (move_uploaded_file($_FILES['recipe_image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                throw new Exception("Failed to upload the recipe image.");
            }
        }

        // Insert recipe
        $stmt = $conn->prepare("INSERT INTO tbl_recipe (recipe_name, recipe_category, recipe_batchSize, recipe_unitOfMeasure, recipe_instructions, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$recipe_name, $recipe_category, $recipe_batchSize, $recipe_unitOfMeasure, $recipe_instructions, $image_path]);

        $recipe_id = $conn->lastInsertId();

        // Insert ingredients
        $stmt = $conn->prepare("INSERT INTO tbl_ingredients (recipe_id, ingredient_name, ingredient_quantity, ingredient_unitOfMeasure) VALUES (?, ?, ?, ?)");

        foreach ($_POST['ingredient_name'] as $key => $name) {
            if (!empty($name)) {
                $stmt->execute([
                    $recipe_id,
                    trim($name),
                    floatval($_POST['ingredient_quantity'][$key]),
                    trim($_POST['ingredient_unit'][$key])
                ]);
            }
        }

        $conn->commit();
        $success_message = "Recipe added successfully!";
        
    } catch(Exception $e) {
        $conn->rollBack();
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recipe - Roti Seri Production</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/add_recipe.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/dashboard_navigation.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Add New Recipe</h1>
            <div class="divider"></div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" class="recipe-form" enctype="multipart/form-data">
            <div class="form-section">
                <h2>Recipe Details</h2>
                <div class="form-group">
                    <label for="recipe_name">Recipe Name</label>
                    <input type="text" id="recipe_name" name="recipe_name" required>
                </div>

                <div class="form-group">
                    <label for="recipe_image">Recipe Image</label>
                    <input type="file" name="recipe_image" accept="image/*">
                    <div class="image-preview" id="imagePreview">
                        <img src="" alt="Recipe Image Preview" class="image-preview__image" style="display: none; width: 400px; height: 300px; object-fit: cover;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="recipe_category">Category</label>
                    <select id="recipe_category" name="recipe_category" required>
                        <option value="">Select Category</option>
                        <option value="Bread">Bread</option>
                        <option value="Cake">Cake</option>
                        <option value="Pastry">Pastry</option>
                        <option value="Cookie">Cookie</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="batch_size">Batch Size</label>
                        <input type="number" 
                               id="batch_size" 
                               name="batch_size" 
                               step="0.01" 
                               min="0.01" 
                               required 
                               oninput="validateBatchSize(this)"
                               onblur="validateBatchSize(this)">
                    </div>
                    <div class="form-group">
                        <label for="unit_of_measure">Unit of Measure</label>
                        <select id="unit_of_measure" name="unit_of_measure" required>
                            <option value="">Select Unit</option>
                            <option value="pcs">Pieces</option>
                            <option value="kg">Kilograms</option>
                            <option value="g">Grams</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Recipe Instructions Section -->
            <div class="form-section">
                <h2>Recipe Instructions</h2>
                <div class="form-group">
                    <label for="recipe_instructions">Instructions (Enter each step on a new line)</label>
                    <textarea 
                        id="recipe_instructions" 
                        name="recipe_instructions" 
                        rows="10" 
                        placeholder="1. First step
2. Second step
3. Third step
4. Fourth step..."
                        required
                    ></textarea>
                    <small class="form-text">Enter each instruction step on a new line, preferably numbered.</small>
                </div>
            </div>

            <div class="form-section">
                <h2>Ingredients</h2>
                <div id="ingredients-container">
                    <div class="ingredient-row">
                        <div class="form-group">
                            <label>Ingredient Name</label>
                            <input type="text" name="ingredient_name[]" required>
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" 
                                   name="ingredient_quantity[]" 
                                   step="0.01" 
                                   min="0.01" 
                                   required 
                                   oninput="validateQuantity(this)"
                                   onblur="validateQuantity(this)">
                        </div>
                        <div class="form-group">
                            <label>Unit</label>
                            <select name="ingredient_unit[]" required>
                                <option value="">Select Unit</option>
                                <option value="kg">Kilograms</option>
                                <option value="g">Grams</option>
                                <option value="l">Liters</option>
                                <option value="ml">Milliliters</option>
                                <option value="pcs">Pieces</option>
                            </select>
                        </div>
                        <button type="button" class="remove-ingredient" onclick="removeIngredient(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <button type="button" class="add-ingredient" onclick="addIngredient()">
                    <i class="fas fa-plus"></i> Add Ingredient
                </button>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Save Recipe</button>
                <a href="view_recipes.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </main>

    <?php if ($success_message): ?>
        <div class="alert success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <script>
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.querySelector('.image-preview__image');
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }

        if (file) {
            reader.readAsDataURL(file);
        }
    });
</script>

    <script src="js/dashboard.js"></script>
    <script src="js/add_recipe.js"></script>
</body>
</html>