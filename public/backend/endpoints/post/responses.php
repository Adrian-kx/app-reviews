<?php
require '../../../../bootstrap.php'; // Carrega autoload e configurações globais
require '../../utils/db.php'; // Função para conectar ao banco
require '../../utils/jwt.php'; // Função para validar o JWT

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");// Gerar um ID único tokenizado (10 caracteres alfanuméricos)
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
    if (!isset($input_data['sector']) || !isset($input_data['date']) || !isset($input_data['responses'])) {
        throw new Exception("Os campos 'sector', 'date' e 'responses' são obrigatórios.", 400);
    }

    // Validar se 'responses' é um array
    if (!is_array($input_data['responses'])) {
        throw new Exception("O campo 'responses' deve ser um array.", 400);
    }

    // Validar cada objeto em 'responses'
    foreach ($input_data['responses'] as $response) {
        if (!isset($response['question_id']) || !isset($response['answer'])) {
            throw new Exception("Cada objeto em 'responses' deve conter 'question_id' e 'answer'.", 400);
        }
    }

    // Extrair os dados da requisição
    $id = generateTokenId(); // Gerar um ID único
    $sector = $input_data['sector'];
    $date = $input_data['date'];
    $responses = json_encode($input_data['responses']); // Serializar o array de respostas como JSON

    // Conectar ao banco de dados
    $pdo = getPDOConnection();

    // Inserir a nova resposta
    $stmt = $pdo->prepare("
        INSERT INTO public.responses (id, date, sector, responses)
        VALUES (:id, :date, :sector, :responses)
    ");
    $stmt->execute([
        ':id' => $id,
        ':date' => $date,
        ':sector' => $sector,
        ':responses' => $responses,
    ]);

    // Retornar a resposta
    echo json_encode([
        "status" => "success",
        "message" => "Resposta adicionada com sucesso!",
        "data" => [
            "id" => $id,
            "date" => $date,
            "sector" => $sector,
            "responses" => $input_data['responses']
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