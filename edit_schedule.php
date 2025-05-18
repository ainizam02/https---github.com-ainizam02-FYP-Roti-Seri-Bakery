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

// Check if schedule ID is provided
if (!isset($_GET['id'])) {
    header("Location: view_schedules.php");
    exit();
}

$schedule_id = $_GET['id'];

// Handle AJAX requests for user availability
if (isset($_GET['date'])) {
    $selected_date = $_GET['date'];
    $current_schedule_id = $_GET['schedule_id'] ?? 0;

    // First, get user availability
    $stmt = $conn->prepare("
        SELECT u.user_id, u.user_fullName, u.user_role,
               CASE 
                   WHEN EXISTS (
                       SELECT 1 
                       FROM tbl_schedule_assignments sa 
                       JOIN tbl_schedule s ON sa.schedule_id = s.schedule_id
                       WHERE sa.user_id = u.user_id 
                       AND s.schedule_date = ?
                       AND sa.schedule_id != ?
                   ) THEN 'Unavailable'
                   ELSE 'Available'
               END AS availability_status,
               COUNT(DISTINCT sa.schedule_id) as assignment_count
        FROM tbl_users u
        LEFT JOIN tbl_schedule_assignments sa ON u.user_id = sa.user_id
        LEFT JOIN tbl_schedule s ON sa.schedule_id = s.schedule_id 
            AND s.schedule_date = ?
        WHERE u.user_role IN ('Baker', 'Supervisor')
        GROUP BY u.user_id, u.user_fullName, u.user_role
        ORDER BY u.user_role, u.user_fullName
    ");
    $stmt->execute([$selected_date, $current_schedule_id, $selected_date]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get equipment availability
    $stmt = $conn->prepare("
        SELECT 
            e.equipment_id,
            e.equipment_name,
            CASE 
                WHEN e.equipment_status = 'Out of Order' THEN 'Out of Order'
                WHEN EXISTS (
                    SELECT 1 
                    FROM tbl_schedule_equipment se
                    JOIN tbl_schedule s ON se.schedule_id = s.schedule_id
                    WHERE se.equipment_id = e.equipment_id 
                    AND s.schedule_date = ?
                    AND se.schedule_id != ?
                ) THEN 'In Use'
                ELSE 'Available'
            END as availability_status
        FROM tbl_equipments e
        ORDER BY e.equipment_name
    ");
    $stmt->execute([$selected_date, $current_schedule_id]);
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the response
    $response = [
        'users' => array_map(function ($user) {
            return [
                'user_id' => $user['user_id'],
                'user_fullName' => $user['user_fullName'],
                'user_role' => $user['user_role'],
                'availability_status' => $user['availability_status'],
                'assignment_count' => (int)$user['assignment_count']
            ];
        }, $users),
        'equipment' => array_map(function ($item) {
            return [
                'equipment_id' => $item['equipment_id'],
                'equipment_name' => $item['equipment_name'],
                'availability_status' => $item['availability_status']
            ];
        }, $equipment)
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

try {
    // Get schedule details including completed batches count
    $stmt = $conn->prepare("SELECT s.*, 
                           (SELECT COUNT(*) FROM tbl_batches WHERE schedule_id = s.schedule_id) as assigned_batches,
                           (SELECT COUNT(*) FROM tbl_batches WHERE schedule_id = s.schedule_id AND batch_status = 'Completed') as completed_batches
                           FROM tbl_schedule s 
                           WHERE s.schedule_id = ?");
    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        header("Location: view_schedules.php");
        exit();
    }

    // Add this near the start of the file, after fetching the schedule
    if ($schedule['schedule_status'] === 'Completed') {
        $_SESSION['error'] = "Completed schedules cannot be edited.";
        header("Location: view_schedules.php");
        exit();
    }

    // Get all recipes
    $stmt = $conn->query("SELECT recipe_id, recipe_name, recipe_batchSize FROM tbl_recipe ORDER BY recipe_name");
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all users (bakers and supervisors)
    $stmt = $conn->query("SELECT user_id, user_fullName, user_role FROM tbl_users 
                         WHERE user_role IN ('Baker', 'Supervisor') 
                         ORDER BY user_role, user_fullName");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get assigned users
    $stmt = $conn->prepare("SELECT user_id FROM tbl_schedule_assignments WHERE schedule_id = ?");
    $stmt->execute([$schedule_id]);
    $assigned_users = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get equipment
    $stmt = $conn->prepare("SELECT equipment_id, equipment_name, equipment_status 
                         FROM tbl_equipments 
                         ORDER BY equipment_name");
    $stmt->execute();
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get currently assigned equipment
    $stmt = $conn->prepare("SELECT equipment_id FROM tbl_schedule_equipment WHERE schedule_id = ?");
    $stmt->execute([$schedule_id]);
    $assigned_equipment = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            $conn->beginTransaction(); // Start transaction here

            // Get schedule details
            $recipe_id = $_POST['recipe_id'];
            $schedule_date = $_POST['schedule_date'];
            $quantity = floatval($_POST['quantity']);
            $status = $_POST['status'];
            $assigned_users_new = $_POST['assigned_users'] ?? [];

            // Update schedule
            $stmt = $conn->prepare("UPDATE tbl_schedule SET 
                                  recipe_id = ?, 
                                  schedule_date = ?, 
                                  schedule_quantityToProduce = ?,
                                  schedule_status = ?,
                                  schedule_orderVolumn = ?,
                                  schedule_batchNum = ?
                                  WHERE schedule_id = ?");
            $stmt->execute([
                $recipe_id,
                $schedule_date,
                $quantity,
                $status,
                $_POST['schedule_orderVolumn'],
                $_POST['schedule_batchNum'],
                $schedule_id
            ]);

            // Delete existing assignments
            $stmt = $conn->prepare("DELETE FROM tbl_schedule_assignments WHERE schedule_id = ?");
            $stmt->execute([$schedule_id]);

            // Insert new assignments
            if (!empty($assigned_users_new)) {
                $stmt = $conn->prepare("INSERT INTO tbl_schedule_assignments (schedule_id, user_id) VALUES (?, ?)");
                foreach ($assigned_users_new as $user_id) {
                    $stmt->execute([$schedule_id, $user_id]);
                }
            }

            // Update equipment assignments
            $new_equipment = $_POST['equipment'] ?? [];

            // First, set previously assigned equipment back to Available
            $stmt = $conn->prepare("UPDATE tbl_equipments SET equipment_status = 'Available' 
                                  WHERE equipment_id IN (
                                      SELECT equipment_id FROM tbl_schedule_equipment 
                                      WHERE schedule_id = ?
                                  )");
            $stmt->execute([$schedule_id]);

            // Delete existing equipment assignments
            $stmt = $conn->prepare("DELETE FROM tbl_schedule_equipment WHERE schedule_id = ?");
            $stmt->execute([$schedule_id]);

            // Insert new equipment assignments
            if (!empty($new_equipment)) {
                $stmt = $conn->prepare("INSERT INTO tbl_schedule_equipment (schedule_id, equipment_id) VALUES (?, ?)");
                foreach ($new_equipment as $equipment_id) {
                    $stmt->execute([$schedule_id, $equipment_id]);
                }

                // Update new equipment status to 'In Use'
                $stmt = $conn->prepare("UPDATE tbl_equipments SET equipment_status = 'In Use' WHERE equipment_id = ?");
                foreach ($new_equipment as $equipment_id) {
                    $stmt->execute([$equipment_id]);
                }
            }

            $conn->commit();
            $success_message = "Schedule updated successfully!";

            // Refresh schedule data
            $stmt = $conn->prepare("SELECT * FROM tbl_schedule WHERE schedule_id = ?");
            $stmt->execute([$schedule_id]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

            // Refresh assigned users
            $stmt = $conn->prepare("SELECT user_id FROM tbl_schedule_assignments WHERE schedule_id = ?");
            $stmt->execute([$schedule_id]);
            $assigned_users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule - Roti Seri Production</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/schedule.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <?php include 'includes/dashboard_navigation.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Edit Schedule</h1>
            <div class="divider"></div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" class="schedule-form <?php echo $schedule['schedule_status'] === 'Completed' ? 'completed' : ''; ?>">
            <div class="form-section">
                <h2>Schedule Details</h2>

                <div class="form-group">
                    <label for="recipe_id">Recipe</label>
                    <select id="recipe_id" name="recipe_id" required>
                        <option value="">Select Recipe</option>
                        <?php foreach ($recipes as $recipe): ?>
                            <option value="<?php echo $recipe['recipe_id']; ?>"
                                data-batch-size="<?php echo $recipe['recipe_batchSize']; ?>"
                                <?php echo $schedule['recipe_id'] == $recipe['recipe_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($recipe['recipe_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="schedule_date">Production Date</label>
                    <input type="date" id="schedule_date" name="schedule_date"
                        value="<?php echo $schedule['schedule_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="schedule_orderVolumn">Order Volume (units)</label>
                    <div class="input-with-button">
                        <input type="number"
                            id="schedule_orderVolumn"
                            name="schedule_orderVolumn"
                            value="<?php echo htmlspecialchars($schedule['schedule_orderVolumn']); ?>"
                            required
                            min="1">
                        <button type="button" id="calculateBtn" class="calculate-btn">
                            <i class="fas fa-calculator"></i> Calculate
                        </button>
                    </div>
                    <small id="calculation-info" class="calculation-info"></small>
                </div>

                <div class="form-group">
                    <label for="schedule_batchNum">Number of Batch:</label>
                    <input type="number"
                        id="schedule_batchNum"
                        name="schedule_batchNum"
                        class="form-control"
                        value="<?php echo htmlspecialchars($schedule['schedule_batchNum']); ?>"
                        min="1"
                        readonly>
                    <small id="batch-calculation" class="calculation-info"></small>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity to Produce</label>
                    <input type="number" id="quantity" name="quantity"
                        step="0.01" min="0.01" required readonly
                        value="<?php echo $schedule['schedule_quantityToProduce']; ?>">
                    <small id="quantity-calculation" class="calculation-info"></small>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Pending" <?php echo $schedule['schedule_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo $schedule['schedule_status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo $schedule['schedule_status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h2>Assign Users</h2>
                <div class="user-selection">
                    <?php foreach ($users as $user): ?>
                        <div class="user-checkbox">
                            <input type="checkbox"
                                name="assigned_users[]"
                                value="<?php echo $user['user_id']; ?>"
                                <?php echo in_array($user['user_id'], $assigned_users) ? 'checked' : ''; ?>>
                            <span><?php echo htmlspecialchars($user['user_fullName']); ?> 
                                (<?php echo $user['user_role']; ?>)</span>
                            <span class="status <?php echo in_array($user['user_id'], $assigned_users) ? 'unavailable' : 'available'; ?>">
                                <?php echo in_array($user['user_id'], $assigned_users) ? 'Unavailable' : 'Available'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-section">
                <h2>Equipment Selection</h2>
                <div class="equipment-selection">
                    <?php foreach ($equipment as $item): ?>
                        <div class="equipment-checkbox">
                            <input type="checkbox"
                                name="equipment[]"
                                value="<?php echo $item['equipment_id']; ?>"
                                <?php echo in_array($item['equipment_id'], $assigned_equipment) ? 'checked' : ''; ?>>
                            <span><?php echo htmlspecialchars($item['equipment_name']); ?></span>
                            <span class="status <?php echo in_array($item['equipment_id'], $assigned_equipment) ? 'in-use' : 'available'; ?>">
                                <?php echo in_array($item['equipment_id'], $assigned_equipment) ? 'In Use' : 'Available'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Update Schedule</button>
                <a href="view_schedules.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </main>

    <script src="js/dashboard.js"></script>
    <script>
        function calculateBatchAndQuantity() {
            const recipeSelect = document.getElementById('recipe_id');
            const orderVolume = document.getElementById('schedule_orderVolumn').value;
            const selectedOption = recipeSelect.options[recipeSelect.selectedIndex];

            // Clear previous calculation info
            document.getElementById('calculation-info').innerHTML = '';
            document.getElementById('batch-calculation').innerHTML = '';
            document.getElementById('quantity-calculation').innerHTML = '';

            if (orderVolume && selectedOption.value) {
                const batchSize = parseFloat(selectedOption.getAttribute('data-batch-size'));
                const recipeName = selectedOption.text;

                // Calculate number of batches (rounded up)
                const rawBatches = orderVolume / batchSize;
                const numBatches = Math.ceil(rawBatches);

                // Calculate actual quantity to produce
                const quantity = numBatches * batchSize;

                // Update the form fields
                document.getElementById('schedule_batchNum').value = numBatches;
                document.getElementById('quantity').value = quantity;

                // Show calculation details
                document.getElementById('calculation-info').innerHTML =
                    `Selected recipe: ${recipeName} (Batch size: ${batchSize} units)`;

                document.getElementById('batch-calculation').innerHTML =
                    `${orderVolume} units ÷ ${batchSize} units per batch = ${rawBatches.toFixed(2)} → Rounded up to ${numBatches} batches`;

                document.getElementById('quantity-calculation').innerHTML =
                    `${numBatches} batches × ${batchSize} units per batch = ${quantity} units total`;
            } else {
                // Clear the fields if no recipe is selected or no order volume entered
                document.getElementById('schedule_batchNum').value = '';
                document.getElementById('quantity').value = '';
            }
        }

        // Add event listeners
        document.getElementById('calculateBtn').addEventListener('click', function(e) {
            e.preventDefault(); // Prevent form submission
            calculateBatchAndQuantity();
        });

        // Also calculate when Enter is pressed in the order volume field
        document.getElementById('schedule_orderVolumn').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent form submission
                calculateBatchAndQuantity();
            }
        });

        // Calculate on page load if values exist
        if (document.getElementById('schedule_orderVolumn').value) {
            calculateBatchAndQuantity();
        }

        function checkAvailability() {
            const dateInput = document.getElementById('schedule_date');
            const selectedDate = dateInput.value;
            const scheduleId = <?php echo $schedule_id; ?>;

            if (!selectedDate) return;

            fetch(`edit_schedule.php?date=${selectedDate}&schedule_id=${scheduleId}`)
                .then(response => response.json())
                .then(data => {
                    // Handle user availability
                    const userCheckboxes = document.querySelectorAll('.user-checkbox');
                    userCheckboxes.forEach(checkbox => {
                        const userId = checkbox.querySelector('input').value;
                        const user = data.users.find(u => u.user_id === userId);
                        const statusSpan = checkbox.querySelector('.status');
                        
                        if (user) {
                            let statusText = user.availability_status;
                            if (user.assignment_count > 0) {
                                statusText += ` (${user.assignment_count} schedule${user.assignment_count > 1 ? 's' : ''})`;
                            }
                            statusSpan.textContent = statusText;
                            statusSpan.className = `status ${user.availability_status.toLowerCase()}`;
                            
                            // Disable checkbox if user is unavailable
                            const checkbox_input = checkbox.querySelector('input[type="checkbox"]');
                            if (!checkbox_input.checked) {
                                checkbox_input.disabled = user.availability_status === 'Unavailable';
                            }
                        }
                    });

                    // Handle equipment availability
                    const equipmentCheckboxes = document.querySelectorAll('.equipment-checkbox');
                    equipmentCheckboxes.forEach(checkbox => {
                        const equipmentId = checkbox.querySelector('input').value;
                        const equipment = data.equipment.find(e => e.equipment_id === equipmentId);
                        const statusSpan = checkbox.querySelector('.status');
                        
                        if (equipment) {
                            statusSpan.textContent = equipment.availability_status;
                            statusSpan.className = `status ${equipment.availability_status.toLowerCase().replace(' ', '-')}`;
                            
                            // Disable checkbox if equipment is unavailable or in use
                            const checkbox_input = checkbox.querySelector('input[type="checkbox"]');
                            if (!checkbox_input.checked) {
                                checkbox_input.disabled = equipment.availability_status === 'Out of Order' || 
                                                        equipment.availability_status === 'In Use';
                            }
                        }
                    });
                })
                .catch(error => console.error('Error checking availability:', error));
        }

        // Add event listener for date changes
        document.getElementById('schedule_date').addEventListener('change', checkAvailability);

        // Check availability on page load if date is already selected
        if (document.getElementById('schedule_date').value) {
            checkAvailability();
        }

        document.querySelector('form.schedule-form').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            const currentStatus = '<?php echo $schedule['schedule_status']; ?>';
            const newStatus = document.getElementById('status').value;
            
            // If schedule is already completed, prevent any changes
            if (currentStatus === 'Completed') {
                alert('This schedule is already completed and cannot be modified.');
                return false;
            }
            
            // If the new status is Completed, show confirmation dialog
            if (newStatus === 'Completed') {
                const confirmed = confirm(
                    "Are you sure you want to mark this schedule as Completed?\n\n" +
                    "Warning: Once completed, this schedule cannot be edited again."
                );
                
                if (!confirmed) {
                    return false;
                }
            }
            
            // If confirmed or not changing to Completed, submit the form
            this.submit();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const totalBatches = <?php echo $schedule['schedule_batchNum']; ?>;
            const completedBatches = <?php echo $schedule['completed_batches']; ?>;

            statusSelect.addEventListener('change', function() {
                if (this.value === 'Completed' && completedBatches < totalBatches) {
                    alert(`Cannot mark schedule as completed. Only ${completedBatches} out of ${totalBatches} batches are completed.`);
                    // Reset to previous value
                    this.value = '<?php echo $schedule['schedule_status']; ?>';
                }
            });
        });
    </script>
</body>

</html>