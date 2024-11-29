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

try {
    // Receber o refresh token do cliente
    $input_data = json_decode(file_get_contents('php://input'), true);

    // Verifique se o refresh_token foi enviado
    if (!isset($input_data['refresh_token'])) {
        throw new Exception("Necessário refresh token no body.", 400);
    }

    $refresh_token = $input_data['refresh_token'];

    // Conectar ao banco de dados
    $pdo = getPDOConnection();

    // Verificar se o refresh token existe e ainda é válido
    $stmt = $pdo->prepare("SELECT username, expires_at FROM public.refresh_tokens WHERE token = :token");
    $stmt->execute([':token' => $refresh_token]);
    $token_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$token_data || strtotime($token_data['expires_at']) <= time()) {
        throw new Exception("O refresh token está expirado ou inválido.", 401);
    }

    $username = $token_data['username'];

    // Gerar um novo JWT
    $issued_at = time();
    $expiration_time = $issued_at + (60 * 60); // 1 hora de validade
    $payload = [
        "iss" => "http://localhost",
        "iat" => $issued_at,
        "exp" => $expiration_time,
        "data" => [
            "username" => $username
        ]
    ];

    $jwt = JWT::encode($payload, $secret_key, 'HS256');

    // Retornar o novo JWT
    echo json_encode([
        "message" => "Token refreshed",
        "jwt" => $jwt
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        "message" => $e->getMessage()
    ]);
}