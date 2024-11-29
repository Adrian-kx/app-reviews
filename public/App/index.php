<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Questionário de Avaliação</h1>

        <div id="quiz-container">
            <p class="text-center">Carregando questões...</p>
        </div>
    </div>

    <script>
        const API_URL = 'https://example.com/public/backend/endpoints';

        async function getQuestions() {
        const token = await localforage.getItem("jwt");
        try {
                const response = await fetch(`${API_URL}/get/questions.php`, {
                    headers: {
                        "Authorization": `Bearer ${token}`
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    return data.status === 'success' ? data.data : [];
                } else {
                    console.error('Erro ao buscar questões:', response.statusText);
                    return [];
                }
            } catch (error) {
                console.error('Erro na requisição de questões:', error);
                return [];
            }
        }

        // Função para enviar as respostas para a API
        async function sendAnswers(answers) {
        const token = await localforage.getItem("jwt");
        try {
                const response = await fetch(`${API_URL}/post/responses.php`, {
                    method: 'POST',
                    headers: {
                        "Authorization": `Bearer ${token}`,
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(answers)
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.status === 'success') {
                        alert('Respostas enviadas com sucesso!');
                    } else {
                        console.error('Erro ao enviar respostas:', data);
                    }
                } else {
                    console.error('Erro ao enviar respostas:', response.statusText);
                }
            } catch (error) {
                console.error('Erro na requisição de envio de respostas:', error);
            }
        }

        // Função para carregar as questões na página
        async function loadQuestions() {
            const questions = await getQuestions();
            const quizContainer = document.getElementById('quiz-container');

            if (questions.length === 0) {
                quizContainer.innerHTML = '<p class="text-danger text-center">Nenhuma questão disponível no momento.</p>';
                return;
            }

            const form = document.createElement('form');
            form.id = 'quiz-form';
            form.innerHTML = questions.map((question, index) => {
                const questionHTML = `
                    <div class="mb-4">
                        <h5>${question.question}</h5>
                        ${
                            question.type === 'multiple'
                                ? question.options.map(option => `
                                    <div>
                                        <input type="radio" name="answers[${index}]" value="${option}" required>
                                        <label>${option}</label>
                                    </div>
                                `).join('')
                                : `
                                    <textarea name="answers[${index}]" rows="4" class="form-control" required></textarea>
                                `
                        }
                    </div>
                `;
                return questionHTML;
            }).join('');

            // Botão de envio
            const submitButton = document.createElement('button');
            submitButton.type = 'button';
            submitButton.className = 'btn btn-primary';
            submitButton.textContent = 'Enviar';
            submitButton.addEventListener('click', async () => {
                const formData = new FormData(form);
                const answers = Array.from(formData.entries()).map(([key, value], index) => ({
                    question_id: questions[index].id,
                    answer: value
                }));

                await sendAnswers(answers);
            });

            form.appendChild(submitButton);
            quizContainer.innerHTML = '';
            quizContainer.appendChild(form);
        }

        // Carrega as questões ao iniciar a página
        loadQuestions();
    </script>
</body>

</html>
