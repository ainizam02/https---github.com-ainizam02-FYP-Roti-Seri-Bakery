<?php
session_start();
require_once 'config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* Update the valid sort columns in view_batches.php */
$valid_sort_columns = ['batch_id', 'recipe_name', 'schedule_date', 'batch_startTime', 'batch_status', 'quality_check']; 

try {
    // Get filters
    $recipe_filter = $_GET['recipe'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $date_filter = $_GET['date'] ?? '';
    $sort_by = $_GET['sort'] ?? 'batch_startTime';
    $sort_order = $_GET['order'] ?? 'DESC';

    // Get all recipes for filter dropdown
    $stmt = $conn->query("SELECT recipe_id, recipe_name FROM tbl_recipe ORDER BY recipe_name");
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Base query
    $query = "SELECT b.*, r.recipe_name, s.schedule_date, s.schedule_batchNum,
              (SELECT COUNT(*) FROM tbl_batches WHERE schedule_id = b.schedule_id) as assigned_batches,
              GROUP_CONCAT(DISTINCT CONCAT(u.user_fullName, ' (', ba.ba_task, ')') SEPARATOR ', ') as assigned_users
              FROM tbl_batches b
              LEFT JOIN tbl_recipe r ON b.recipe_id = r.recipe_id
              LEFT JOIN tbl_schedule s ON b.schedule_id = s.schedule_id
              LEFT JOIN tbl_batch_assignments ba ON b.batch_id = ba.batch_id
              LEFT JOIN tbl_users u ON ba.user_id = u.user_id";

    $where_conditions = [];
    $params = [];

    // Apply filters
    if ($recipe_filter) {
        $where_conditions[] = "b.recipe_id = ?";
        $params[] = $recipe_filter;
    }
    if ($status_filter) {
        $where_conditions[] = "b.batch_status = ?";
        $params[] = $status_filter;
    }
    if ($date_filter) {
        $where_conditions[] = "DATE(b.batch_startTime) = ?";
        $params[] = $date_filter;
    }

    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $query .= " GROUP BY b.batch_id";

    // Add sorting
    $valid_sort_columns = ['batch_id', 'recipe_name', 'schedule_date', 'batch_startTime', 'batch_status', 'quality_check'];
    if (in_array($sort_by, $valid_sort_columns)) {
        $query .= " ORDER BY $sort_by $sort_order";
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add this before the statistics section
    if ($recipe_filter) {
        // Get current schedule information and completed batches count
        $stmt = $conn->prepare("SELECT s.*, 
                               (SELECT COUNT(*) FROM tbl_batches WHERE schedule_id = s.schedule_id) as assigned_batches,
                               (SELECT COUNT(*) FROM tbl_batches WHERE schedule_id = s.schedule_id AND batch_status = 'Completed') as completed_batches
                               FROM tbl_schedule s 
                               WHERE s.recipe_id = ? 
                               AND s.schedule_status != 'Completed'
                               ORDER BY s.schedule_date DESC 
                               LIMIT 1");
        $stmt->execute([$recipe_filter]);
        $currentSchedule = $stmt->fetch();
    }

} catch(PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}

// Function to create sort URL
function getSortUrl($column) {
    $params = $_GET;
    $params['sort'] = $column;
    $params['order'] = ($column === ($_GET['sort'] ?? '') && ($_GET['order'] ?? 'DESC') === 'DESC') ? 'ASC' : 'DESC';
    return '?' . http_build_query($params);
}

// Function to get sort indicator
function getSortIndicator($column) {
    if ($column === ($_GET['sort'] ?? '')) {
        return ($_GET['order'] ?? 'DESC') === 'DESC' ? '▼' : '▲';
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Batches - Roti Seri Production</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/batch.css">
    <link rel="stylesheet" href="css/view_recipes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/dashboard_navigation.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Batch List</h1>
            <div class="divider"></div>
        </div>

        <div class="batch-container">
            <!-- Filters -->
            <div class="filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="recipe">Recipe</label>
                        <select name="recipe" id="recipe">
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
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" value="<?php echo $date_filter; ?>">
                    </div>

                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </form>

                <a href="add_batch.php" class="add-btn">
                    <i class="fas fa-plus"></i> Add New Batch
                </a>
            </div>

            <!-- Batches Table -->
            <div class="table-container">
                <table class="batch-table">
                    <thead>
                        <tr>
                            <th>
                                <a href="<?php echo getSortUrl('batch_id'); ?>" class="sort-link">
                                    ID <?php echo getSortIndicator('batch_id'); ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo getSortUrl('recipe_name'); ?>" class="sort-link">
                                    Recipe <?php echo getSortIndicator('recipe_name'); ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo getSortUrl('schedule_date'); ?>" class="sort-link">
                                    Schedule Date <?php echo getSortIndicator('schedule_date'); ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo getSortUrl('batch_startTime'); ?>" class="sort-link">
                                    Start Time <?php echo getSortIndicator('batch_startTime'); ?>
                                </a>
                            </th>
                            <th>End Time</th>
                            <th>Assigned Users</th>
                            <th>
                                <a href="<?php echo getSortUrl('batch_status'); ?>" class="sort-link">
                                    Status <?php echo getSortIndicator('batch_status'); ?>
                                </a>
                            </th>
                            <th>
                                <label for="quality_check">Quality Check</label>
                            </th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($batches)): ?>
                            <tr>
                                <td colspan="9" class="no-records">No batches found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($batches as $batch): ?>
                                <tr data-batch-id="<?php echo $batch['batch_id']; ?>">
                                    <td><?php echo $batch['batch_id']; ?></td>
                                    <td><?php echo htmlspecialchars($batch['recipe_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($batch['schedule_date'])); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($batch['batch_startTime'])); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($batch['batch_endTime'])); ?></td>
                                    <td><?php echo htmlspecialchars($batch['assigned_users'] ?? 'No assignments'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $batch['batch_status'])); ?>">
                                            <?php echo $batch['batch_status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- <div class="quality-comments">
                                            <?php echo htmlspecialchars($batch['quality_check'] ?? '-'); ?>
                                        </div> -->
                                        <a href="quality_check.php?id=<?php echo $batch['batch_id']; ?>&section=quality_check" 
                                           class="action-btn edit-btn <?php echo ($batch['batch_status'] !== 'Completed') ? 'disabled' : ''; ?>" 
                                           title="<?php echo ($batch['batch_status'] !== 'Completed') ? 'Cannot edit completed batch' : 'Edit'; ?>"
                                           <?php echo ($batch['batch_status'] !== 'Completed') ? 'onclick="return false;"' : ''; ?>>
                                            <i class="fas fa-edit"></i> 
                                        </a>
                                        <button class="action-btn edit-btn" 
                                                onclick="viewQc(<?php echo $batch['batch_id']; ?>)" 
                                                title="View Quality Check">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                    <td class="remarks-cell"><?php echo htmlspecialchars($batch['quality_check'] ?? '-'); ?></td>
                                    <td class="actions">
                                        <a href="edit_batch.php?id=<?php echo $batch['batch_id']; ?>&section=action" 
                                           class="action-btn edit-btn <?php echo ($batch['batch_status'] === 'Completed') ? 'disabled' : ''; ?>" 
                                           title="<?php echo ($batch['batch_status'] === 'Completed') ? 'Cannot edit completed batch' : 'Edit'; ?>"
                                           <?php echo ($batch['batch_status'] === 'Completed') ? 'onclick="return false;"' : ''; ?>>
                                            <i class="fas fa-edit"></i> 
                                        </a>
                                        <button class="action-btn delete-btn" onclick="deleteBatch(<?php echo $batch['batch_id']; ?>)" title="Delete">
                                            <i class="fas fa-trash"></i> 
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="batch-info">
                <?php if (isset($currentSchedule) && $currentSchedule): ?>
                    <?php
                    $remaining_batches = $currentSchedule['schedule_batchNum'] - $currentSchedule['assigned_batches'];
                    ?>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Total Batches Required:</label>
                            <span><?php echo $currentSchedule['schedule_batchNum']; ?></span>
                        </div>
                        <div class="info-item">
                            <label>Assigned Batches:</label>
                            <span><?php echo $currentSchedule['assigned_batches']; ?></span>
                        </div>
                        <div class="info-item">
                            <label>Completed Batches:</label>
                            <span><?php echo $currentSchedule['completed_batches']; ?></span>
                        </div>
                        <div class="info-item">
                            <label>Unassigned Batches:</label>
                            <span><?php echo $remaining_batches; ?></span>
                        </div>
                    </div>
                <?php elseif ($recipe_filter): ?>
                    <div class="info-message">
                        No active production schedule found for this recipe.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ingredients Modal -->
        <div id="ingredients-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Quality Check</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="ingredients-list"></div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
    <script src="js/batch.js"></script>
    <script>
        function viewQc(batchId) {
            console.log(`Fetching QC data for batch ID: ${batchId}`);

            fetch(`get_qc.php?batch_id=${batchId}`)
                .then(response => {
                    console.log('API Response:', response);
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('QC Data:', data);

                    const modal = document.getElementById('ingredients-modal');
                    const qcList = modal.querySelector('#ingredients-list'); // Corrected the selector

                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    let html = `
                        <table>
                            <thead>
                                <tr>
                                    <th>Production Stage</th>
                                    <th>Appearance</th>
                                    <th>Texture</th>
                                    <th>Taste & Flavour</th>
                                    <th>Shape & Size</th>
                                    <th>Packaging</th>  
                                    <th>Remarks</th>                                  
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    data.forEach(qc => {
                        html += `
                            <tr>
                                <td>${qc.production_stage}</td>
                                <td>${qc.appearance}</td>
                                <td>${qc.texture}</td>
                                <td>${qc.taste_flavour}</td>
                                <td>${qc.shape_size}</td>
                                <td>${qc.packaging}</td>
                                <td>${qc.qc_comments}</td>
                                <td>${new Date(qc.created_at).toLocaleString()}</td>
                            </tr>
                        `;
                    });

                    html += '</tbody></table>';
                    qcList.innerHTML = html;
                    modal.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching QC data:', error);
                    alert('Error loading quality checks');
                });
            console.log("viewQC function loaded");
        }
    </script>
</body>
</html> 