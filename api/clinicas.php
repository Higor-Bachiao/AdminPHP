<?php
// api/clinicas.php
// GET    /api/clinicas.php          listar          200 | 401
// GET    /api/clinicas.php?id=3     buscar          200 | 401, 404
// POST   /api/clinicas.php          criar           201 | 401, 400, 422
// PUT    /api/clinicas.php?id=3     atualizar       200 | 401, 400, 404, 422
// DELETE /api/clinicas.php?id=3     excluir (admin) 204 | 401, 403, 404

require_once __DIR__ . "/../includes/api.php";
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/clinicas.php";

exigirLoginApi();   // todo o endpoint exige login

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        if (!isset($_GET["id"])) {
            responder(200, listarClinicas($pdo));
        }

        $clinica = buscarClinica($pdo, exigirIdApi());
        if (!$clinica) {
            responder(404, ["erro" => "Clínica não encontrada."]);
        }
        responder(200, $clinica);

    case "POST":
        $dados = normalizarClinica(lerJson());
        $erros = validarClinica($pdo, $dados);
        if ($erros) {
            responder(422, ["erros" => $erros]);
        }

        $id = inserirClinica($pdo, $dados);
        responder(201, buscarClinica($pdo, $id));

    case "PUT":
        $id = exigirIdApi();
        if (!buscarClinica($pdo, $id)) {
            responder(404, ["erro" => "Clínica não encontrada."]);
        }

        $dados = normalizarClinica(lerJson());
        $erros = validarClinica($pdo, $dados, $id);
        if ($erros) {
            responder(422, ["erros" => $erros]);
        }

        atualizarClinica($pdo, $id, $dados);
        responder(200, buscarClinica($pdo, $id));

    case "DELETE":
        exigirPerfilApi("admin");   // só administrador exclui

        $id = exigirIdApi();
        if (!buscarClinica($pdo, $id)) {
            responder(404, ["erro" => "Clínica não encontrada."]);
        }

        excluirClinica($pdo, $id);
        responder(204);

    default:
        header("Allow: GET, POST, PUT, DELETE");
        responder(405, ["erro" => "Método não permitido."]);
}
