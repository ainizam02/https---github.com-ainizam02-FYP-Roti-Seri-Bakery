document.addEventListener('DOMContentLoaded', function() {
    // Toggle submenu
    const submenuItems = document.querySelectorAll('.has-submenu > a');
    submenuItems.forEach(item => {
      item.addEventListener('click', function(e) {
        e.preventDefault();
        const parent = this.parentElement;
        
        // Close other open submenus
        const openMenus = document.querySelectorAll('.has-submenu.open');
        openMenus.forEach(menu => {
          if (menu !== parent) {
            menu.classList.remove('open');
          }
        });
        
        // Toggle current submenu
        parent.classList.toggle('open');
      });
    });
    
    // Toggle sidebar on mobile
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');
    
    if (menuToggle) {
      menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('expanded');
        
        // Adjust content margin when sidebar is toggled on mobile
        if (window.innerWidth <= 992) {
          if (sidebar.classList.contains('expanded')) {
            content.style.marginLeft = '220px';
          } else {
            content.style.marginLeft = '70px';
          }
        }
      });
    }
    
    // User dropdown toggle
    const userDropdownToggle = document.getElementById('user-dropdown-toggle');
    const userDropdown = document.getElementById('user-dropdown');
    
    if (userDropdownToggle && userDropdown) {
      userDropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        userDropdown.classList.toggle('show');
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!userDropdownToggle.contains(e.target) && !userDropdown.contains(e.target)) {
          userDropdown.classList.remove('show');
        }
      });
    }
    
    // Initialize active submenu to be open on page load
    const activeSubmenuParent = document.querySelector('.has-submenu.active');
    if (activeSubmenuParent) {
      activeSubmenuParent.classList.add('open');
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
      if (window.innerWidth > 992) {
        content.style.marginLeft = '220px';
      } else {
        content.style.marginLeft = sidebar.classList.contains('expanded') ? '220px' : '70px';
      }
    });
    
    // Initialize active state for main sidebar items
    const currentPath = window.location.pathname;
    const currentPage = currentPath.substring(currentPath.lastIndexOf('/') + 1);
    
    const menuItems = document.querySelectorAll('.sidebar-menu > li > a');
    menuItems.forEach(item => {
      const href = item.getAttribute('href');
      if (href && href === currentPage) {
        item.parentElement.classList.add('active');
      }
    });
  });