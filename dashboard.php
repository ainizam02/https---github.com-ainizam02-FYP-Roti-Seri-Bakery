<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Get total recipes
    $stmt = $conn->query("SELECT COUNT(*) as total_recipes FROM tbl_recipe");
    $total_recipes = $stmt->fetch(PDO::FETCH_ASSOC)['total_recipes'];

    // Get total schedules
    $stmt = $conn->query("SELECT COUNT(*) as total_schedules FROM tbl_schedule");
    $total_schedules = $stmt->fetch(PDO::FETCH_ASSOC)['total_schedules'];

    // Only fetch total bakers if the user is an Admin
    $total_bakers = null;
    if ($_SESSION['user_role'] === 'Admin') {
        $stmt = $conn->query("SELECT COUNT(*) as total_bakers FROM tbl_users WHERE user_role = 'Baker' or user_role = 'Supervisor'");
        $total_bakers = $stmt->fetch(PDO::FETCH_ASSOC)['total_bakers'];
    }

    // Get total batches
    $stmt = $conn->query("SELECT COUNT(*) as total_batches FROM tbl_batches");
    $total_batches = $stmt->fetch(PDO::FETCH_ASSOC)['total_batches'];

    // Get stock items data
    $stmt = $conn->query("SELECT b.batch_id, r.recipe_name, r.recipe_category, r.recipe_batchSize
                         FROM tbl_batches b
                         LEFT JOIN tbl_recipe r ON b.recipe_id = r.recipe_id
                         WHERE b.batch_status = 'Completed'
                         ORDER BY b.batch_id DESC");
    $stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Roti Seri Production</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/view_recipes.css">
    <link rel="stylesheet" href="css/view_stock.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <?php include 'includes/dashboard_navigation.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <div class="divider"></div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card" onclick="window.location.href='view_recipes.php'">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Recipes</h3>
                    <p><?php echo $total_recipes; ?></p>
                </div>
            </div>

            <div class="stat-card" onclick="window.location.href='view_schedules.php'">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Schedules</h3>
                    <p><?php echo $total_schedules; ?></p>
                </div>
            </div>

            <?php if ($_SESSION['user_role'] === 'Admin'): ?>
                <div class="stat-card" onclick="window.location.href='baker_info.php'">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Bakers</h3>
                        <p><?php echo $total_bakers; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="stat-card" onclick="window.location.href='view_batches.php'">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Batches</h3>
                    <p><?php echo $total_batches; ?></p>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2>Stock Items</h2>
                <a href="export_stock.php" class="export-btn">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
            </div>
            <?php if (isset($error_message)): ?>
                <div class="alert error"><?php echo $error_message; ?></div>
            <?php else: ?>
                <?php if (empty($stock_items)): ?>
                    <div class="no-data">
                        <p>No stock items found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="recipes-table">
                            <thead>
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Recipe Name</th>
                                    <th>Category</th>
                                    <th>Stock Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stock_items as $item): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($item['batch_id']); ?></td>
                                        <td><?php echo htmlspecialchars($item['recipe_name']); ?></td>
                                        <td>
                                            <span class="category-badge <?php echo strtolower($item['recipe_category']); ?>">
                                                <?php echo htmlspecialchars($item['recipe_category']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="stock-level">
                                                <div class="progress-bar">
                                                    <?php
                                                    $stockLevel = $item['recipe_batchSize'];
                                                    $percentage = min(100, ($stockLevel / 100) * 100);
                                                    $status = $percentage < 30 ? 'low' : ($percentage < 70 ? 'medium' : 'high');
                                                    ?>
                                                    <div class="progress-fill <?php echo $status; ?>" style="width: <?php echo $percentage; ?>%"></div>
                                                </div>
                                                <span class="stock-value"><?php echo htmlspecialchars($item['recipe_batchSize']); ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Rest of your dashboard content -->
    </main>

    <script src="js/dashboard.js"></script>
</body>

</html>