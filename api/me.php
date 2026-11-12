<?php
// api/me.php
// GET /api/me.php   200 com o usuário da sessão | 401
// Útil para a interface saber quem está logado e qual o perfil.

require_once __DIR__ . "/../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Allow: GET");
    responder(405, ["erro" => "Método não permitido."]);
}

exigirLoginApi();

responder(200, [
    "id"     => (int) $_SESSION["usuario_id"],
    "nome"   => $_SESSION["nome"] ?? "",
    "perfil" => $_SESSION["perfil"] ?? "",
]);
