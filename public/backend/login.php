<?php
require '../../vendor/autoload.php'; 
use \Firebase\JWT\JWT;
use Dotenv\Dotenv; 

// Carregar o .env
$dotenv = Dotenv::createImmutable(__DIR__ . '../../'); 
$dotenv->load();

// Obter a chave secreta do .env
$secret_key = $_ENV['JWT_SECRET'] ?? 'default_secret_key';

// Arquivo JSON para armazenar os refresh tokens
$refresh_token_file = 'refresh_tokens.json';

// Se o arquivo não existir, cria um array vazio e o arquivo
if (!file_exists($refresh_token_file)) {
    file_put_contents($refresh_token_file, json_encode([]));
}

// Função para carregar os refresh tokens do arquivo JSON
function load_refresh_tokens() {
    global $refresh_token_file;
    return json_decode(file_get_contents($refresh_token_file), true);
}

// Função para salvar os refresh tokens no arquivo JSON
function save_refresh_tokens($refresh_tokens) {
    global $refresh_token_file;
    file_put_contents($refresh_token_file, json_encode($refresh_tokens));
}

// Carrega os refresh tokens existentes
$refresh_tokens = load_refresh_tokens();

// Usuários simulados
$users = [
    'admin' => password_hash('123456', PASSWORD_DEFAULT), // Senha hashada
];

// Decodificar o corpo da requisição JSON
$input_data = json_decode(file_get_contents('php://input'), true);

// Verificar se os dados foram enviados
if (isset($input_data['username']) && isset($input_data['password'])) {
    $username = $input_data['username'];
    $password = $input_data['password'];

    // Verificar se o usuário existe e a senha está correta
    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        // Gerar JWT
        $issued_at = time();
        $expiration_time = $issued_at + (60 * 60); // 1 hora de validade
        $payload = [
            "iss" => "http://localhost", // Emissor
            "iat" => $issued_at, // Tempo de emissão
            "exp" => $expiration_time, // Expiração
            "data" => [
                "username" => $username
            ]
        ];

        $jwt = JWT::encode($payload, $secret_key, 'HS256');

        // Gerar Refresh Token
        $refresh_token = bin2hex(random_bytes(32)); // Token aleatório
        $refresh_expiration = time() + (7 * 24 * 60 * 60); // 7 dias de validade
        $refresh_tokens[$refresh_token] = [
            "username" => $username,
            "expires_at" => $refresh_expiration
        ];

        // Salva os refresh tokens no arquivo JSON
        save_refresh_tokens($refresh_tokens);

        // Retornar o JWT e o refresh token para o cliente
        echo json_encode([
            "message" => "Login realizado com sucesso!",
            "jwt" => $jwt,
            "refresh_token" => $refresh_token
        ]);
    } else {
        // Credenciais inválidas
        http_response_code(401);
        echo json_encode(["message" => "Usuário ou senha incorretos!"]);
    }
} else {
    // Se os dados de login não foram fornecidos
    http_response_code(400);
    echo json_encode(["message" => "Insira um usuário e senha!"]);
}
?>