<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionário de Avaliação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body style="overflow: hidden; display: flex; justify-content: center; align-items: center; height: 100vh; width: 100vw; max-width: 100vw; max-height: 100vh; background-color: rgb(240, 247, 253);">

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
        const questions = [{
                type: 'clickable-cards',
                question: 'Como você avalia o atendimento do hospital?',
                options: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
            },
            {
                type: 'multiple',
                question: 'Você recomendaria nosso hospital a amigos ou familiares?',
                options: ['Sim', 'Não', 'Talvez'],
                colors: ['#28a745', '#dc3545', '#ffc107']
            },
            {
                type: 'text',
                question: 'Por favor, descreva sua experiência no hospital.'
            }
        ];

        let currentQuestion = 0;
        let answers = [];

        function loadQuestion() {
            const questionEl = document.getElementById('question');
            const answerEl = document.getElementById('answer');
            const current = questions[currentQuestion];

            questionEl.innerHTML = `<h2 style='font-size: 40px; font-weight: bold; '>${current.question}</h2>`;

            if (current.type === 'clickable-cards') {
                answerEl.innerHTML = current.options.map(option => `
                    <div style="background-color: ${getCardColor(option)}; 
                                width: 80px; 
                                height: 80px; 
                                text-align: center;
                                display: flex;
                                justify-content: center;
                                align-items: center; 
                                line-height: 50px; 
                                font-size: 40px;
                                font-weight: bold; 
                                color: white; 
                                cursor: pointer; 
                                border-radius: 8px; 
                                transition: transform 0.2s ease;"
                        onclick="selectAnswer(${option})">
                        ${option}
                    </div>
                `).join('');
            } else if (current.type === 'multiple') {
                answerEl.innerHTML = current.options.map((option, index) => `
                    <button style="background-color: ${current.colors[index]}; 
                                    padding: 10px 20px; 
                                    font-size: 25px;
                                    color: white; 
                                    font-weight: bold;
                                    cursor: pointer; 
                                    border-radius: 8px; 
                                    transition: background-color 0.2s ease, transform 0.2s ease; 
                                    border: none; 
                                    text-align: center;"
                            onclick="selectAnswer('${option}')">
                        ${option}
                    </button>
                `).join('');
            } else if (current.type === 'text') {
                answerEl.innerHTML = `
                    <textarea style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ced4da;" 
                              rows="4" placeholder="Escreva sua resposta aqui..." id="text-answer"></textarea>
                    <button class="btn btn-primary mt-3" id="next-btn" onclick="selectAnswer(document.getElementById('text-answer').value)" style="margin-top: 20px;">Próxima</button>
                `;
            }
        }

        function getCardColor(option) {
            const red = Math.round((255 * (10 - option)) / 9);
            const green = Math.round((255 * (option - 1)) / 9);
            return `rgb(${red}, ${green}, 0)`;
        }

        function selectAnswer(option) {
            answers[currentQuestion] = {
                question: questions[currentQuestion].question,
                answer: option
            };
            nextQuestion();
        }

        function nextQuestion() {
            currentQuestion++;
            if (currentQuestion < questions.length) {
                loadQuestion();
            } else {
                showThanksMessage();
            }
        }

        function showThanksMessage() {
            document.getElementById('quiz-content').style.display = 'none'; // Esconde o quiz
            document.getElementById('thanks-message').style.display = 'flex'; // Mostra a tela de agradecimento

            setTimeout(() => {
                // Resetar perguntas e respostas
                currentQuestion = 0; // Volta para a primeira pergunta
                answers = []; // Limpa as respostas

                document.getElementById('quiz-content').style.display = 'flex'; // Mostra o quiz novamente
                document.getElementById('thanks-message').style.display = 'none'; // Esconde a mensagem de agradecimento

                loadQuestion(); // Carrega a primeira questão novamente
            }, 5000); // Após 5 segundos
        }


        window.onload = function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log("Erro ao tentar entrar em tela cheia:", err);
                });
            }

            loadQuestion(); // Carregar a primeira pergunta após tentar entrar em tela cheia
        };

        const fullscreenBtn = document.getElementById('fullscreen-btn');

        fullscreenBtn.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log("Erro ao tentar entrar em tela cheia:", err);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        });

        // Escuta as mudanças de estado de tela cheia para alterar o botão
        document.addEventListener('fullscreenchange', function() {
            if (document.fullscreenElement) {
                // Se estiver em tela cheia, mostrar o ícone de sair
                fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>'; // Ícone de sair
            } else {
                // Se não estiver em tela cheia, mostrar o texto "Tela Cheia"
                fullscreenBtn.innerHTML = 'Tela Cheia';
            }
        });
    </script>
</body>

</html>