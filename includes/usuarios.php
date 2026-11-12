<?php
// includes/usuarios.php
// Funções de acesso à tabela usuarios (PDO + prepared statements)

function inserirUsuario(PDO $pdo, string $nome, string $email, string $senha, string $perfil = "usuario"): int
{
    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, email, senha_hash, perfil)
            VALUES (:n, :e, :h, :p)";
    $st = $pdo->prepare($sql);
    $st->execute([
        ":n" => $nome,
        ":e" => $email,
        ":h" => $hash,
        ":p" => $perfil,
    ]);

    return (int) $pdo->lastInsertId();
}

function buscarUsuarioPorEmail(PDO $pdo, string $email): ?array
{
    $st = $pdo->prepare("SELECT id, nome, email, senha_hash, perfil
                         FROM usuarios WHERE email = :e LIMIT 1");
    $st->execute([":e" => $email]);
    $u = $st->fetch();

    return $u ?: null;
}

// Devolve o usuário se e-mail e senha conferem, ou null.
// Quem chama decide a mensagem — sempre genérica.
function autenticar(PDO $pdo, string $email, string $senha): ?array
{
    $u = $email !== "" ? buscarUsuarioPorEmail($pdo, $email) : null;

    if ($u && password_verify($senha, $u["senha_hash"])) {
        unset($u["senha_hash"]);
        return $u;
    }

    return null;
}

function emailJaCadastrado(PDO $pdo, string $email): bool
{
    $st = $pdo->prepare("SELECT 1 FROM usuarios WHERE email = :e LIMIT 1");
    $st->execute([":e" => $email]);

    return (bool) $st->fetchColumn();
}
