// This file contains JavaScript code for the dashboard, including functions to fetch data from the backend and render graphs using charting libraries.

document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('myChart').getContext('2d');

    function fetchData() {
        fetch('../backend/get_dashboard_data.php')
            .then(response => response.json())
            .then(data => {
                renderChart(data);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function renderChart(data) {
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'User Statistics',
                    data: data.values,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    fetchData();
});