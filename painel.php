<?php
require_once "includes/auth.php";
exigirLogin();

// daqui para baixo, a página só roda para quem está logado

require_once "config/conexao.php";
require_once "includes/clinicas.php";

// Impede que o botão "voltar" mostre a página do cache após o logout
header("Cache-Control: no-store, no-cache, must-revalidate");

$total  = contarClinicas($pdo);
$titulo = "Painel";
require "includes/topo.php";
?>
    <main class="conteudo">
        <h1>Painel</h1>
        <div class="cartoes">
            <a class="cartao" href="clinicas.php">
                <span class="numero"><?= $total ?></span>
                <span>clínicas cadastradas</span>
            </a>
        </div>
    </main>
</body>
</html>
