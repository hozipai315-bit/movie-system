/**
 * Admin Panel Charts Initialization
 */
document.addEventListener('DOMContentLoaded', function() {
    // Top Moods Chart
    const moodCanvas = document.getElementById('moodChart');
    if (moodCanvas) {
        const labels = JSON.parse(moodCanvas.getAttribute('data-labels') || '[]');
        const counts = JSON.parse(moodCanvas.getAttribute('data-counts') || '[]');
        
        new Chart(moodCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Detections',
                    data: counts,
                    backgroundColor: '#ff0000',
                    borderRadius: 10,
                    barThickness: 25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Method Analytics Chart
    const methodCanvas = document.getElementById('methodChart');
    if (methodCanvas) {
        const labels = JSON.parse(methodCanvas.getAttribute('data-labels') || '[]');
        const counts = JSON.parse(methodCanvas.getAttribute('data-counts') || '[]');

        new Chart(methodCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: ['#ff0000', '#000000', '#666666'],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { weight: '700', size: 11 }
                        }
                    }
                }
            }
        });
    }
});
