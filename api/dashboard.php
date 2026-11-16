<?php
// api/dashboard.php
// GET /api/dashboard.php   resumo do sistema (só admin)   200 | 401, 403, 405

require_once __DIR__ . "/../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Allow: GET");
    responder(405, ["erro" => "Método não permitido."]);
}

exigirPerfilApi("admin");   // 401 sem login, 403 para usuário comum

require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/dashboard.php";

responder(200, resumoDashboard($pdo));
