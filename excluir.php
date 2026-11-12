<?php
require_once "includes/auth.php";
exigirPerfil("admin");   // usuário comum recebe 403, mesmo digitando o endereço

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die("Método não permitido.");
}

require_once "config/conexao.php";
require_once "includes/clinicas.php";

$id = (int) ($_POST["id"] ?? 0);

if ($id > 0 && buscarClinica($pdo, $id)) {
    excluirClinica($pdo, $id);
}

header("Location: clinicas.php?msg=excluida");
exit;
