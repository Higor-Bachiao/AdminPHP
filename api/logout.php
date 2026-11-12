<?php
// api/logout.php
// POST /api/logout.php   204 (encerra a sessão, logado ou não)

require_once __DIR__ . "/../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    responder(405, ["erro" => "Método não permitido."]);
}

encerrarSessao();
responder(204);
