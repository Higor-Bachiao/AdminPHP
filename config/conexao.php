<?php
// config/conexao.php
// Padrão do XAMPP: usuário root sem senha.
// Para sobrescrever localmente, crie config/local.php retornando um array
// com as mesmas chaves (esse arquivo fica fora do git). A chave opcional
// "dsn" substitui a string de conexão inteira.

$config = [
    "host"    => "127.0.0.1",
    "porta"   => 3306,
    "banco"   => "clinica_admin",
    "usuario" => "root",
    "senha"   => "",
];

if (file_exists(__DIR__ . "/local.php")) {
    $config = array_merge($config, require __DIR__ . "/local.php");
}

$dsn = $config["dsn"]
    ?? "mysql:host={$config['host']};port={$config['porta']};dbname={$config['banco']};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $config["usuario"], $config["senha"], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Não expõe detalhes da conexão para o usuário
    error_log("Erro de conexão: " . $e->getMessage());

    // Dentro da API a resposta precisa ser JSON
    if (function_exists("responder")) {
        responder(500, ["erro" => "Erro ao conectar ao banco de dados."]);
    }

    http_response_code(500);
    die("Erro ao conectar ao banco de dados.");
}
