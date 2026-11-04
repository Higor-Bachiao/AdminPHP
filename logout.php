<?php
require_once "includes/auth.php";

// 1. Esvazia os dados da sessão
$_SESSION = [];

// 2. Remove o cookie de sessão do navegador
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}

// 3. Destrói a sessão no servidor
session_destroy();

// 4. Volta para o login
header("Location: login.php");
exit;
