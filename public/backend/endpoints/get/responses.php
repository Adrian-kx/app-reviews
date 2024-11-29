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

    $pdo = getPDOConnection();

    // Consultar a tabela responses
    $stmt = $pdo->query("SELECT * FROM public.responses");
    $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($responses)) {
        throw new Exception("Nenhuma resposta encontrada na tabela responses.", 404);
    }

    echo json_encode([
        "status" => "success",
        "data" => $responses
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        "message" => $e->getMessage(),
        "error" => $e->getCode() === 500 ? "Erro interno no servidor" : $e->getMessage()
    ]);
}
?>