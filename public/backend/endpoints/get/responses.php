<?php
require '../../../../bootstrap.php';
require '../../utils/jwt.php';
require '../../utils/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Resposta para requisições OPTIONS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // Validação do cabeçalho Authorization
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        throw new Exception("Você não tem permissão para isso.", 401);
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);

    // Validação do JWT
    $decoded = validateJWT($token);

    // Conexão com o banco
    $pdo = getPDOConnection();
    if (!$pdo) {
        throw new Exception("Erro ao conectar ao banco de dados.", 500);
    }

    // Filtro de parâmetros opcionais (limit e offset)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    // Consulta ao banco
    $stmt = $pdo->prepare("SELECT * FROM public.responses LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($responses)) {
        throw new Exception("Nenhuma resposta encontrada na tabela responses.", 404);
    }

    // Total de registros
    $total = $pdo->query("SELECT COUNT(*) FROM public.responses")->fetchColumn();

    // Resposta JSON
    echo json_encode([
        "status" => "success",
        "data" => $responses,
        "total" => $total,
        "limit" => $limit,
        "offset" => $offset
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        "message" => $e->getMessage(),
        "error" => $e->getCode() === 500 ? "Erro interno no servidor" : $e->getMessage()
    ]);
}
