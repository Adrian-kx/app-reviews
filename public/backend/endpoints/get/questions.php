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
    if (!isset($headers['Authorization'])) {
        throw new Exception("Você não tem permissão para isso.", 401);
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);

    // Validação do JWT
    $decoded = validateJWT($token);

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
        "data" => $questions
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        "message" => $e->getMessage(),
        "error" => $e->getCode() === 500 ? "Erro interno no servidor" : $e->getMessage()
    ]);
}
?>
