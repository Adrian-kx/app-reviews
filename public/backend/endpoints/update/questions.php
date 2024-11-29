<?php
require '../../../../bootstrap.php';
require '../../utils/jwt.php';
require '../../utils/db.php';

header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception("Você não tem permissão para isso.", 401);
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);

    $decoded = validateJWT($token);

    // Decodificar os dados da requisição
    $input_data = json_decode(file_get_contents('php://input'), true);

    // Validar campos obrigatórios
    if (!isset($input_data['id']) || !isset($input_data['question'])) {
        throw new Exception("Os campos 'id' e 'question' são obrigatórios.", 400);
    }

    $id = $input_data['id'];
    $question = $input_data['question'];
    $type = isset($input_data['type']) ? $input_data['type'] : null;
    $options = isset($input_data['options']) ? $input_data['options'] : null;
    $colors = isset($input_data['colors']) ? $input_data['colors'] : null;

    // Conectar ao banco de dados
    $pdo = getPDOConnection();

    // Atualizar a questão
    $stmt = $pdo->prepare("
        UPDATE public.questions
        SET question = :question, type = :type, options = :options, colors = :colors
        WHERE id = :id
    ");
    $stmt->execute([
        ':id' => $id,
        ':question' => $question,
        ':type' => $type,
        ':options' => $options ? '{' . implode(',', $options) . '}' : null,
        ':colors' => $colors ? '{' . implode(',', $colors) . '}' : null,
    ]);

    // Verificar se alguma linha foi afetada
    if ($stmt->rowCount() === 0) {
        throw new Exception("Nenhuma questão encontrada com o ID fornecido.", 404);
    }

    echo json_encode([
        "status" => "success",
        "message" => "Questão atualizada com sucesso!"
    ]);
} catch (Exception $e) {
    $response_code = is_numeric($e->getCode()) ? (int)$e->getCode() : 500;
    http_response_code($response_code);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}