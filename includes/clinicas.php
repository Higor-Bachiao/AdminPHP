<?php
// includes/clinicas.php
// Funções de acesso à tabela clinicas (PDO + prepared statements).
// Usadas tanto pelas páginas quanto pela API: o SQL fica só aqui.

const CAMPOS_CLINICA = "id, nome, cnpj, email, telefone, cidade, criado_em";

function listarClinicas(PDO $pdo): array
{
    return $pdo->query("SELECT " . CAMPOS_CLINICA . " FROM clinicas ORDER BY nome")->fetchAll();
}

function contarClinicas(PDO $pdo): int
{
    return (int) $pdo->query("SELECT COUNT(*) FROM clinicas")->fetchColumn();
}

function buscarClinica(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare("SELECT " . CAMPOS_CLINICA . " FROM clinicas WHERE id = :id");
    $st->execute([":id" => $id]);
    $c = $st->fetch();

    return $c ?: null;
}

function inserirClinica(PDO $pdo, array $c): int
{
    $sql = "INSERT INTO clinicas (nome, cnpj, email, telefone, cidade)
            VALUES (:nome, :cnpj, :email, :telefone, :cidade)";
    $st = $pdo->prepare($sql);
    $st->execute([
        ":nome"     => $c["nome"],
        ":cnpj"     => $c["cnpj"],
        ":email"    => $c["email"],
        ":telefone" => $c["telefone"],
        ":cidade"   => $c["cidade"],
    ]);

    return (int) $pdo->lastInsertId();
}

function atualizarClinica(PDO $pdo, int $id, array $c): void
{
    $sql = "UPDATE clinicas
            SET nome = :nome, cnpj = :cnpj, email = :email,
                telefone = :telefone, cidade = :cidade
            WHERE id = :id";
    $st = $pdo->prepare($sql);
    $st->execute([
        ":nome"     => $c["nome"],
        ":cnpj"     => $c["cnpj"],
        ":email"    => $c["email"],
        ":telefone" => $c["telefone"],
        ":cidade"   => $c["cidade"],
        ":id"       => $id,
    ]);
}

function excluirClinica(PDO $pdo, int $id): void
{
    $st = $pdo->prepare("DELETE FROM clinicas WHERE id = :id");
    $st->execute([":id" => $id]);
}

function cnpjEmUso(PDO $pdo, string $cnpj, ?int $ignorarId = null): bool
{
    $st = $pdo->prepare("SELECT id FROM clinicas WHERE cnpj = :cnpj");
    $st->execute([":cnpj" => $cnpj]);
    $id = $st->fetchColumn();

    return $id !== false && (int) $id !== $ignorarId;
}

// Recebe os dados brutos (formulário ou JSON) e devolve só os campos
// conhecidos, já limpos: espaços removidos, CNPJ e telefone só com dígitos.
function normalizarClinica(array $d): array
{
    $texto   = fn($v) => is_scalar($v) ? trim((string) $v) : "";
    $digitos = fn($v) => preg_replace("/\D/", "", $texto($v));

    return [
        "nome"     => $texto($d["nome"] ?? ""),
        "cnpj"     => $digitos($d["cnpj"] ?? ""),
        "email"    => $texto($d["email"] ?? ""),
        "telefone" => $digitos($d["telefone"] ?? ""),
        "cidade"   => $texto($d["cidade"] ?? ""),
    ];
}

// Regras de validação em um lugar só: a página e a API chamam esta função.
// $idAtual é o id da clínica sendo editada (para não acusar o próprio CNPJ).
function validarClinica(PDO $pdo, array $c, ?int $idAtual = null): array
{
    $erros = [];

    if ($c["nome"] === "" || mb_strlen($c["nome"]) > 150) {
        $erros[] = "Nome é obrigatório (até 150 caracteres).";
    }
    if (strlen($c["cnpj"]) !== 14) {
        $erros[] = "CNPJ deve ter 14 dígitos.";
    } elseif (cnpjEmUso($pdo, $c["cnpj"], $idAtual)) {
        $erros[] = "CNPJ já cadastrado.";
    }
    if (!filter_var($c["email"], FILTER_VALIDATE_EMAIL)) {
        $erros[] = "E-mail inválido.";
    }
    if (strlen($c["telefone"]) < 10 || strlen($c["telefone"]) > 11) {
        $erros[] = "Telefone deve ter DDD + número (10 ou 11 dígitos).";
    }
    if ($c["cidade"] === "" || mb_strlen($c["cidade"]) > 100) {
        $erros[] = "Cidade é obrigatória (até 100 caracteres).";
    }

    return $erros;
}
