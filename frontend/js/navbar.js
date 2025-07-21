/**
 * Navbar functionality for Sistema de Controlo de Barreiras IoT
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize navbar
    initNavbar();

    // Initialize logout button
    initLogoutButton();

    // Set user name from localStorage or use default
    const userName = localStorage.getItem('userName') || 'Admin';
    document.getElementById('user-name').textContent = userName;

    // Update data-user attribute for mobile view
    const navbarMenu = document.getElementById('navbar-menu');
    if (navbarMenu) {
        navbarMenu.setAttribute('data-user', userName);
    }
});

/**
 * Initialize navbar functionality
 */
function initNavbar() {
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMenu = document.getElementById('navbar-menu');
    const navbarItems = document.querySelectorAll('.navbar-item');

    // Get hash from URL or use default
    const hash = window.location.hash || '#dashboard';
    const sectionId = hash.substring(1);

    // Activate correct nav item
    navbarItems.forEach(item => {
        if (item.getAttribute('href') === hash) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    // Show correct section
    showSection(sectionId);

    // Toggle mobile menu
    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
        });
    }

    // Handle navbar item clicks
    navbarItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Remove active class from all items
            navbarItems.forEach(i => i.classList.remove('active'));

            // Add active class to clicked item
            this.classList.add('active');

            // Hide mobile menu after click
            if (navbarMenu) {
                navbarMenu.classList.remove('active');
            }

            // Get target section id
            const targetId = this.getAttribute('href').substring(1);

            // Show/hide sections based on selection
            showSection(targetId);

            // Prevent default anchor behavior
            e.preventDefault();
        });
    });
}

/**
 * Show selected section and hide others
 */
function showSection(sectionId) {
    // Define section mappings
    const sections = {
        'dashboard': document.querySelector('.dashboard-section'),
        'macs': document.querySelector('.macs-section'),
        'config': document.querySelector('.config-section'),
        'metrics': document.querySelector('.metrics-section')
    };

    // Hide all sections
    Object.values(sections).forEach(section => {
        if (section) {
            section.style.display = 'none';
        }
    });

    // Show selected section
    if (sections[sectionId]) {
        sections[sectionId].style.display = 'block';
    }
}

/**
 * Initialize logout button functionality
 */
function initLogoutButton() {
    const logoutBtn = document.getElementById('logout-btn');
    const mobileLogoutBtn = document.getElementById('mobile-logout-btn');

    // Function to handle logout
    const handleLogout = function() {
        // Simulate logout action
        console.log('Logout clicked');

        // Show confirmation dialog
        if (confirm('Tem a certeza que pretende sair?')) {
            // Redirect to login page or perform logout action
            window.location.href = 'login.html';
        }
    };

    // Add event listener to desktop logout button
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }

    // Add event listener to mobile logout button
    if (mobileLogoutBtn) {
        mobileLogoutBtn.addEventListener('click', handleLogout);
    }
}
