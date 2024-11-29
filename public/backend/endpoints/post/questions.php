<?php
require '../../../../bootstrap.php'; // Carrega autoload e configurações globais
require '../../utils/db.php'; // Função para conectar ao banco
require '../../utils/jwt.php'; // Função para validar o JWT

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Lida com requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
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
    $decoded = validateJWT($token); // Função de validação do JWT no arquivo jwt.php

    // Ler os dados da requisição
    $input_data = json_decode(file_get_contents('php://input'), true);

    // Validar os campos obrigatórios
    if (!isset($input_data['type']) || !isset($input_data['question'])) {
        throw new Exception("Os campos 'type' e 'question' são obrigatórios.", 400);
    }

    // Extrair dados da requisição
    $id = generateTokenId(); // Gerar um ID único
    $type = $input_data['type'];
    $question = $input_data['question'];
    $options = isset($input_data['options']) ? $input_data['options'] : null;
    $colors = isset($input_data['colors']) ? $input_data['colors'] : null;

    // Conectar ao banco de dados
    $pdo = getPDOConnection();

    // Inserir a nova questão
    $stmt = $pdo->prepare("
        INSERT INTO public.questions (id, type, question, options, colors)
        VALUES (:id, :type, :question, :options, :colors)
    ");
    $stmt->execute([
        ':id' => $id,
        ':type' => $type,
        ':question' => $question,
        ':options' => $options ? '{' . implode(',', $options) . '}' : null,
        ':colors' => $colors ? '{' . implode(',', $colors) . '}' : null,
    ]);

    // Retornar a resposta
    echo json_encode([
        "status" => "success",
        "message" => "Questão criada com sucesso!",
        "data" => [
            "id" => $id,
            "type" => $type,
            "question" => $question,
            "options" => $options,
            "colors" => $colors
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