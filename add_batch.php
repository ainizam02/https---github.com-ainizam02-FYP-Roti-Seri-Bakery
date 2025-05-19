<?php
session_start();
require_once 'config/db_connection.php';

// Add these constants at the top of the file after session_start()
define('MAX_REMARKS_LENGTH', 500);
define('MAX_QUALITY_CHECK_LENGTH', 500);
define('ALLOWED_TASKS', ['Mixing', 'Baking', 'Decorating']);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_message = '';
$error_message = '';

try {
    // Get recipes that have schedules
    $stmt = $conn->query("SELECT DISTINCT r.recipe_id, r.recipe_name 
                         FROM tbl_recipe r
                         INNER JOIN tbl_schedule s ON r.recipe_id = s.recipe_id
                         WHERE s.schedule_status != 'Completed'
                         ORDER BY r.recipe_name");
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get schedules
    $stmt = $conn->query("SELECT s.schedule_id, r.recipe_id, r.recipe_name, s.schedule_date,
                         s.schedule_batchNum,
                         (SELECT COUNT(*) FROM tbl_batches WHERE schedule_id = s.schedule_id) as assigned_batches,
                         (SELECT COUNT(*) FROM tbl_batches WHERE schedule_id = s.schedule_id AND batch_status = 'Completed') as completed_batches
                         FROM tbl_schedule s 
                         JOIN tbl_recipe r ON s.recipe_id = r.recipe_id 
                         WHERE s.schedule_status != 'Completed'
                         ORDER BY s.schedule_date DESC");
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get bakers
    $stmt = $conn->query("SELECT user_id, user_fullName FROM tbl_users 
                         WHERE user_role = 'Baker' 
                         ORDER BY user_fullName");
    $bakers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            // Verify CSRF token
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                throw new Exception('Invalid CSRF token');
            }

            $conn->beginTransaction();

            // Validate recipe_id
            $recipe_id = filter_input(INPUT_POST, 'recipe_id', FILTER_VALIDATE_INT);
            if ($recipe_id === false || $recipe_id <= 0) {
                throw new Exception("Invalid recipe ID");
            }
            
            $stmt = $conn->prepare("SELECT recipe_id FROM tbl_recipe WHERE recipe_id = ?");
            $stmt->execute([$recipe_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Invalid recipe selected");
            }

            // Validate schedule_id
            $schedule_id = filter_input(INPUT_POST, 'schedule_id', FILTER_VALIDATE_INT);
            if ($schedule_id === false || $schedule_id <= 0) {
                throw new Exception("Invalid schedule ID");
            }
            
            // Verify schedule exists and has capacity
            $stmt = $conn->prepare("SELECT s.schedule_id, s.schedule_batchNum, 
                                   (SELECT COUNT(*) FROM tbl_batches WHERE schedule_id = s.schedule_id) as assigned_batches
                                   FROM tbl_schedule s WHERE s.schedule_id = ?");
            $stmt->execute([$schedule_id]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$schedule) {
                throw new Exception("Invalid schedule selected");
            }
            
            // Check if schedule has reached its batch limit
            if ($schedule['assigned_batches'] >= $schedule['schedule_batchNum']) {
                throw new Exception("This schedule has reached its maximum number of batches");
            }

            // Validate datetime format and logic
            $start_time = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['start_time']);
            $end_time = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['end_time']);
            
            if (!$start_time || !$end_time) {
                throw new Exception("Invalid date/time format");
            }

            // Ensure end time is after start time
            if ($end_time <= $start_time) {
                throw new Exception("End time must be after start time");
            }

            // Ensure times are not in the past
            $now = new DateTime();
            if ($start_time < $now) {
                throw new Exception("Start time cannot be in the past");
            }

            // Convert to string format for database
            $start_time = $start_time->format('Y-m-d H:i:s');
            $end_time = $end_time->format('Y-m-d H:i:s');

            // Validate and sanitize text inputs
            $remarks = trim(filter_var($_POST['remarks'], FILTER_SANITIZE_STRING));

            // Check length limits
            if (strlen($remarks) > MAX_REMARKS_LENGTH) {
                throw new Exception("Remarks exceed maximum length of " . MAX_REMARKS_LENGTH . " characters");
            }

            // Validate assignments array
            $assignments = isset($_POST['assignments']) ? $_POST['assignments'] : [];
            if (empty($assignments)) {
                throw new Exception("At least one task assignment is required");
            }
            if (count($assignments) > 10) { // Set a reasonable maximum number of assignments
                throw new Exception("Too many task assignments");
            }

            $validated_assignments = [];
            foreach ($assignments as $assignment) {
                // Validate user_id
                $user_id = filter_var($assignment['user_id'], FILTER_VALIDATE_INT);
                if ($user_id === false || $user_id <= 0) {
                    throw new Exception("Invalid baker ID");
                }

                $stmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE user_id = ? AND user_role = 'Baker'");
                $stmt->execute([$user_id]);
                if (!$stmt->fetch()) {
                    throw new Exception("Invalid baker selected");
                }

                // Validate task
                $task = trim(filter_var($assignment['task'], FILTER_SANITIZE_STRING));
                if (!in_array($task, ALLOWED_TASKS)) {
                    throw new Exception("Invalid task selected");
                }

                $validated_assignments[] = [
                    'user_id' => $user_id,
                    'task' => $task
                ];
            }

            // Insert batch
            $stmt = $conn->prepare("INSERT INTO tbl_batches (recipe_id, schedule_id, batch_startTime, 
                                                          batch_endTime, batch_remarks) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$recipe_id, $schedule_id, $start_time, $end_time, $remarks]);
            
            $batch_id = $conn->lastInsertId();

            // Insert task assignments
            if (!empty($validated_assignments)) {
                $stmt = $conn->prepare("INSERT INTO tbl_batch_assignments 
                                      (batch_id, user_id, ba_task, ba_status) 
                                      VALUES (?, ?, ?, 'Pending')");
                
                foreach ($validated_assignments as $assignment) {
                    $stmt->execute([
                        $batch_id,
                        $assignment['user_id'],
                        $assignment['task']
                    ]);
                }
            }

            $conn->commit();
            $success_message = "Batch created successfully!";

        } catch(Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            $error_message = "Error: " . $e->getMessage();
        }
    }
} catch(PDOException $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Batch - Roti Seri Production</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/batch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/dashboard_navigation.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Add New Batch</h1>
            <div class="divider"></div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" class="batch-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-section">
                <h2>Batch Details</h2>
                
                <div class="form-group">
                    <label for="recipe_id">Recipe</label>
                    <select id="recipe_id" name="recipe_id" required>
                        <option value="">Select Recipe</option>
                        <?php foreach ($recipes as $recipe): ?>
                            <option value="<?php echo htmlspecialchars($recipe['recipe_id']); ?>">
                                <?php echo htmlspecialchars($recipe['recipe_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="schedule_id">Production Schedule</label>
                    <select id="schedule_id" name="schedule_id" required disabled>
                        <option value="">Select Recipe First</option>
                        <?php foreach ($schedules as $schedule): ?>
                            <?php 
                                $remaining_batches = $schedule['schedule_batchNum'] - $schedule['assigned_batches'];
                            ?>
                            <option value="<?php echo htmlspecialchars($schedule['schedule_id']); ?>" 
                                    data-recipe="<?php echo htmlspecialchars($schedule['recipe_id']); ?>"
                                    data-total="<?php echo htmlspecialchars($schedule['schedule_batchNum']); ?>"
                                    data-assigned="<?php echo htmlspecialchars($schedule['assigned_batches']); ?>"
                                    data-completed="<?php echo htmlspecialchars($schedule['completed_batches']); ?>"
                                    data-remaining="<?php echo htmlspecialchars($remaining_batches); ?>">
                                <?php echo htmlspecialchars($schedule['recipe_name'] . ' - ' . 
                                      date('M d, Y', strtotime($schedule['schedule_date']))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="batch-info" style="display: none;">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Total Batches Required:</label>
                            <span id="total-batches">-</span>
                        </div>
                        <div class="info-item">
                            <label>Assigned Batches:</label>
                            <span id="assigned-batches">-</span>
                        </div>
                        <div class="info-item">
                            <label>Completed Batches:</label>
                            <span id="completed-batches">-</span>
                        </div>
                        <div class="info-item">
                            <label>Unassigned Batches:</label>
                            <span id="remaining-batches">-</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="datetime-local" id="start_time" name="start_time" required>
                    </div>

                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="datetime-local" id="end_time" name="end_time" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" rows="3"></textarea>
                </div>
            </div>

            <div class="form-section">
                <h2>Task Assignments</h2>
                <div id="task-assignments">
                    <div class="task-assignment">
                        <div class="form-group">
                            <label>Baker</label>
                            <select name="assignments[0][user_id]" required>
                                <option value="">Select Baker</option>
                                <?php foreach ($bakers as $baker): ?>
                                    <option value="<?php echo htmlspecialchars($baker['user_id']); ?>">
                                        <?php echo htmlspecialchars($baker['user_fullName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Task</label>
                            <select name="assignments[0][task]" required>
                                <option value="">Select Task</option>
                                <option value="Mixing">Mixing</option>
                                <option value="Baking">Baking</option>
                                <option value="Decorating">Decorating</option>
                            </select>
                        </div>
                        <button type="button" class="remove-task" onclick="removeTask(this)" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <button type="button" class="add-task-btn" onclick="addTask()">
                    <i class="fas fa-plus"></i> Add Another Task
                </button>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Create Batch</button>
                <a href="view_batches.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </main>

    <script src="js/dashboard.js"></script>
    <script src="js/batch.js"></script>
</body>
</html> 