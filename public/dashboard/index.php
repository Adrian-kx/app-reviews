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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
<div class="container mt-4">
    <!-- Gráfico de levantamento por setor -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Média de Avaliações por Setor</h5>
                    <canvas id="setorChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico geral -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Distribuição Geral das Notas</h5>
                    <canvas id="geralChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de média geral por mês -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Média Geral de Avaliações por Mês</h5>
                    <canvas id="mensalChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de médias -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Médias por Setor</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Setor</th>
                                <th>Média</th>
                            </tr>
                        </thead>
                        <tbody id="mediaTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js Script -->
<script>
    const datas = [
        { name: "Recepção", reviews: [{ note: 8 }, { note: 6 }, { note: 9 }] },
        { name: "Administração", reviews: [{ note: 2 }, { note: 4 }, { note: 5 }, { note: 5 }] },
        { name: "Enfermagem", reviews: [{ note: 9 }, { note: 7 }, { note: 8 }] },
        { name: "Médicos", reviews: [{ note: 10 }, { note: 9 }, { note: 7 }] },
        { name: "UTI", reviews: [{ note: 9 }, { note: 10 }, { note: 8 }] },
        { name: "Alimentação", reviews: [{ note: 6 }, { note: 5 }, { note: 7 }] }
    ];

    // Calculando a média por setor
    const setores = datas.map(item => item.name);
    const medias = datas.map(item => {
        const total = item.reviews.reduce((sum, review) => sum + review.note, 0);
        return (total / item.reviews.length).toFixed(2);
    });

    // Gráfico de levantamento por setor
    const ctxSetor = document.getElementById('setorChart').getContext('2d');
    new Chart(ctxSetor, {
        type: 'bar',
        data: {
            labels: setores,
            datasets: [{
                label: 'Média das Avaliações',
                data: medias,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Distribuição geral das notas
    const allNotas = datas.flatMap(item => item.reviews.map(review => review.note));
    const ctxGeral = document.getElementById('geralChart').getContext('2d');
    new Chart(ctxGeral, {
        type: 'bar',
        data: {
            labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            datasets: [{
                label: 'Frequência das Notas',
                data: allNotas.reduce((acc, note) => {
                    acc[note - 1] = (acc[note - 1] || 0) + 1;
                    return acc;
                }, new Array(10).fill(0)),
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Gráfico de média geral por mês (dados fictícios)
    const meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho"];
    const mediaMensal = [7.5, 6.8, 7.9, 8.2, 7.1, 7.8];
    const ctxMensal = document.getElementById('mensalChart').getContext('2d');
    new Chart(ctxMensal, {
        type: 'line',
        data: {
            labels: meses,
            datasets: [{
                label: 'Média Mensal',
                data: mediaMensal,
                fill: false,
                borderColor: 'rgba(153, 102, 255, 1)',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Inserir dados da tabela
    const mediaTable = document.getElementById('mediaTable');
    setores.forEach((setor, index) => {
        const row = `<tr><td>${setor}</td><td>${medias[index]}</td></tr>`;
        mediaTable.innerHTML += row;
    });
</script>
</body>
</html>
