<?php
require '../../../../vendor/autoload.php'; 
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Dotenv\Dotenv; 

// Carregar o .env
$dotenv = Dotenv::createImmutable(__DIR__ . '../../../../../'); 
$dotenv->load();

// Obter a chave secreta do .env
$secret_key = $_ENV['JWT_SECRET'] ?? 'default_secret_key'; 

// Obter o token do cabeçalho Authorization
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["message" => "Você não tem permissão para isso."]);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

try {
    // Decodificar o token
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

    // Carregar e retornar os dados do arquivo data.json
    $data = file_get_contents('../../../../data.json');
    echo $data;

} catch (Exception $e) {
    // Token inválido ou expirado
    http_response_code(401);
    echo json_encode(["message" => "Acesso negado", "error" => $e->getMessage()]);
}
?>