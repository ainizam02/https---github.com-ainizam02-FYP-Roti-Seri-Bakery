<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RotGotManager Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add dropdown functionality for all dropdown menus
            const dropdownMenus = document.querySelectorAll('.menu-item-dropdown');

            dropdownMenus.forEach(dropdown => {
                dropdown.addEventListener('click', function (e) {
                    // Only toggle if clicked on the header, not the dropdown items
                    if (!e.target.closest('.dropdown-item')) {
                        this.classList.toggle('active');
                    }
                });
            });

            // Prevent dropdown menu from closing when clicking on dropdown items
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    // You can add functionality here to handle item selection
                    console.log('Selected:', this.innerText);
                });
            });
        });
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            background-color: #f5f5f5;
        }

        /* Sidebar styles */
        .sidebar {
            width: 220px;
            background-color: white;
            height: 100vh;
            padding: 20px 15px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            position: fixed;
        }

        .logo-container {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 30px;
            height: 30px;
            background-color: #3d4b64;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }

        .logo-text {
            font-weight: bold;
            font-size: 16px;
        }

        /* Menu styles */
        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin: 5px 0;
            border-radius: 8px;
            cursor: pointer;
            color: #67748e;
            font-size: 14px;
        }

        .menu-item.active {
            background-color: #e9efff;
            color: #3361ff;
        }

        .menu-item.active a {
            color: #3361ff !important;
        }

        .menu-item i {
            margin-right: 10px;
            font-size: 18px;
        }

        .menu-text {
            margin-left: 10px;
        }

        /* Dropdown menu styles */
        .menu-item-dropdown {
            margin: 5px 0;
            border-radius: 8px;
            cursor: pointer;
            color: #67748e;
            font-size: 14px;
            overflow: hidden;
        }

        .menu-item-dropdown.active {
            background-color: #e9efff;
            color: #3361ff;
        }

        .menu-item-header {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            justify-content: space-between;
        }

        .dropdown-arrow {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .menu-item-dropdown.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }

        .menu-item-dropdown.active .dropdown-menu {
            max-height: 150px;
        }

        .dropdown-item {
            padding: 10px 15px 10px 40px;
            font-size: 13px;
            color: #67748e;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        /* .dropdown-item:hover {
            background-color: #e9efff;
            color: #3361ff;
        } */

        /* Main content styles */
        .main-content {
            flex: 1;
            margin-left: 220px;
            padding: 20px 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .page-subtitle {
            font-size: 12px;
            color: #67748e;
            margin-top: 5px;
        }

        .date {
            color: #67748e;
            font-size: 14px;
        }

        /* Stats cards section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }

        .stat-card:nth-child(1)::before {
            background-color: #3361ff;
        }

        .stat-card:nth-child(2)::before {
            background-color: #4cd964;
        }

        .stat-card:nth-child(3)::before {
            background-color: #5ac8fa;
        }

        .stat-card:nth-child(4)::before {
            background-color: #ffcc00;
        }

        .stat-title {
            font-size: 12px;
            color: #67748e;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 12px;
            color: #67748e;
        }

        .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Counter cards section */
        .counter-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .counter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .counter-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }

        .counter-card:nth-child(1)::before {
            background-color: #3361ff;
        }

        .counter-card:nth-child(2)::before {
            background-color: #ffcc00;
        }

        .counter-card:nth-child(3)::before {
            background-color: #ff3b30;
        }

        .counter-title {
            font-size: 12px;
            color: #67748e;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .counter-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .view-all {
            font-size: 12px;
            color: #3361ff;
            cursor: pointer;
            text-decoration: none;
        }

        /* Dashboard bottom section */
        .dashboard-bottom {
            display: grid;
            grid-template-columns: 65% 35%;
            gap: 20px;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 16px;
            font-weight: bold;
        }

        .date-picker {
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 8px 12px;
            font-size: 13px;
            color: #333;
        }

        .date-picker i {
            margin-left: 10px;
            color: #3361ff;
        }

        .chart-area {
            height: 250px;
            width: 100%;
        }

        /* Hot selling items table */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 16px;
            font-weight: bold;
        }

        .details-btn {
            display: flex;
            align-items: center;
            font-size: 13px;
            color: #3361ff;
            border: 1px solid #3361ff;
            border-radius: 5px;
            padding: 6px 12px;
            cursor: pointer;
            background: transparent;
        }

        .details-btn i {
            margin-right: 5px;
        }

        .hot-items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .hot-items-table th {
            text-align: left;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
            color: #67748e;
            font-weight: normal;
            font-size: 14px;
        }

        .hot-items-table td {
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        /* User info */
        .user-info {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 180px;
            font-size: 12px;
            color: #67748e;
        }

        .upgrade-btn {
            background-color: #3361ff;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 6px 15px;
            margin-top: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        .icon-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }

        .blue-bg {
            background-color: #e6efff;
            color: #3361ff;
            border-radius: 6px;
        }

        .green-bg {
            background-color: #e6fff0;
            color: #4cd964;
            border-radius: 6px;
        }

        .light-blue-bg {
            background-color: #e6f7ff;
            color: #5ac8fa;
            border-radius: 6px;
        }

        .yellow-bg {
            background-color: #fff9e6;
            color: #ffcc00;
            border-radius: 6px;
        }

        .red-bg {
            background-color: #ffe6e6;
            color: #ff3b30;
            border-radius: 6px;
        }

        .sidebar-toggle {
            position: absolute;
            top: 20px;
            right: -10px;
            width: 24px;
            height: 24px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 10;
            transition: transform 0.3s ease;
        }

        .sidebar-toggle svg {
            width: 14px;
            height: 14px;
            transition: transform 0.3s ease;
        }

        body.sidebar-collapsed .sidebar-toggle {
            transform: rotate(180deg);
        }

        .sidebar {
            transition: width 0.3s ease;
        }

        body.sidebar-collapsed .sidebar {
            width: 70px;
        }

        body.sidebar-collapsed .logo-container,
        body.sidebar-collapsed .logo-text,
        body.sidebar-collapsed .menu-text,
        body.sidebar-collapsed .dropdown-arrow,
        body.sidebar-collapsed .dropdown-menu,
        body.sidebar-collapsed .user-info {
            display: none;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 70px;
        }

        body.sidebar-collapsed .menu-item,
        body.sidebar-collapsed .menu-item-header {
            justify-content: center;
            padding: 12px 0;
        }

        body.sidebar-collapsed .icon-container {
            margin-right: 0;
        }

        /* Tooltip for collapsed sidebar */
        body.sidebar-collapsed .menu-item,
        body.sidebar-collapsed .menu-item-header {
            position: relative;
        }

        body.sidebar-collapsed .menu-item:hover::after,
        body.sidebar-collapsed .menu-item-header:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 70px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 100;
        }

        .company-name {
    font-size: 16px;
    font-weight: 500;
    color: #333;
  }
  
  .company-name2 {
    font-size: 16px;
    font-weight: 600;
    color: #3361ff;
  }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" color="grey">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </div>
        <div class="logo-container">
            <img src="assets/images/logo_name_w.png" alt="Roti Seri Logo" style="height: 30px;">
            <span class="company-name">RotiSeri</span>
            <span class="company-name2">
                <?php 
            echo ($_SESSION['user_role'] === 'Supervisor') ? 'Manager' : 'Staff';
        ?>
        </span>
        </div>

        <div class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"">
            <a href=" dashboard.php"
            style="display: flex; align-items: center; text-decoration: none; font-family: inherit; color: #67748e">
            <span class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </span>
            <span class="menu-text">Dashboard</span>
            </a>
        </div>

        <?php if ($_SESSION['user_role'] === 'Supervisor'): ?>
        <div class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'baker_info.php' ? 'active' : ''; ?>">
            <a href=" baker_info.php"
            style="display: flex; align-items: center; text-decoration: none; font-family: inherit; color: #67748e">
            <span class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </span>
            <span class="menu-text">Baker Info</span>
            </a>
        </div>
<?php endif; ?>

        <div
            class="menu-item-dropdown  <?php echo in_array(basename($_SERVER['PHP_SELF']), ['view_recipes.php', 'add_recipe.php']) ? 'active' : ''; ?>">
            <div class="menu-item-header">
                <span class="icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                        <path
                            d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z">
                        </path>
                    </svg>
                </span>
                <span class="menu-text">Recipe Management</span>
                <span class="dropdown-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </span>
            </div>
            <div class="dropdown-menu <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"">
                <a href=" view_recipes.php"
                style="display: flex; align-items: center; text-decoration: none; font-family: inherit;;">
                <div class="dropdown-item">View Details</div>
                </a>
                <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                    <a href="add_recipe.php"
                        style="display: flex; align-items: center; text-decoration: none; font-family: inherit;;">
                        <div class="dropdown-item">Add New</div>
                    </a>
                <?php endif; ?>
            </div>
        </div>


        <div
            class="menu-item-dropdown <?php echo in_array(basename($_SERVER['PHP_SELF']), ['view_schedules.php', 'add_schedule.php']) ? 'active' : ''; ?>">
            <div class="menu-item-header">
                <span class="icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                        <path
                            d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z">
                        </path>
                    </svg>
                </span>
                <span class="menu-text">Production Scheduling</span>
                <span class="dropdown-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </span>
            </div>
            <div class="dropdown-menu <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"">
                <a href=" view_schedules.php"
                style="display: flex; align-items: center; text-decoration: none; font-family: inherit;;">
                <div class="dropdown-item">View Details</div>
                </a>
                <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                    <a href="add_schedule.php"
                        style="display: flex; align-items: center; text-decoration: none; font-family: inherit;;">
                        <div class="dropdown-item">Add New</div>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div
            class="menu-item-dropdown <?php echo in_array(basename($_SERVER['PHP_SELF']), ['view_batches.php', 'add_batch.php']) ? 'active' : ''; ?>">
            <div class="menu-item-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                    <path
                        d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z">
                    </path>
                </svg>
                <span class="menu-text">Batch Tracking</span>
                <span class="dropdown-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </span>
            </div>
            <div class="dropdown-menu">
                <a href="view_batches.php"
                    style="display: flex; align-items: center; text-decoration: none; font-family: inherit;;">
                    <div class="dropdown-item">View Details</div>
                </a>
                <?php if ($_SESSION['user_role'] !== 'Baker'): ?>
                    <a href="add_batch.php"
                        style="display: flex; align-items: center; text-decoration: none; font-family: inherit;;">
                        <div class="dropdown-item">Add New</div>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="menu-item">
            <a href="logout.php"
                style="display: flex; align-items: center; text-decoration: none; font-family: inherit; color: #67748e">
                <span class="icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </span>
                <span class="menu-text">Log Out</span>
            </a>
        </div>

        <div class="user-info">
            <a href="manage_account.php"
                style="display: flex; align-items: center; text-decoration: none; font-family: inherit; color: #67748e">
                <div><?php echo htmlspecialchars($_SESSION['user_fullName']); ?></div>
            </a>
            <button class="upgrade-btn"><?php echo htmlspecialchars($_SESSION['user_role']); ?></button>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Existing dropdown code...
        
        // Add sidebar collapse functionality
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const body = document.body;
        
        // Add data-tooltip attributes to menu items
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            const menuText = item.querySelector('.menu-text').textContent;
            item.setAttribute('data-tooltip', menuText);
        });
        
        // Add data-tooltip attributes to dropdown headers
        const dropdownHeaders = document.querySelectorAll('.menu-item-header');
        dropdownHeaders.forEach(header => {
            const menuText = header.querySelector('.menu-text').textContent;
            header.setAttribute('data-tooltip', menuText);
        });
        
        sidebarToggle.addEventListener('click', function() {
            body.classList.toggle('sidebar-collapsed');
            
            // Save the state to localStorage
            const isCollapsed = body.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
        
        // Check localStorage for saved state
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            body.classList.add('sidebar-collapsed');
        }
    });
</script>