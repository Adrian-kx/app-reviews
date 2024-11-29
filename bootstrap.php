<?php
require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

// Carregar o .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();