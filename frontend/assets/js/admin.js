/** Admin dashboard chart (Chart.js) — booking requests per month by status. */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    const canvas = document.getElementById('bookingsChart');
    if (!canvas || !window.Chart) return;

    const data = JSON.parse(canvas.dataset.chart);

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                { label: 'Confirmed', data: data.confirmed, backgroundColor: '#0e7c86', borderRadius: 4 },
                { label: 'Pending',   data: data.pending,   backgroundColor: '#f2a541', borderRadius: 4 },
                { label: 'Cancelled', data: data.cancelled, backgroundColor: '#b8c2c8', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
});
