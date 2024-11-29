<?php
require __DIR__ . '/../../../vendor/autoload.php';

function getPDOConnection() {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $db = $_ENV['DB_NAME'] ?? 'app-reviews';
    $user = $_ENV['DB_USER'] ?? 'clersonklaumannjunior';
    $pass = $_ENV['DB_PASSWORD'] ?? 'coxinha123';

    $dsn = "pgsql:host=$host;dbname=$db";
    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Erro ao conectar ao banco: " . $e->getMessage());
    }
}