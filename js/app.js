// js/app.js
// Etapa 3 — a interface consome a API de clínicas com fetch.
// Regra de ouro: primeiro o status, depois o corpo (a mesma ordem do Postman).

// Caminho relativo: mesma origem da página, então não há CORS
// e o cookie da sessão (PHPSESSID) vai junto automaticamente.
const URL_API = "api/clinicas.php";

const $ = (seletor) => document.querySelector(seletor);

const ehAdmin = $("#app").dataset.perfil === "admin";
let editandoId = null;   // null = criando; número = editando (PUT)

// ---------------------------------------------------------------------------
// Mensagens na tela (sempre com textContent, nunca innerHTML com dados)
// ---------------------------------------------------------------------------

function mensagem(texto, tipo = "") {
    const p = $("#msg");
    p.textContent = texto;
    p.className = "msg" + (tipo ? " " + tipo : "");
}

function mostrarErrosForm(erros) {
    const ul = $("#erros-form");
    ul.innerHTML = "";   // só limpa; os itens entram com textContent
    for (const erro of erros) {
        const li = document.createElement("li");
        li.textContent = erro;
        ul.appendChild(li);
    }
    ul.hidden = erros.length === 0;
}

// ---------------------------------------------------------------------------
// fetch + 401
// ---------------------------------------------------------------------------

// Faz a requisição e trata o 401 em um lugar só: sessão expirada ou sem
// login leva para login.php. Devolve null nesse caso.
async function requisitar(url, opcoes = {}) {
    const resp = await fetch(url, opcoes);

    if (resp.status === 401) {
        location.href = "login.php";
        return null;
    }

    return resp;
}

// Mensagem padrão para os status de erro que a API pode devolver
function textoDoStatus(status) {
    switch (status) {
        case 400: return "Requisição inválida.";
        case 403: return "Você não tem permissão.";
        case 404: return "Registro não existe.";
        case 405: return "Ação não permitida.";
        default:  return "Erro no servidor (" + status + "). Tente novamente.";
    }
}

// ---------------------------------------------------------------------------
// Listar (GET) — estados: carregando, lista, vazio, erro
// ---------------------------------------------------------------------------

async function carregar() {
    mensagem("Carregando...");

    try {
        const resp = await requisitar(URL_API);
        if (!resp) return;                       // 401: já redirecionou

        if (!resp.ok) {                          // 403, 500... chegam como resposta normal
            $("#tabela").hidden = true;
            mensagem(textoDoStatus(resp.status), "erro");
            return;
        }

        const clinicas = await resp.json();      // só lê o corpo depois do status
        mostrar(clinicas);

        if (clinicas.length === 0) {
            mensagem("Nenhuma clínica cadastrada.");
        } else {
            mensagem("");
        }
    } catch (erro) {
        // Sem rede, servidor desligado ou resposta que não é JSON
        $("#tabela").hidden = true;
        $("#total").textContent = "–";
        mensagem("Servidor indisponível.", "erro");
    }
}

function formatarCnpj(c) {
    return c.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
}

function formatarTelefone(t) {
    return t.replace(/^(\d{2})(\d{4,5})(\d{4})$/, "($1) $2-$3");
}

function celula(tr, texto) {
    const td = document.createElement("td");
    td.textContent = texto;   // se vier uma tag no nome, ela aparece escrita, não executa
    tr.appendChild(td);
}

function mostrar(clinicas) {
    const tbody = $("#lista");
    tbody.innerHTML = "";

    for (const c of clinicas) {
        const tr = document.createElement("tr");

        celula(tr, c.nome);
        celula(tr, formatarCnpj(c.cnpj));
        celula(tr, c.email);
        celula(tr, formatarTelefone(c.telefone));
        celula(tr, c.cidade);

        const acoes = document.createElement("td");
        acoes.className = "acoes";

        const editar = document.createElement("button");
        editar.type = "button";
        editar.className = "botao-secundario";
        editar.textContent = "Editar";
        editar.onclick = () => iniciarEdicao(c);
        acoes.appendChild(editar);

        // Esconder o botão melhora a tela; quem protege é o 403 do servidor
        if (ehAdmin) {
            const excluirBtn = document.createElement("button");
            excluirBtn.type = "button";
            excluirBtn.className = "botao-perigo";
            excluirBtn.textContent = "Excluir";
            excluirBtn.onclick = () => excluir(c.id, c.nome);
            acoes.appendChild(excluirBtn);
        }

        tr.appendChild(acoes);
        tbody.appendChild(tr);
    }

    $("#tabela").hidden = clinicas.length === 0;
    $("#total").textContent = clinicas.length;
}

// ---------------------------------------------------------------------------
// Criar (POST) e atualizar (PUT) — 201/200, 422, 404
// ---------------------------------------------------------------------------

function lerFormulario() {
    return {
        nome: $("#nome").value,
        cnpj: $("#cnpj").value,
        email: $("#email").value,
        telefone: $("#telefone").value,
        cidade: $("#cidade").value,
    };
}

async function salvar(dados) {
    const criando = editandoId === null;
    const url = criando ? URL_API : `${URL_API}?id=${editandoId}`;

    $("#salvar").disabled = true;

    try {
        const resp = await requisitar(url, {
            method: criando ? "POST" : "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(dados),
        });
        if (!resp) return;

        if (resp.status === 201 || resp.status === 200) {
            const c = await resp.json();
            cancelarEdicao();
            await carregar();
            mensagem(criando
                ? `Clínica "${c.nome}" cadastrada.`
                : `Clínica "${c.nome}" atualizada.`, "sucesso");
        } else if (resp.status === 422) {
            // Erros de validação do servidor, perto do formulário
            const r = await resp.json();
            mostrarErrosForm(r.erros);
        } else if (resp.status === 404) {
            // Editando um registro que outra pessoa excluiu
            cancelarEdicao();
            mostrarErrosForm(["Registro não existe."]);
            carregar();
        } else {
            mostrarErrosForm([textoDoStatus(resp.status)]);
        }
    } catch (erro) {
        mostrarErrosForm(["Servidor indisponível."]);
    } finally {
        $("#salvar").disabled = false;
    }
}

function iniciarEdicao(c) {
    editandoId = c.id;
    $("#nome").value = c.nome;
    $("#cnpj").value = formatarCnpj(c.cnpj);
    $("#email").value = c.email;
    $("#telefone").value = formatarTelefone(c.telefone);
    $("#cidade").value = c.cidade;

    $("#titulo-form").textContent = "Editar clínica";
    $("#salvar").textContent = "Salvar alterações";
    $("#cancelar").hidden = false;
    mostrarErrosForm([]);
    $("#nome").focus();
}

function cancelarEdicao() {
    editandoId = null;
    $("#form").reset();
    $("#titulo-form").textContent = "Nova clínica";
    $("#salvar").textContent = "Salvar";
    $("#cancelar").hidden = true;
    mostrarErrosForm([]);
}

// ---------------------------------------------------------------------------
// Excluir (DELETE) — 204, 403, 404
// ---------------------------------------------------------------------------

async function excluir(id, nome = "esta clínica") {
    if (!confirm(`Excluir ${nome}?`)) return;

    try {
        const resp = await requisitar(`${URL_API}?id=${id}`, { method: "DELETE" });
        if (!resp) return;

        if (resp.status === 204) {
            if (editandoId === id) cancelarEdicao();
            await carregar();                 // recarregar mostra o estado real
            mensagem("Clínica excluída.", "sucesso");
        } else if (resp.status === 403) {
            mensagem("Você não tem permissão.", "erro");
        } else if (resp.status === 404) {
            mensagem("Registro não existe.", "erro");
            carregar();
        } else {
            mensagem(textoDoStatus(resp.status), "erro");
        }
    } catch (erro) {
        mensagem("Servidor indisponível.", "erro");
    }
}

// ---------------------------------------------------------------------------
// Eventos
// ---------------------------------------------------------------------------

$("#form").addEventListener("submit", async (ev) => {
    ev.preventDefault();   // não recarrega a página: quem envia é o fetch
    mostrarErrosForm([]);
    await salvar(lerFormulario());
});

$("#cancelar").addEventListener("click", cancelarEdicao);

carregar();
