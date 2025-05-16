document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const body = document.body;
    const userDropdownToggle = document.getElementById('user-dropdown-toggle');
    const userDropdown = document.getElementById('user-dropdown');

    // Sidebar toggle
    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        body.classList.toggle('sidebar-collapsed');
    });

    // User dropdown toggle
    userDropdownToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!userDropdown.contains(e.target) && !userDropdownToggle.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });

    // Submenu toggle
    const submenuItems = document.querySelectorAll('.has-submenu > a');
    submenuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            parent.classList.toggle('active');
        });
    });
}); 