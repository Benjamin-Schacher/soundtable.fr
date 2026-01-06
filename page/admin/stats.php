<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - SoundTable</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a1a1a;
            color: #f0f0f0;
            margin: 0;
            padding: 2rem;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        h1 { margin: 0; }
        .btn {
            padding: 0.5rem 1rem;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .btn:hover { background-color: #5a6268; }
        
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        .chart-container {
            background-color: #2a2a2a;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        h2 { margin-top: 0; font-size: 1.2rem; color: #ccc; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Statistiques des Vues</h1>
            <a href="/admin/dashboard" class="btn">Retour au Dashboard</a>
        </div>

        <div class="charts-grid">
            <div class="chart-container">
                <h2>Vues des 30 derniers jours</h2>
                <canvas id="dailyChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Top Pages (Vues Totales)</h2>
                <canvas id="pagesChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        async function loadStats() {
            try {
                const response = await fetch('/api/admin/get_stats.php');
                const data = await response.json();

                if (data.error) {
                    alert('Erreur: ' + data.error);
                    return;
                }

                // Graphique Vues Journalières
                const dailyCtx = document.getElementById('dailyChart').getContext('2d');
                new Chart(dailyCtx, {
                    type: 'line',
                    data: {
                        labels: data.daily_views.map(item => item.view_date),
                        datasets: [{
                            label: 'Vues Uniques',
                            data: data.daily_views.map(item => item.daily_views),
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { labels: { color: '#fff' } }
                        },
                        scales: {
                            y: { ticks: { color: '#aaa' }, grid: { color: '#444' } },
                            x: { ticks: { color: '#aaa' }, grid: { color: '#444' } }
                        }
                    }
                });

                // Graphique Top Pages
                const pagesCtx = document.getElementById('pagesChart').getContext('2d');
                new Chart(pagesCtx, {
                    type: 'bar',
                    data: {
                        labels: data.total_views.map(item => item.page_name),
                        datasets: [{
                            label: 'Vues Totales',
                            data: data.total_views.map(item => item.total_views),
                            backgroundColor: '#28a745'
                        }]
                    },
                    options: {
                        responsive: true,
                        indexAxis: 'y',
                        plugins: {
                            legend: { labels: { color: '#fff' } }
                        },
                        scales: {
                            y: { ticks: { color: '#aaa' }, grid: { color: '#444' } },
                            x: { ticks: { color: '#aaa' }, grid: { color: '#444' } }
                        }
                    }
                });

            } catch (err) {
                console.error(err);
                alert('Impossible de charger les statistiques.');
            }
        }

        loadStats();
    </script>
</body>
</html>
