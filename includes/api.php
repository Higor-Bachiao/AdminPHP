<?php
// includes/api.php
// Base de todos os endpoints: resposta em JSON, leitura do corpo e proteção.

require_once __DIR__ . "/auth.php";   // mesma sessão das páginas (aula 12)

// Em endpoint, nada de avisos do PHP misturados no JSON
ini_set("display_errors", "0");

// Qualquer exceção não tratada vira 500 em JSON, sem expor detalhes
set_exception_handler(function (Throwable $e): void {
    error_log("Erro na API: " . $e->getMessage());
    responder(500, ["erro" => "Erro interno no servidor."]);
});

function responder(int $status, $dados = null): void
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");

    if ($dados !== null) {
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Lê o corpo da requisição como JSON.
// Corpo vazio vira []; JSON malformado responde 400.
function lerJson(): array
{
    $texto = file_get_contents("php://input");

    if (trim($texto) === "") {
        return [];
    }

    $dados = json_decode($texto, true);

    if (!is_array($dados)) {
        responder(400, ["erro" => "JSON inválido no corpo da requisição."]);
    }

    return $dados;
}

// Autenticação: sem login responde 401 (a API não redireciona)
function exigirLoginApi(): void
{
    if (empty($_SESSION["usuario_id"])) {
        responder(401, ["erro" => "Faça login."]);
    }
}

// Autorização: logado mas sem o perfil responde 403
function exigirPerfilApi(string $perfil): void
{
    exigirLoginApi();

    if (($_SESSION["perfil"] ?? "") !== $perfil) {
        responder(403, ["erro" => "Sem permissão."]);
    }
}

// Lê o ?id= da URL; ausente ou inválido responde 400
function exigirIdApi(): int
{
    $id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

    if ($id === false) {
        responder(400, ["erro" => "Informe um id válido na URL (?id=)."]);
    }

    return $id;
}
