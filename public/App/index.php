<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionário</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.9.0/localforage.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/index.css">

</head>

<body>
    <header style="height: 100px; width: 90vw; position: absolute; top: 0; padding: 15px 0px; display: flex; justify-content: space-between; align-items: center;">
        <div style="height: 100%;">
            <img src="./../assets/logo-white.png" alt="Logo da Empresa" style="height: 100%;">
        </div>

        <div>
            <button class="btn btn-outline-primary" id="fullscreen-btn">
                Tela Cheia

            </button>
        </div>
    </header>

    <div class="container-fluid d-flex align-items-center justify-content-center" id="quiz-container" style="text-align: center;">
        <div class="question-card p-4" id="quiz-content" style="gap: 15px;display: flex; justify-content: center; align-items: center; flex-direction: column; height: 50vh; width: 90%; background-color: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <div id="question" style="margin-bottom: 20px; font-size: 24px;"></div>
            <div id="answer" style="display: flex; justify-content: space-between; gap: 15px; flex-wrap: wrap;"></div>
        </div>

        <div id="thanks-message" style="min-width: 70vw; display: none; justify-content: center; align-items: center; flex-direction: column; height: 100%; width: 100%; text-align: center;">
            <h1>Obrigado por completar o questionário!</h1>
            <p>Sua participação é muito importante para nós.</p>
        </div>
    </div>

    <script>
    const API_URL = 'http://localhost:8000/public/backend/endpoints';
    let questions = [];
    let currentQuestion = 0;
    let answers = [];

    async function getQuestions() {
        const token = await localforage.getItem("jwt");
        try {
            const response = await fetch(`${API_URL}/get/questions.php`, {
                headers: {
                    "Authorization": `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                console.log(data.data);
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

    async function sendAnswers() {
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

    function loadQuestion() {
        const quizContent = document.getElementById('quiz-content');
        const questionEl = document.getElementById('question');
        const answerEl = document.getElementById('answer');

        if (currentQuestion >= questions.length) {
            document.getElementById('quiz-content').style.display = 'none';
            document.getElementById('thanks-message').style.display = 'flex';
            sendAnswers();
            return;
        }

        const current = questions[currentQuestion];
        questionEl.innerHTML = `<h2 style="font-size: 30px; font-weight: bold;">${current.question}</h2>`;
        answerEl.innerHTML = '';

        if (current.type === 'clickable-cards') {
            const options = current.options && Array.isArray(current.options)
                ? current.options
                : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // Opções padrão
            answerEl.innerHTML = options.map(option => `
                <div style="background-color: ${getCardColor(option)}; 
                            width: 80px; 
                            height: 80px; 
                            text-align: center; 
                            display: flex; 
                            justify-content: center; 
                            align-items: center; 
                            font-size: 25px; 
                            font-weight: bold; 
                            color: white; 
                            cursor: pointer; 
                            border-radius: 8px;" 
                    onclick="selectAnswer(${option})">
                    ${option}
                </div>
            `).join('');
        } else if (current.type === 'multiple') {
            const options = typeof current.options === 'string'
                ? current.options.replace(/{|}/g, '').split(',')
                : current.options || [];
            const colors = typeof current.colors === 'string'
                ? current.colors.replace(/{|}/g, '').split(',')
                : current.colors || [];
            answerEl.innerHTML = options.map((option, index) => `
                <button style="background-color: ${colors[index] || '#000'}; 
                                padding: 10px 20px; 
                                font-size: 18px; 
                                color: white; 
                                font-weight: bold; 
                                border: none; 
                                cursor: pointer; 
                                border-radius: 8px;" 
                        onclick="selectAnswer('${option}')">
                    ${option}
                </button>
            `).join('');
        } else if (current.type === 'text') {
            answerEl.innerHTML = `
                <textarea style="width: 100%; padding: 10px; border-radius: 5px;" 
                          rows="4" placeholder="Escreva sua resposta aqui..." id="text-answer"></textarea>
                <button class="btn btn-primary mt-3" onclick="selectAnswer(document.getElementById('text-answer').value)">Próxima</button>
            `;
        }
    }

    function getCardColor(option) {
        const red = Math.round((255 * (10 - option)) / 9);
        const green = Math.round((255 * (option - 1)) / 9);
        return `rgb(${red}, ${green}, 0)`;
    }

    function selectAnswer(answer) {
        answers.push({
            question_id: questions[currentQuestion].id,
            answer
        });
        currentQuestion++;
        loadQuestion();
    }

    async function startQuiz() {
        questions = await getQuestions();
        if (questions.length === 0) {
            document.getElementById('quiz-container').innerHTML = '<p class="text-danger text-center">Nenhuma questão disponível no momento.</p>';
            return;
        }
        loadQuestion();
    }

    startQuiz();
</script>


</body>

</html>