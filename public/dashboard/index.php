<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.9.0/localforage.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="./css/index.css">
</head>

<body>
    <div id="app">
        <div id="loginContainer" style="display: none;align-self:center;margin-top: 25vh;height: 50vh">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card shadow-sm">
                        <div style="display: flex; justify-content: space-around; flex-direction: row; align-items:center;padding:20px">
                            <div style="width: 50%;padding: 50px">
                                <img src="./../assets/logo-white.png" alt="Logo" style="max-width: 100%; width: 100%;">
                            </div>
                            <div style="max-width: 50%; width: 50%;">
                                <h5 class="card-title">Login</h5>
                                <form id="loginForm">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Usuário</label>
                                        <input type="text" class="form-control" id="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Senha</label>
                                        <input type="password" class="form-control" id="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Entrar</button>
                                </form>
                                <div id="loginMessage" class="mt-3"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div id="dashboardContainer" style="display: none;">
            <header>
                <header class="container mt-2">
                    <img src="./../assets/logo-white.png" alt="Logo">
                    <h2 class="card-title">Dashboard das avaliações</h2>
                    <div>
                        <button id="manageQuestionsButton" class="question-button-page">Gerenciar perguntas</button>
                        <button class="logout-button">Sair</button>
                    </div>
                </header>
            </header>
            <div class="container mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Média de Avaliações por Setor</h5>
                                <canvas id="setorChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Distribuição Geral das Notas</h5>
                                <canvas id="geralChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title ">Média Geral de Avaliações por Mês</h5>
                                <div style="padding: 25px 200px !important;">
                                    <canvas id="mensalChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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


                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Filtrar Avaliações por Setor, Nota e Data</h5>

                                <label for="setorFilter">Filtrar por Setor:</label>
                                <select id="setorFilter" class="form-select mb-3">
                                    <option value="">Todos</option>
                                </select>

                                <label for="sortNotas">Ordenar por Nota:</label>
                                <select id="sortNotas" class="form-select mb-3">
                                    <option value="desc">Maior para Menor</option>
                                    <option value="asc">Menor para Maior</option>
                                </select>

                                <label for="dateStart">Data Inicial:</label>
                                <input type="date" id="dateStart" class="form-control mb-3">

                                <label for="dateEnd">Data Final:</label>
                                <input type="date" id="dateEnd" class="form-control mb-3">
                                <button id="filterButton" class="btn btn-primary mb-3">Filtrar</button>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Setor</th>
                                            <th>Nota</th>
                                            <th>Data</th>
                                            <th>Texto</th>
                                        </tr>
                                    </thead>
                                    <tbody id="filterTable"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/index.js"></script>



</body>

</html>