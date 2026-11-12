<?php
// includes/topo.php — cabeçalho das páginas logadas
// Espera $titulo definido pela página.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo ?? "Admin Clínicas") ?> · Admin Clínicas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="topo">
        <nav class="menu">
            <strong>Admin Clínicas</strong>
            <a href="painel.php">Painel</a>
            <a href="clinicas.php">Clínicas</a>
        </nav>
        <nav>
            <span>Olá, <?= e($_SESSION["nome"] ?? "usuário") ?>
                <small class="tag"><?= e($_SESSION["perfil"] ?? "") ?></small>
            </span>
            <a class="botao-sair" href="logout.php">Sair</a>
        </nav>
    </header>
