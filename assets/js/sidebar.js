$(document).ready(function () {
    // Sidebar toggle
    $('#sidebarCollapse').on('click', function () {
        $('.sidebar').toggleClass('active');
    });

    // Submenu handling
    $('.sidebar-dropdown > a').click(function(e) {
        e.preventDefault();
        
        // Close other submenus
        $('.sidebar .has-sub').not($(this).parent()).removeClass('active');
        $('.sidebar-submenu').not($(this).next()).slideUp(200);
        
        // Toggle current submenu
        $(this).parent().toggleClass('active');
        $(this).next('.sidebar-submenu').slideToggle(200);
    });

    // Highlight active menu item based on current page
    const currentPage = window.location.pathname.split('/').pop().split('.')[0];
    $(`.sidebar a[href*="${currentPage}"]`).addClass('active');
    
    // Open parent menu if child is active
    const activeLink = $('.sidebar a.active');
    if (activeLink.length) {
        activeLink.parents('.sidebar-dropdown').addClass('active');
        activeLink.parents('.sidebar-submenu').show();
    }
});