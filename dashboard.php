<?php
require_once "includes/auth.php";
exigirPerfil("admin");   // camada 1: usuário comum recebe 403 (a API também confere)

// Impede que o botão "voltar" mostre a página do cache após o logout
header("Cache-Control: no-store, no-cache, must-revalidate");

$titulo = "Dashboard";
require "includes/topo.php";
?>
    <main class="conteudo">
        <h1>Dashboard</h1>

        <!-- Mensagens de estado: carregando, erro, sem permissão -->
        <p id="msg" class="msg" role="status" aria-live="polite"></p>

        <div id="dash" hidden>
            <div class="cartoes">
                <div class="cartao">
                    <span class="numero" id="total-clinicas">–</span>
                    <span>clínicas cadastradas</span>
                </div>
                <div class="cartao">
                    <span class="numero" id="total-cidades">–</span>
                    <span>cidades atendidas</span>
                </div>
                <div class="cartao">
                    <span class="numero" id="total-usuarios">–</span>
                    <span>usuários no sistema</span>
                </div>
                <div class="cartao">
                    <span class="numero" id="total-perfis">–</span>
                    <span>administradores / comuns</span>
                </div>
            </div>

            <section class="bloco">
                <h2>Clínicas por cidade</h2>
                <ul id="por-cidade" class="barras"></ul>
            </section>

            <section class="bloco">
                <h2>Últimas clínicas cadastradas</h2>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Cidade</th>
                                <th>Cadastrada em</th>
                            </tr>
                        </thead>
                        <tbody id="ultimas"></tbody>
                    </table>
                </div>
            </section>

            <section class="bloco">
                <h2>Usuários</h2>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Perfil</th>
                                <th>Desde</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="usuarios"></tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
</body>
</html>
