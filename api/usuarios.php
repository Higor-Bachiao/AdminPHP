<?php
// api/usuarios.php
// PUT /api/usuarios.php?id=3   corpo {"perfil": "admin"}   tornar admin (só admin)
//     200 | 400, 401, 403, 404, 405, 422
//
// Só promove. Rebaixar não é oferecido: o perfil fica guardado na sessão no
// login, então um admin rebaixado continuaria admin até sair do sistema.
// Pelo mesmo motivo, quem é promovido vira admin a partir do próximo login.

require_once __DIR__ . "/../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    header("Allow: PUT");
    responder(405, ["erro" => "Método não permitido."]);
}

exigirPerfilApi("admin");   // 401 sem login, 403 para usuário comum

require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/usuarios.php";

$id = exigirIdApi();

if (!buscarUsuarioPorId($pdo, $id)) {
    responder(404, ["erro" => "Usuário não encontrado."]);
}

$perfil = lerJson()["perfil"] ?? null;

if ($perfil !== "admin") {
    responder(422, ["erros" => ["Perfil inválido: só é possível tornar o usuário admin."]]);
}

alterarPerfilUsuario($pdo, $id, "admin");   // já ser admin não é erro: o PUT é idempotente

responder(200, buscarUsuarioPorId($pdo, $id));
