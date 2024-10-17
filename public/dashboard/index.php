<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="css/index.css">

</head>

<body>

    <body>
        <div class="header">
            <h2>Hospital Dashboard</h2>
            <button class="logout-button" onclick="logout()">Sair</button>
        </div>

        <div class="container">
            <div class="card-container">


                <div class="card">
                    <h3>Média de Avaliações por Setor</h3>
                    <div class="chart-container">
                        <canvas id="avgRatingsChart"></canvas>
                    </div>
                </div>

                <div class="card">
                    <h3>Distribuição de Notas</h3>
                    <div class="chart-container">
                        <canvas id="ratingsDistributionChart"></canvas>
                    </div>
                </div>
                <div class="card">
                    <h3>Resumo de Avaliações</h3>
                    <table>
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
            </div>
        </div>
        <script>
            const data = [{
                    name: "Recepção",
                    reviews: [{
                        note: 8
                    }, {
                        note: 6
                    }, {
                        note: 9
                    }]
                },
                {
                    name: "Administração",
                    reviews: [{
                        note: 2
                    }, {
                        note: 4
                    }, {
                        note: 5
                    }]
                },
                {
                    name: "Enfermagem",
                    reviews: [{
                        note: 9
                    }, {
                        note: 7
                    }, {
                        note: 8
                    }]
                },
                {
                    name: "Médicos",
                    reviews: [{
                        note: 10
                    }, {
                        note: 9
                    }, {
                        note: 7
                    }]
                },
                {
                    name: "UTI",
                    reviews: [{
                        note: 9
                    }, {
                        note: 10
                    }, {
                        note: 8
                    }]
                },
                {
                    name: "Alimentação",
                    reviews: [{
                        note: 6
                    }, {
                        note: 5
                    }, {
                        note: 7
                    }]
                }
            ];

            const POSITIVE_THRESHOLD = 7;
            const NEUTRAL_THRESHOLD = 5;

            function populateTable(data) {
                const tableBody = document.getElementById('tableBody');
                tableBody.innerHTML = data.map(item => {
                    const {
                        positive,
                        neutral,
                        negative
                    } = calculateRatingsDistribution(item.reviews);
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

            function calculateAvgRatings(data) {
                return data.map(item => {
                    const total = item.reviews.reduce((sum, review) => sum + review.note, 0);
                    const avg = total / item.reviews.length;
                    return avg.toFixed(1);
                });
            }

            function calculateRatingsDistribution(reviews) {
                return reviews.reduce((acc, review) => {
                    if (review.note >= POSITIVE_THRESHOLD) acc.positive++;
                    else if (review.note >= NEUTRAL_THRESHOLD) acc.neutral++;
                    else acc.negative++;
                    return acc;
                }, {
                    positive: 0,
                    neutral: 0,
                    negative: 0
                });
            }

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

            function calculateGlobalDistribution(data) {
                return data.reduce((acc, item) => {
                    const {
                        positive,
                        neutral,
                        negative
                    } = calculateRatingsDistribution(item.reviews);
                    acc.positive += positive;
                    acc.neutral += neutral;
                    acc.negative += negative;
                    return acc;
                }, {
                    positive: 0,
                    neutral: 0,
                    negative: 0
                });
            }

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

            // Função de logout simulada
            function logout() {
                alert('Você saiu da sessão.');
                window.location.href = '/login'; // Redireciona para a página de login (ajustar conforme necessário)
            }
        </script>
    </body>

</html>