<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    
    <!-- Ant Design CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/antd/4.16.13/antd.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Axios (opcional) -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <style>
        .container {
            margin: 50px;
        }
        .chart-container {
            width: 60%;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hospital Dashboard - Avaliações</h1>

        <div id="reviewsTable" class="ant-table">
            <h2>Resumo de Avaliações</h2>
            <table class="ant-table-content">
                <thead>
                    <tr>
                        <th>Setor</th>
                        <th>Avaliações Positivas</th>
                        <th>Avaliações Neutras</th>
                        <th>Avaliações Negativas</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>

        <div class="chart-container">
            <canvas id="avgRatingsChart"></canvas>
        </div>

        <div class="chart-container">
            <canvas id="ratingsDistributionChart"></canvas>
        </div>
    </div>

    <!-- Ant Design JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/antd/4.16.13/antd.min.js"></script>

    <!-- Custom Script -->
    <script>
        // Dados fictícios da avaliação
        const data = [
            { name: "Recepção", reviews: [ { note: 8 }, { note: 6 }, { note: 9 } ] },
            { name: "Administração", reviews: [ { note: 2 }, { note: 4 }, { note: 5 } ] },
            { name: "Enfermagem", reviews: [ { note: 9 }, { note: 7 }, { note: 8 } ] },
            { name: "Médicos", reviews: [ { note: 10 }, { note: 9 }, { note: 7 } ] },
            { name: "UTI", reviews: [ { note: 9 }, { note: 10 }, { note: 8 } ] },
            { name: "Alimentação", reviews: [ { note: 6 }, { note: 5 }, { note: 7 } ] }
        ];

        // Constantes para melhorar a legibilidade e evitar números mágicos
        const POSITIVE_THRESHOLD = 7;
        const NEUTRAL_THRESHOLD = 5;

        // Função para inicializar a tabela de resumos
        function populateTable(data) {
            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = data.map(item => {
                const { positive, neutral, negative } = calculateRatingsDistribution(item.reviews);
                return `
                    <tr>
                        <td>${item.name}</td>
                        <td>${positive}</td>
                        <td>${neutral}</td>
                        <td>${negative}</td>
                    </tr>
                `;
            }).join('');
        }

        // Função para calcular a média de notas por setor
        function calculateAvgRatings(data) {
            return data.map(item => {
                const total = item.reviews.reduce((sum, review) => sum + review.note, 0);
                const avg = total / item.reviews.length;
                return avg.toFixed(1);
            });
        }

        // Função para calcular a distribuição de notas (Positiva, Neutra e Negativa)
        function calculateRatingsDistribution(reviews) {
            return reviews.reduce((acc, review) => {
                if (review.note >= POSITIVE_THRESHOLD) acc.positive++;
                else if (review.note >= NEUTRAL_THRESHOLD) acc.neutral++;
                else acc.negative++;
                return acc;
            }, { positive: 0, neutral: 0, negative: 0 });
        }

        // Função para inicializar gráfico de barras (Média de Avaliações por Setor)
        function createBarChart(labels, data) {
            new Chart(document.getElementById('avgRatingsChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Média de Avaliações',
                        data: data,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10
                        }
                    }
                }
            });
        }

        // Função para inicializar gráfico de pizza (Distribuição de Notas)
        function createPieChart(distribution) {
            new Chart(document.getElementById('ratingsDistributionChart'), {
                type: 'pie',
                data: {
                    labels: ['Positivas (>= 7)', 'Neutras (5-6)', 'Negativas (< 5)'],
                    datasets: [{
                        data: [distribution.positive, distribution.neutral, distribution.negative],
                        backgroundColor: ['#4caf50', '#ffeb3b', '#f44336']
                    }]
                }
            });
        }

        // Função para calcular a distribuição global de notas
        function calculateGlobalDistribution(data) {
            return data.reduce((acc, item) => {
                const { positive, neutral, negative } = calculateRatingsDistribution(item.reviews);
                acc.positive += positive;
                acc.neutral += neutral;
                acc.negative += negative;
                return acc;
            }, { positive: 0, neutral: 0, negative: 0 });
        }

        // Função principal para inicializar o dashboard
        function initDashboard() {
            const avgRatings = calculateAvgRatings(data);
            const labels = data.map(item => item.name);
            const globalDistribution = calculateGlobalDistribution(data);

            populateTable(data);
            createBarChart(labels, avgRatings);
            createPieChart(globalDistribution);
        }

        // Inicializa o dashboard
        document.addEventListener('DOMContentLoaded', initDashboard);
    </script>
</body>
</html>
