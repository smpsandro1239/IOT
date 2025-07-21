/**
 * Navbar functionality for Sistema de Controlo de Barreiras IoT
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize navbar
    initNavbar();
});

/**
 * Initialize navbar functionality
 */
function initNavbar() {
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMenu = document.getElementById('navbar-menu');
    const navbarItems = document.querySelectorAll('.navbar-item');

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
