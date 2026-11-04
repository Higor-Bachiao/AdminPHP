<?php
require_once "includes/auth.php";
exigirLogin();

// daqui para baixo, a página só roda para quem está logado

// Impede que o botão "voltar" mostre a página do cache após o logout
header("Cache-Control: no-store, no-cache, must-revalidate");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel · Admin Clínicas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="topo">
        <strong>Admin Clínicas</strong>
        <nav>
            <span>Olá, <?= e($_SESSION["nome"] ?? "usuário") ?>
                <small class="tag"><?= e($_SESSION["perfil"] ?? "") ?></small>
            </span>
            <a class="botao-sair" href="logout.php">Sair</a>
        </nav>
    </header>

    <main class="conteudo">
        <h1>Painel</h1>
        <p>Você está autenticado. As telas de gestão das clínicas entram aqui.</p>
    </main>
</body>
</html>
