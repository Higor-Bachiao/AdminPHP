<?php
// includes/auth.php
// Inicia a sessão (uma vez só) e concentra as regras de acesso.

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        "httponly" => true,   // JavaScript não lê o cookie de sessão
        "samesite" => "Lax",
        "secure"   => !empty($_SERVER["HTTPS"]),
    ]);
    session_start();
}

// Autenticação: quem é você?
function exigirLogin(): void
{
    if (empty($_SESSION["usuario_id"])) {
        header("Location: login.php");
        exit;
    }
}

// Autorização: você pode fazer isso?
function exigirPerfil(string $perfil): void
{
    exigirLogin();

    if (($_SESSION["perfil"] ?? "") !== $perfil) {
        http_response_code(403);
        die("Acesso negado.");
    }
}

// Grava o usuário na sessão após o login (usado pela página e pela API)
function iniciarSessaoUsuario(array $u): void
{
    session_regenerate_id(true);          // evita fixação de sessão
    $_SESSION["usuario_id"] = $u["id"];
    $_SESSION["nome"]       = $u["nome"];
    $_SESSION["perfil"]     = $u["perfil"]; // nunca a senha nem o hash
}

// Esvazia a sessão, apaga o cookie e destrói a sessão no servidor
function encerrarSessao(): void
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }

    session_destroy();
}

function usuarioLogado(): bool
{
    return !empty($_SESSION["usuario_id"]);
}

function ehAdmin(): bool
{
    return ($_SESSION["perfil"] ?? "") === "admin";
}

// Atalho para escapar saída HTML
function e(?string $valor): string
{
    return htmlspecialchars($valor ?? "", ENT_QUOTES, "UTF-8");
}
