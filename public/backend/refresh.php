<?php
require '../../vendor/autoload.php'; 
use \Firebase\JWT\JWT;
use Dotenv\Dotenv; 

// Carregar o .env
$dotenv = Dotenv::createImmutable(__DIR__ . '../../../'); 
$dotenv->load();

// Obter a chave secreta do .env
$secret_key = $_ENV['JWT_SECRET'] ?? 'default_secret_key'; 

// Arquivo JSON para armazenar os refresh tokens
$refresh_token_file = 'refresh_tokens.json';

// Função para carregar os refresh tokens do arquivo JSON
function load_refresh_tokens() {
    global $refresh_token_file;
    if (file_exists($refresh_token_file)) {
        return json_decode(file_get_contents($refresh_token_file), true);
    } else {
        return []; // Se o arquivo não existir, retorna um array vazio
    }
}

// Carrega os refresh tokens existentes
$refresh_tokens = load_refresh_tokens();

// Receber o refresh token do cliente (garantindo que o input seja decodificado corretamente)
$input_data = json_decode(file_get_contents('php://input'), true);

// Verifique se o refresh_token foi enviado
if (!isset($input_data['refresh_token'])) {
    http_response_code(400);
    echo json_encode(["message" => "Necessário refresh token no body."]);
    exit;
}

$refresh_token = $input_data['refresh_token'];

// Verificar se o refresh token existe e ainda é válido
if (isset($refresh_tokens[$refresh_token]) && $refresh_tokens[$refresh_token]['expires_at'] > time()) {
    $username = $refresh_tokens[$refresh_token]['username'];

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
} else {
    // Refresh token inválido ou expirado
    http_response_code(401);
    echo json_encode(["message" => "O refresh token está expirado ou mal formatado."]);
}
?>