<?php
require_once "includes/auth.php";
exigirLogin();

require_once "config/conexao.php";
require_once "includes/clinicas.php";

header("Cache-Control: no-store, no-cache, must-revalidate");

$clinicas = listarClinicas($pdo);
$msg      = $_GET["msg"] ?? null;
$titulo   = "Clínicas";
require "includes/topo.php";

function formatarCnpj(string $c): string
{
    return preg_replace("/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/", "$1.$2.$3/$4-$5", $c);
}

function formatarTelefone(string $t): string
{
    return preg_replace("/^(\d{2})(\d{4,5})(\d{4})$/", "($1) $2-$3", $t);
}
?>
    <main class="conteudo">
        <h1>Clínicas</h1>

        <?php if ($msg === "excluida"): ?>
            <div class="alerta sucesso">Clínica excluída.</div>
        <?php endif; ?>

        <?php if (!$clinicas): ?>
            <p>Nenhuma clínica cadastrada.</p>
        <?php else: ?>
            <div class="tabela-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Cidade</th>
                            <?php if (ehAdmin()): ?><th></th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clinicas as $c): ?>
                            <tr>
                                <td><?= e($c["nome"]) ?></td>
                                <td><?= e(formatarCnpj($c["cnpj"])) ?></td>
                                <td><?= e($c["email"]) ?></td>
                                <td><?= e(formatarTelefone($c["telefone"])) ?></td>
                                <td><?= e($c["cidade"]) ?></td>
                                <?php if (ehAdmin()): ?>
                                    <td>
                                        <!-- Esconder o botão não protege: excluir.php confere o perfil no servidor -->
                                        <form method="post" action="excluir.php">
                                            <input type="hidden" name="id" value="<?= (int) $c["id"] ?>">
                                            <button type="submit" class="botao-perigo">Excluir</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
