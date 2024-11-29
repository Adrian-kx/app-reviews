<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Perguntas</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.9.0/localforage.min.js"></script>
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>

    <div class="container mt-5">
        <button id="returnedToHome" class="logout-button">Voltar</button>

        <h1 class="mt-4">Gerenciamento de Perguntas</h1>

        <!-- Formulário para adicionar ou editar perguntas -->
        <div id="questionForm">
            <form id="addQuestionForm" class="mb-4">
                <input type="hidden" id="questionId">

                <div class="mb-3">
                    <label for="questionType" class="form-label">Tipo de Pergunta</label>
                    <select class="form-select" id="questionType" required>
                        <option value="" selected disabled>Selecione o tipo de pergunta</option>
                        <option value="clickable-cards">Cartões clicáveis (1 a 10)</option>
                        <option value="multiple">Múltipla escolha</option>
                        <option value="text">Texto</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="questionText" class="form-label">Pergunta</label>
                    <input type="text" class="form-control" id="questionText" placeholder="Digite a pergunta" required>
                </div>

                <!-- Campos adicionais dinâmicos -->
                <div id="additionalFields"></div>

                <button type="submit" class="btn btn-primary" id="submitQuestion">Salvar Pergunta</button>
            </form>
        </div>

        <!-- Tabela para listar perguntas -->
        <h2>Perguntas</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Pergunta</th>
                    <th>Opções</th>
                    <th>Cores</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="questionsTable"></tbody>
        </table>
    </div>
    <script>
        function fixMalformedJsonString($string) {
            // Remove as chaves externas `{}` e separa por vírgula
            $string = trim($string, '{}');
            $array = explode(',', $string);

            // Retorna o array como JSON válido
            return $array;
        }
        document
            .getElementById("returnedToHome")
            .addEventListener("click", function() {
                window.location.href = "/app-reviews/public/dashboard/";
            });
        const API_URL = 'http://localhost:8000/public/backend/endpoints';

        async function getJwt() {
            const jwt = await localforage.getItem("jwt");
            console.log("JWT", jwt)
            return jwt
        }

        async function fetchQuestions() {
            try {
                const jwt = await localforage.getItem("jwt");
                if (!jwt) {
                    console.error("JWT não encontrado");
                    return [];
                }

                const response = await fetch(`${API_URL}/get/questions.php`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${jwt}`,
                        'Content-Type': 'application/json',
                    },
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error(`Erro ao buscar perguntas (HTTP ${response.status}):`, errorText);
                    return [];
                }

                const data = await response.json();

                const questions = data.data.map((question) => {

                    // Corrige as strings malformadas no formato "{item1,item2,item3}"
                    const fixMalformedJsonString = (string) => {
                        if (!string || typeof string !== "string") return null;
                        return string
                            .replace(/{|}/g, "") // Remove as chaves externas
                            .split(","); // Divide por vírgula
                    };

                    return {
                        ...question,
                        options: fixMalformedJsonString(question.options),
                        colors: fixMalformedJsonString(question.colors),
                    };
                });

                console.log("Perguntas processadas:", questions);
                return questions;
            } catch (error) {
                console.error("Erro na função fetchQuestions:", error);
                return [];
            }
        }

        async function saveQuestion(question, id = null) {
            const jwt = await getJwt()

            const url = id ? `${API_URL}/update/questions.php` : `${API_URL}/post/questions.php`;
            const method = id ? 'PUT' : 'POST';
            console.log("AQUIII O DATAS", question)
            const response = await fetch(url, {
                method,
                headers: {
                    'Authorization': `Bearer ${jwt}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(id ? {
                    ...question,
                    id
                } : question),
            });

            if (!response.ok) {
                console.error('Erro ao salvar a pergunta:', await response.text());
            }
        }

        // Função para excluir pergunta
        async function deleteQuestion(id) {
            const jwt = await getJwt()
            const response = await fetch(`${API_URL}/delete/questions.php`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${jwt}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id
                }),
            });
            console.log("id", id)
            if (!response.ok) {
                console.error('Erro ao excluir a pergunta:', await response.text());
            }
        }

        // Função para renderizar perguntas na tabela
        async function renderQuestions() {
            const questions = await fetchQuestions();
            const tableBody = document.getElementById('questionsTable');
            tableBody.innerHTML = '';

            questions.forEach((question) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${question?.id}</td>
                    <td>${question?.type}</td>
                    <td>${question?.question}</td>
                    <td>${question?.options?.join(', ') || ''}</td>
                    <td>${question?.colors?.join(', ') || ''}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="editQuestion('${String(question.id)}', '${question.type}', '${question.question}', '${question.options?.join(', ')}', '${question.colors?.join(', ')}')">Editar</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteAndRender('${String(question.id)}')">Excluir</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        function addColorPicker() {
            const colorPickersDiv = document.getElementById('colorPickers');
            const colorPicker = document.createElement('div');
            colorPicker.classList.add('input-group', 'mb-2');

            colorPicker.innerHTML = `
        <input type="color" class="form-control form-control-color" value="#000000" title="Escolha uma cor">
        <button type="button" class="btn btn-danger btn-sm removeColorPicker">Remover</button>
    `;

            colorPickersDiv.appendChild(colorPicker);

            // Configura o botão de remover seletor de cor
            colorPicker.querySelector('.removeColorPicker').addEventListener('click', () => {
                colorPickersDiv.removeChild(colorPicker);
            });
        }

        // Função para configurar o formulário para edição
        function editQuestion(id, type, question, options, colors) {
            document.getElementById('questionId').value = id;
            document.getElementById('questionType').value = type;
            document.getElementById('questionText').value = question;

            showAdditionalFields(type);

            if (type === 'multiple') {
                options = ['Sim', 'Não', 'Talvez'];
                colors = ['#28a745', '#dc3545', '#ffc107'];
            }
        }

        async function deleteAndRender(id) {
            await deleteQuestion(id);
            renderQuestions();
        }

        function showAdditionalFields(type) {
            const additionalFields = document.getElementById('additionalFields');
            additionalFields.innerHTML = '';

            if (type === 'clickable-cards') {
                additionalFields.innerHTML = `<p>As opções serão fixas de 1 a 10.</p>`;
            } else if (type === 'multiple') {
                // Informar ao usuário que múltipla escolha é fixa
                additionalFields.innerHTML = `
            <p>Esta pergunta será configurada automaticamente como:</p>
            <ul>
                <li>Opções: <b>Sim</b>, <b>Não</b>, <b>Talvez</b></li>
                <li>Cores: <span style="color:#28a745;">Sim (#28a745)</span>, 
                    <span style="color:#dc3545;">Não (#dc3545)</span>, 
                    <span style="color:#ffc107;">Talvez (#ffc107)</span></li>
            </ul>
        `;

            }
        }


        // Alterar os campos adicionais ao selecionar o tipo
        document.getElementById('questionType').addEventListener('change', (e) => {
            showAdditionalFields(e.target.value);
        });

        // Configuração do formulário de envio
        document.getElementById('addQuestionForm').addEventListener('submit', async (event) => {
            event.preventDefault();

            const id = document.getElementById('questionId').value;
            const type = document.getElementById('questionType').value;
            const question = document.getElementById('questionText').value;

            let options = null;
            let colors = null;

            if (type === 'clickable-cards') {
                options = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            } else if (type === 'multiple') {
                options = ['Sim', 'Não', 'Talvez'];
                colors = ['#28a745', '#dc3545', '#ffc107'];
            }

            const data = {
                type,
                question,
                options,
                colors
            };

            await saveQuestion(data, id || null);
            document.getElementById('addQuestionForm').reset();
            showAdditionalFields('');
            renderQuestions();
        });

        // Inicializar a página
        renderQuestions();
    </script>
</body>

</html>