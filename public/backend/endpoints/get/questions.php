<?php
require_once '../../../../bootstrap.php';
require_once '../../utils/jwt.php';
require_once '../../utils/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Lida com requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $headers = getallheaders();
    $decoded = null;

    // Verifica se o JWT foi enviado no cabeçalho
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        // Tenta validar o JWT, mas continua se falhar
        try {
            $decoded = validateJWT($token);
        } catch (Exception $e) {
            // JWT inválido ou ausente não impede a execução
            $decoded = null;
        }
    }

    // Conexão com o banco de dados
    $pdo = getPDOConnection();

    // Consulta na tabela questions
    $stmt = $pdo->query("SELECT * FROM public.questions");
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($questions)) {
        throw new Exception("Nenhuma questão encontrada na tabela questions.", 404);
    }

    echo json_encode([
        "status" => "success",
        "data" => $questions,
        "user" => $decoded, // Retorna o usuário decodificado, se o JWT for válido
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        "message" => $e->getMessage(),
        "error" => $e->getCode() === 500 ? "Erro interno no servidor" : $e->getMessage()
    ]);
}
