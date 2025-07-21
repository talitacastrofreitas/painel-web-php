<?php


// --- CARREGAMENTO DAS VARIÁVEIS DE AMBIENTE ---
require_once __DIR__ . '/../vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// --- DEFINIÇÃO DE CONSTANTES GLOBAIS ---

define('BASE_PATH', __DIR__ . '/../');
define('BASE_URL', $_ENV['BASE_URL']);
define('PAINEL_TV_API_KEY_SECRET', $_ENV['PAINEL_TV_API_KEY_SECRET']);



// --- CONEXÃO COM O BANCO DE DADOS ---
$serverName = $_ENV['DB_HOST'];
$connectionOptions = [
    "Database" => $_ENV['DB_DATABASE'],
    "Uid" => $_ENV['DB_USERNAME'],
    "PWD" => $_ENV['DB_PASSWORD']
];

try {
    $conn = new PDO("sqlsrv:Server=$serverName;Database={$connectionOptions['Database']}", $connectionOptions['Uid'], $connectionOptions['PWD']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro crítico na conexão com o banco de dados. Por favor, contate o administrador.");
}
