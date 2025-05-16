<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!-- Top Bar -->
<nav class="top-bar">
    <div class="top-bar-left">
        <button id="menu-toggle" class="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <img src="assets/images/logo_name_w.png" alt="Roti Seri Logo" style="height: 30px;">
        <span class="company-name">RotiSeri</span>
        <span class="company-name2">Staff</span>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <!-- Add user menu to top of sidebar -->
    <div class="sidebar-user">
        <div class="user-info">
            <span><?php echo htmlspecialchars($_SESSION['user_fullName']); ?></span>
            <button id="user-dropdown-toggle">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="user-dropdown" id="user-dropdown">
            <a href="manage_account.php">
                <i class="fas fa-user-cog"></i> Manage Account
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <!-- Show Baker Info only for Admins -->
        <?php if ($_SESSION['user_role'] === 'Admin'): ?>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'baker_info.php' ? 'active' : ''; ?>">
                <a href="baker_info.php"><i class="fas fa-users"></i> Baker Info</a>
            </li>
        <?php endif; ?>
        <li class="has-submenu <?php echo in_array(basename($_SERVER['PHP_SELF']), ['view_recipes.php', 'add_recipe.php']) ? 'active' : ''; ?>">
            <a href="#"><i class="fas fa-book"></i> Recipe Management</a>
            <ul class="submenu">
                <li><a href="view_recipes.php">View Details</a></li>
                <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                    <li><a href="add_recipe.php">Add New</a></li>
                <?php endif; ?>
            </ul>
        </li>
        <li class="has-submenu <?php echo in_array(basename($_SERVER['PHP_SELF']), ['view_schedules.php', 'add_schedule.php']) ? 'active' : ''; ?>">
            <a href="#"><i class="fas fa-calendar-alt"></i> Production Scheduling</a>
            <ul class="submenu">
                <li><a href="view_schedules.php">View Details</a></li>
                <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                    <li><a href="add_schedule.php">Add New</a></li>
                <?php endif; ?>
            </ul>
        </li>
        <li class="has-submenu <?php echo in_array(basename($_SERVER['PHP_SELF']), ['view_batches.php', 'add_batch.php']) ? 'active' : ''; ?>">
            <a href="#"><i class="fas fa-boxes"></i> Batch Tracking</a>
            <ul class="submenu">
                <li><a href="view_batches.php">View Details</a></li>
                <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                    <li><a href="add_batch.php">Add New</a></li>
                <?php endif; ?>
            </ul>
        </li>

    </ul>
</div>

<!-- Bottom Bar -->
<div class="bottombar">
    <p>&copy; 2024 YSLProduction | Production System</p>
</div>