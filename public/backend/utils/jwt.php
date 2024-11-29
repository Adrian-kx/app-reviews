<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validateJWT($token) {
    $secret_key = $_ENV['JWT_SECRET'] ?? 'default_secret_key';
    return JWT::decode($token, new Key($secret_key, 'HS256'));
}