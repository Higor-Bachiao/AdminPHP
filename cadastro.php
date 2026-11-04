<?php
require_once "includes/auth.php";

if (usuarioLogado()) {
    header("Location: painel.php");
    exit;
}

$erros = [];
$nome  = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "config/conexao.php";
    require_once "includes/usuarios.php";

    $nome      = trim($_POST["nome"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $senha     = $_POST["senha"] ?? "";
    $confirmar = $_POST["confirmar"] ?? "";

    if ($nome === "") {
        $erros[] = "Informe o nome.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Informe um e-mail válido.";
    }
    if (strlen($senha) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres.";
    }
    if ($senha !== $confirmar) {
        $erros[] = "As senhas não conferem.";
    }

    if (!$erros && emailJaCadastrado($pdo, $email)) {
        $erros[] = "Não foi possível concluir o cadastro com esse e-mail.";
    }

    if (!$erros) {
        // Todo cadastro público entra como "usuario"; admin só pelo banco
        inserirUsuario($pdo, $nome, $email, $senha);
        header("Location: login.php?cadastrado=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro · Admin Clínicas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth">
    <main class="card">
        <h1>Criar conta</h1>
        <p class="sub">Preencha os dados para se cadastrar.</p>

        <?php if ($erros): ?>
            <div class="alerta erro">
                <?php foreach ($erros as $msg): ?>
                    <div><?= e($msg) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="cadastro.php">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" value="<?= e($nome) ?>" maxlength="100" required autofocus>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="150" required>

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" minlength="6" required>

            <label for="confirmar">Confirmar senha</label>
            <input type="password" id="confirmar" name="confirmar" minlength="6" required>

            <button type="submit">Cadastrar</button>
        </form>

        <p class="rodape">Já tem conta? <a href="login.php">Entrar</a></p>
    </main>
</body>
</html>
