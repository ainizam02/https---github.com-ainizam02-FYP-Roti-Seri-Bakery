<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get filters
$date_filter = $_GET['date'] ?? '';
$recipe_filter = $_GET['recipe'] ?? '';
$status_filter = $_GET['status'] ?? '';
$sort_by = $_GET['sort'] ?? 'schedule_date';
$sort_order = $_GET['order'] ?? 'DESC';

try {
    // Base query
    $query = "SELECT s.*, r.recipe_name, 
              GROUP_CONCAT(DISTINCT CONCAT(u.user_fullName, ' (', u.user_role, ')') SEPARATOR ', ') as assigned_users,
              GROUP_CONCAT(DISTINCT e.equipment_name SEPARATOR ', ') as assigned_equipment
              FROM tbl_schedule s
              LEFT JOIN tbl_recipe r ON s.recipe_id = r.recipe_id
              LEFT JOIN tbl_schedule_assignments sa ON s.schedule_id = sa.schedule_id
              LEFT JOIN tbl_users u ON sa.user_id = u.user_id
              LEFT JOIN tbl_schedule_equipment se ON s.schedule_id = se.schedule_id
              LEFT JOIN tbl_equipments e ON se.equipment_id = e.equipment_id";

    $where_conditions = [];
    $params = [];

    // Apply filters
    if ($date_filter) {
        $where_conditions[] = "s.schedule_date = ?";
        $params[] = $date_filter;
    }
    if ($recipe_filter) {
        $where_conditions[] = "s.recipe_id = ?";
        $params[] = $recipe_filter;
    }
    if ($status_filter) {
        $where_conditions[] = "s.schedule_status = ?";
        $params[] = $status_filter;
    }

    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $query .= " GROUP BY s.schedule_id";
    $query .= " ORDER BY " . ($sort_by == 'schedule_orderVolumn' ? 's.schedule_orderVolumn' : "s.$sort_by") . " $sort_order";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recipes for filter
    $stmt = $conn->query("SELECT recipe_id, recipe_name FROM tbl_recipe ORDER BY recipe_name");
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}

// Helper function for sorting links
function getSortLink($field, $current_sort, $current_order) {
    $new_order = ($current_sort === $field && $current_order === 'ASC') ? 'DESC' : 'ASC';
    $params = $_GET;
    $params['sort'] = $field;
    $params['order'] = $new_order;
    return '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Schedule - Roti Seri Production</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/schedule.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/dashboard_navigation.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Production Schedule</h1>
            <div class="divider"></div>
        </div>

        <div class="schedule-container">
            <!-- Filters -->
            <div class="filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label>Date:</label>
                        <input type="date" name="date" value="<?php echo $date_filter; ?>">
                    </div>
                    <div class="filter-group">
                        <label>Recipe:</label>
                        <select name="recipe">
                            <option value="">All Recipes</option>
                            <?php foreach ($recipes as $recipe): ?>
                                <option value="<?php echo $recipe['recipe_id']; ?>" 
                                    <?php echo $recipe_filter == $recipe['recipe_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($recipe['recipe_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Status:</label>
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="In Progress" <?php echo $status_filter == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <button type="submit" class="filter-btn">Apply Filters</button>
                </form>
                <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                    <a href="add_schedule.php" class="add-btn">
                        <i class="fas fa-plus"></i> Add New Schedule
                    </a>
                <?php endif; ?>
            </div>

            <!-- Schedule Table -->
            <div class="table-responsive">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>
                                <a href="<?php echo getSortLink('schedule_id', $sort_by, $sort_order); ?>" class="sort-link">
                                    Schedule ID
                                    <?php if ($sort_by === 'schedule_id'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo getSortLink('recipe_name', $sort_by, $sort_order); ?>" class="sort-link">
                                    Recipe Name
                                    <?php if ($sort_by === 'recipe_name'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo getSortLink('schedule_date', $sort_by, $sort_order); ?>" class="sort-link">
                                    Date
                                    <?php if ($sort_by === 'schedule_date'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Quantity</th>
                            <th>
                                <a href="<?php echo getSortLink('schedule_batchNum', $sort_by, $sort_order); ?>" class="sort-link">
                                    Number of Batch
                                    <?php if ($sort_by === 'schedule_batchNum'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Assigned Users</th>
                            <th>
                                <a href="<?php echo getSortLink('schedule_status', $sort_by, $sort_order); ?>" class="sort-link">
                                    Status
                                    <?php if ($sort_by === 'schedule_status'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo getSortLink('schedule_orderVolumn', $sort_by, $sort_order); ?>" class="sort-link">
                                    Order Volume
                                    <?php if ($sort_by === 'schedule_orderVolumn'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Equipment</th>
                            <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules)): ?>
                            <tr>
                                <td colspan="8" class="no-records">No schedules found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td><?php echo $schedule['schedule_id']; ?></td>
                                    <td><?php echo htmlspecialchars($schedule['recipe_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($schedule['schedule_date'])); ?></td>
                                    <td><?php echo $schedule['schedule_quantityToProduce']; ?></td>
                                    <td><?php echo $schedule['schedule_batchNum']; ?></td>
                                    <td><?php echo htmlspecialchars($schedule['assigned_users'] ?? 'No users assigned'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $schedule['schedule_status'])); ?>">
                                            <?php echo $schedule['schedule_status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($schedule['schedule_orderVolumn']) . " units"; ?></td>
                                    <td><?php echo htmlspecialchars($schedule['assigned_equipment'] ?? 'No equipment assigned'); ?></td>
                                    <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                                        <td class="actions">
                                            <?php if ($schedule['schedule_status'] !== 'Completed'): ?>
                                                <a href="edit_schedule.php?id=<?php echo $schedule['schedule_id']; ?>" class="btn edit-btn">
                                                    <i class="fas fa-edit"></i> 
                                                </a>
                                            <?php else: ?>
                                                <button class="btn edit-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
                                                    <i class="fas fa-edit"></i> 
                                                </button>
                                            <?php endif; ?>
                                            <button class="delete-btn" onclick="deleteSchedule(<?php echo $schedule['schedule_id']; ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
    <script src="js/schedule.js"></script>
</body>
</html> 