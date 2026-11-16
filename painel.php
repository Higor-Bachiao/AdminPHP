<?php
require_once "includes/auth.php";
exigirLogin();   // camada 1: a página se protege (a API se protege com exigirLoginApi)

// daqui para baixo, a página só roda para quem está logado

// Impede que o botão "voltar" mostre a página do cache após o logout
header("Cache-Control: no-store, no-cache, must-revalidate");

$titulo = "Painel";
require "includes/topo.php";
?>
    <!-- O perfil só decide o que a tela mostra; quem protege a exclusão é o 403 da API -->
    <main class="conteudo" id="app" data-perfil="<?= e($_SESSION["perfil"] ?? "") ?>">
        <h1>Painel</h1>

        <div class="cartoes">
            <div class="cartao">
                <span class="numero" id="total">–</span>
                <span>clínicas cadastradas</span>
            </div>
        </div>

        <section class="bloco">
            <h2 id="titulo-form">Nova clínica</h2>

            <ul id="erros-form" class="alerta erro lista-erros" hidden></ul>

            <form id="form" class="form-grade" novalidate>
                <div class="campo campo-largo">
                    <label for="nome">Nome</label>
                    <input id="nome" name="nome" maxlength="150" placeholder="Clínica Vida Plena">
                </div>
                <div class="campo">
                    <label for="cnpj">CNPJ</label>
                    <input id="cnpj" name="cnpj" maxlength="18" placeholder="12.345.678/0001-90">
                </div>
                <div class="campo">
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email" maxlength="150" placeholder="contato@clinica.com">
                </div>
                <div class="campo">
                    <label for="telefone">Telefone</label>
                    <input id="telefone" name="telefone" maxlength="15" placeholder="(35) 3551-1234">
                </div>
                <div class="campo">
                    <label for="cidade">Cidade</label>
                    <input id="cidade" name="cidade" maxlength="100" placeholder="Guaxupé">
                </div>
                <div class="acoes-form">
                    <button type="submit" id="salvar">Salvar</button>
                    <button type="button" id="cancelar" class="botao-secundario" hidden>Cancelar edição</button>
                </div>
            </form>
        </section>

        <section class="bloco">
            <h2>Clínicas</h2>

            <!-- Mensagens de estado: carregando, vazio, erro, sucesso -->
            <p id="msg" class="msg" role="status" aria-live="polite"></p>

            <div class="tabela-wrap" id="tabela" hidden>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Cidade</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="lista"></tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="js/app.js"></script>
</body>
</html>
