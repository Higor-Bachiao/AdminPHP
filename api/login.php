<?php
// api/login.php
// POST /api/login.php   corpo JSON {"email": "...", "senha": "..."}
// 200 com os dados do usuário (e o cookie PHPSESSID) | 401 | 422

require_once __DIR__ . "/../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    responder(405, ["erro" => "Método não permitido."]);
}

require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/usuarios.php";

// Aceita JSON (interface/Postman raw) ou formulário (x-www-form-urlencoded)
$ehJson = str_contains($_SERVER["CONTENT_TYPE"] ?? "", "application/json");
$d      = $ehJson ? lerJson() : $_POST;
$email = trim((string) ($d["email"] ?? ""));
$senha = (string) ($d["senha"] ?? "");

if ($email === "" || $senha === "") {
    responder(422, ["erros" => ["Informe e-mail e senha."]]);
}

$u = autenticar($pdo, $email, $senha);

if (!$u) {
    // Mesma mensagem genérica da página de login
    responder(401, ["erro" => "E-mail ou senha inválidos."]);
}

iniciarSessaoUsuario($u);

responder(200, [
    "id"     => (int) $u["id"],
    "nome"   => $u["nome"],
    "email"  => $u["email"],
    "perfil" => $u["perfil"],
]);
