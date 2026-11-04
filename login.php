<?php
require_once "includes/auth.php";

// Quem já está logado não precisa ver o login de novo
if (usuarioLogado()) {
    header("Location: painel.php");
    exit;
}

$erro  = null;
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "config/conexao.php";
    require_once "includes/usuarios.php";

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    $u = $email !== "" ? buscarUsuarioPorEmail($pdo, $email) : null;

    if ($u && password_verify($senha, $u["senha_hash"])) {
        session_regenerate_id(true);          // evita fixação de sessão
        $_SESSION["usuario_id"] = $u["id"];
        $_SESSION["nome"]       = $u["nome"];
        $_SESSION["perfil"]     = $u["perfil"]; // nunca a senha nem o hash
        header("Location: painel.php");
        exit;
    }

    // Mensagem genérica: não revela se o e-mail existe
    $erro = "E-mail ou senha inválidos.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar · Admin Clínicas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth">
    <main class="card">
        <h1>Admin Clínicas</h1>
        <p class="sub">Entre com sua conta para acessar o painel.</p>

        <?php if (isset($_GET["cadastrado"])): ?>
            <div class="alerta sucesso">Cadastro realizado. Faça o login.</div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alerta erro"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>

            <button type="submit">Entrar</button>
        </form>

        <p class="rodape">Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
    </main>
</body>
</html>
