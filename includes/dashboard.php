<?php
// includes/dashboard.php
// Junta os números do dashboard do admin a partir das funções que já existem.
// O SQL continua em clinicas.php e usuarios.php; aqui só se monta o resumo.

require_once __DIR__ . "/clinicas.php";
require_once __DIR__ . "/usuarios.php";

function resumoDashboard(PDO $pdo): array
{
    $usuarios  = listarUsuarios($pdo);
    $admins    = count(array_filter($usuarios, fn($u) => $u["perfil"] === "admin"));
    $porCidade = clinicasPorCidade($pdo);

    return [
        "totais" => [
            "clinicas" => contarClinicas($pdo),
            "cidades"  => count($porCidade),
            "usuarios" => count($usuarios),
            "admins"   => $admins,
            "comuns"   => count($usuarios) - $admins,
        ],
        "por_cidade"       => $porCidade,
        "ultimas_clinicas" => ultimasClinicas($pdo, 5),
        "usuarios"         => $usuarios,
    ];
}
