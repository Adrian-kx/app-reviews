<?php
require '../../../../bootstrap.php'; // Carrega autoload e configurações globais
require '../../utils/db.php'; // Função para conectar ao banco
require '../../utils/jwt.php'; // Função para validar o JWT

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Gerar um ID único tokenizado (10 caracteres alfanuméricos)
function generateTokenId($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

try {
    // Verificar o cabeçalho Authorization
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception("Você não tem permissão para acessar este recurso. Token ausente.", 401);
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);

    // Validar o JWT
    $decoded = validateJWT($token);

    // Ler os dados da requisição
    $input_data = json_decode(file_get_contents('php://input'), true);

    // Validar os campos obrigatórios
    if (!isset($input_data['name'])) {
        throw new Exception("O campo 'name' é obrigatório.", 400);
    }

    // Extrair dados da requisição
    $id = generateTokenId(); // Gerar um ID único
    $name = $input_data['name'];

    // Conectar ao banco de dados
    $pdo = getPDOConnection();

    // Inserir o novo setor
    $stmt = $pdo->prepare("
        INSERT INTO public.sectors (id, name)
        VALUES (:id, :name)
    ");
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
    ]);

    // Retornar a resposta
    echo json_encode([
        "status" => "success",
        "message" => "Setor criado com sucesso!",
        "data" => [
            "id" => $id,
            "name" => $name
        ]
    ]);
} catch (Exception $e) {
    $response_code = is_numeric($e->getCode()) ? (int)$e->getCode() : 500;
    http_response_code($response_code);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>