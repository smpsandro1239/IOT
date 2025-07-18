// Funcao para redimensionar graficos
function resizeCharts() {
    if (typeof window.app !== 'undefined' && window.app.charts) {
        Object.values(window.app.charts).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    }
}

// Redimensionar graficos quando a janela for redimensionada
window.addEventListener('resize', resizeCharts);

// Redimensionar graficos quando a aba ficar visivel
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        setTimeout(resizeCharts, 100);
    }
});
