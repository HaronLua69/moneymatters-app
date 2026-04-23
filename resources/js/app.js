import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

/**
 * Render or re-render a Chart.js chart inside a canvas element.
 * Destroys the previous instance if one exists so Livewire DOM swaps are safe.
 *
 * @param {string} canvasId  - The canvas element id
 * @param {object} config    - A Chart.js configuration object
 */
function renderChart(canvasId, config) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    // Destroy previous chart instance if Livewire re-rendered the component
    if (canvas._chartInstance) {
        canvas._chartInstance.destroy();
    }

    canvas._chartInstance = new Chart(canvas, config);
}

// Expose globally so Blade @script / inline scripts can call it
window.renderChart = renderChart;

// Re-init charts after every Livewire morphdom update (handles page navigation too)
document.addEventListener('livewire:navigated', () => {
    document.dispatchEvent(new CustomEvent('charts:reinit'));
});
