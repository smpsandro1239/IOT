/**
 * Responsive Layout Enhancements for Sistema de Controle de Barreiras IoT
 * Handles dynamic layout adjustments based on screen size
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize responsive layout
    initResponsiveLayout();

    // Listen for window resize events
    window.addEventListener('resize', debounce(function() {
        adjustLayoutForScreenSize();
    }, 250));

    // Initial layout adjustment
    adjustLayoutForScreenSize();
});

/**
 * Initialize responsive layout enhancements
 */
function initResponsiveLayout() {
    // Add layout classes to main containers
    const mainContainers = document.querySelectorAll('.rounded-lg.shadow-lg.p-6');
    mainContainers.forEach(container => {
        container.classList.add('card');
    });

    // Make radar container responsive
    const radarContainer = document.querySelector('.radar-scan');
    if (radarContainer) {
        // Add aspect ratio container for radar
        const parent = radarContainer.parentElement;
        const wrapper = document.createElement('div');
        wrapper.className = 'radar-container';
        parent.insertBefore(wrapper, radarContainer);
        wrapper.appendChild(radarContainer);
    }

    // Add responsive classes to charts
    const chartContainers = document.querySelectorAll('.chart-container');
    chartContainers.forEach(container => {
        container.classList.add('metrics-card');
    });

    // Add responsive classes to vehicle info
    const vehicleInfo = document.querySelector('.grid.grid-cols-2.md\\:grid-cols-4.gap-3');
    if (vehicleInfo) {
        vehicleInfo.classList.add('vehicle-info-compact');
    }
}

/**
 * Adjust layout based on current screen size
 */
function adjustLayoutForScreenSize() {
    const width = window.innerWidth;

    // Mobile layout adjustments
    if (width < 640) {
        // Make charts stack vertically
        const chartGrids = document.querySelectorAll('.grid.grid-cols-1.md\\:grid-cols-3');
        chartGrids.forEach(grid => {
            grid.classList.remove('md:grid-cols-3');
            grid.classList.add('grid-cols-1');
        });

        // Adjust radar size for better visibility on small screens
        const radar = document.querySelector('.radar-scan');
        if (radar) {
            radar.style.height = '250px';
        }

        // Adjust vehicle info for small screens
        const vehicleInfo = document.querySelector('.vehicle-info-compact');
        if (vehicleInfo) {
            vehicleInfo.classList.remove('md:grid-cols-4');
            vehicleInfo.classList.add('grid-cols-2');
        }
    }
    // Tablet layout adjustments
    else if (width < 1024) {
        // Make charts display in 2 columns
        const chartGrids = document.querySelectorAll('.grid.grid-cols-1.md\\:grid-cols-3');
        chartGrids.forEach(grid => {
            grid.classList.remove('md:grid-cols-3');
            grid.classList.add('md:grid-cols-2');
        });

        // Adjust radar for medium screens
        const radar = document.querySelector('.radar-scan');
        if (radar) {
            radar.style.height = '300px';
        }
    }
    // Desktop layout adjustments
    else {
        // Restore default layout for large screens
        const chartGrids = document.querySelectorAll('.grid.grid-cols-1.md\\:grid-cols-2');
        chartGrids.forEach(grid => {
            grid.classList.remove('md:grid-cols-2');
            grid.classList.add('md:grid-cols-3');
        });

        // Optimize radar for large screens
        const radar = document.querySelector('.radar-scan');
        if (radar) {
            radar.style.height = '350px';
        }
    }

    // Adjust chart heights based on container width
    adjustChartHeights();
}

/**
 * Adjust chart heights based on container width
 */
function adjustChartHeights() {
    const chartContainers = document.querySelectorAll('.chart-container');
    chartContainers.forEach(container => {
        const width = container.offsetWidth;
        // Set height proportional to width for better aspect ratio
        const height = Math.max(250, Math.min(300, width * 0.75));
        container.style.height = `${height}px`;
    });
}

/**
 * Debounce function to limit how often a function is called
 */
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}
