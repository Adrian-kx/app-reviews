<?php
require '../../vendor/autoload.php';
require './utils/db.php'; // Conexão ao banco
use \Firebase\JWT\JWT;
use Dotenv\Dotenv;

// Carregar o .env
$dotenv = Dotenv::createImmutable(__DIR__ . '../../../');
$dotenv->load();

// Obter a chave secreta do .env
$secret_key = $_ENV['JWT_SECRET'] ?? 'default_secret_key';

// Decodificar o corpo da requisição JSON
$input_data = json_decode(file_get_contents('php://input'), true);

if (isset($input_data['username']) && isset($input_data['password'])) {
    $username = $input_data['username'];
    $password = $input_data['password'];

    // Conectar ao banco e buscar o usuário
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT password FROM public.users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
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
        $refresh_expiration = date('Y-m-d H:i:s', time() + (7 * 24 * 60 * 60)); // Expira em 7 dias

        // Salvar o token no banco
        $stmt = $pdo->prepare("
            INSERT INTO public.refresh_tokens (token, username, expires_at)
            VALUES (:token, :username, :expires_at)
        ");
        $stmt->execute([
            ':token' => $refresh_token,
            ':username' => $username,
            ':expires_at' => $refresh_expiration
        ]);

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
    // Dados de login não fornecidos
    http_response_code(400);
    echo json_encode(["message" => "Insira um usuário e senha!"]);
}