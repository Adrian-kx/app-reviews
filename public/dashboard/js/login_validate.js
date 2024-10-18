document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevenir o comportamento padrão do formulário

    // Pegar valores dos campos
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // Validação simples do formulário
    if (username === '' || password === '') {
        document.getElementById('loginMessage').textContent = 'Por favor, preencha todos os campos.';
        console.log('Campos de usuário ou senha estão vazios.');
        return;
    }

    console.log('Iniciando requisição de login...');

    // Fazer requisição de login via axios
    axios.post('http://localhost:8000/backend/login.php', {
        username: username,
        password: password
    })
    .then(function(response) {
        // Login bem-sucedido, salvar dados no localForage
        const data = response.data;
        console.log('Login bem-sucedido, recebendo dados:', data);

        localforage.setItem('jwt', data.jwt).then(function() {
            console.log('JWT armazenado com sucesso!');
            // Redirecionar para o dashboard
            document.getElementById('loginContainer').style.display = 'none';
            document.getElementById('dashboardContainer').style.display = 'block';
            console.log('Dashboard exibido.');
        });
        localforage.setItem('username', data.username).then(function() {
            console.log('Nome de usuário armazenado com sucesso!');
        });

        // Exibir mensagem de sucesso
        document.getElementById('loginMessage').textContent = 'Login realizado com sucesso!';
    })
    .catch(function(error) {
        // Tratar erro de login
        document.getElementById('loginMessage').textContent = 'Usuário ou senha incorretos!';
        console.log('Erro ao fazer login:', error);
    });
});

// Verificar se o usuário está logado ao carregar a página
localforage.getItem('jwt').then(function(jwt) {
    if (jwt) {
        // Se o JWT estiver presente, mostrar o dashboard
        document.getElementById('loginContainer').style.display = 'none';
        document.getElementById('dashboardContainer').style.display = 'block';
        console.log('Usuário está logado, exibindo dashboard.');
    } else {
        // Caso contrário, mostrar o formulário de login
        document.getElementById('loginContainer').style.display = 'block';
        document.getElementById('dashboardContainer').style.display = 'none';
        console.log('Usuário não está logado, exibindo formulário de login.');
    }
});
