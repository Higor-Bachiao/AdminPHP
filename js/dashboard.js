// js/dashboard.js
// Dashboard do admin: lê api/dashboard.php com fetch e desenha os números.
// Mesmas regras do app.js: primeiro o status, depois o corpo, e dados só com textContent.

const URL_DASHBOARD = "api/dashboard.php";

const $ = (seletor) => document.querySelector(seletor);

function mensagem(texto, tipo = "") {
    const p = $("#msg");
    p.textContent = texto;
    p.className = "msg" + (tipo ? " " + tipo : "");
}

// "2026-11-16 21:12:08" -> "16/11/2026"
function formatarData(texto) {
    const [data] = String(texto ?? "").split(" ");
    const [ano, mes, dia] = data.split("-");
    return dia ? `${dia}/${mes}/${ano}` : texto;
}

function linha(tbody, valores) {
    const tr = document.createElement("tr");
    for (const valor of valores) {
        const td = document.createElement("td");
        td.textContent = valor;   // se vier uma tag, ela aparece escrita, não executa
        tr.appendChild(td);
    }
    tbody.appendChild(tr);
    return tr;
}

function linhaVazia(tbody, texto, colunas) {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    td.colSpan = colunas;
    td.textContent = texto;
    tr.appendChild(td);
    tbody.appendChild(tr);
}

// Gráfico de barras simples: a largura de cada barra é proporcional ao total
function mostrarPorCidade(lista) {
    const ul = $("#por-cidade");
    ul.innerHTML = "";   // só limpa; os itens entram com textContent

    if (lista.length === 0) {
        const li = document.createElement("li");
        li.textContent = "Nenhuma clínica cadastrada.";
        ul.appendChild(li);
        return;
    }

    const maior = Math.max(...lista.map((c) => Number(c.total)));

    for (const c of lista) {
        const li = document.createElement("li");

        const nome = document.createElement("span");
        nome.className = "barra-nome";
        nome.textContent = c.cidade;

        const trilho = document.createElement("div");
        trilho.className = "barra-trilho";
        const barra = document.createElement("div");
        barra.className = "barra";
        barra.style.width = (Number(c.total) / maior) * 100 + "%";
        trilho.appendChild(barra);

        const valor = document.createElement("span");
        valor.className = "barra-valor";
        valor.textContent = c.total;

        li.append(nome, trilho, valor);
        ul.appendChild(li);
    }
}

function mostrar(d) {
    $("#total-clinicas").textContent = d.totais.clinicas;
    $("#total-cidades").textContent = d.totais.cidades;
    $("#total-usuarios").textContent = d.totais.usuarios;
    $("#total-perfis").textContent = `${d.totais.admins} / ${d.totais.comuns}`;

    mostrarPorCidade(d.por_cidade);

    const ultimas = $("#ultimas");
    ultimas.innerHTML = "";
    for (const c of d.ultimas_clinicas) {
        linha(ultimas, [c.nome, c.cidade, formatarData(c.criado_em)]);
    }
    if (d.ultimas_clinicas.length === 0) {
        linhaVazia(ultimas, "Nenhuma clínica cadastrada.", 3);
    }

    const usuarios = $("#usuarios");
    usuarios.innerHTML = "";
    for (const u of d.usuarios) {
        const perfil = u.perfil === "admin" ? "Administrador" : "Usuário comum";
        const tr = linha(usuarios, [u.nome, u.email, perfil, formatarData(u.criado_em)]);

        const acoes = document.createElement("td");
        acoes.className = "acoes";
        if (u.perfil !== "admin") {
            const botao = document.createElement("button");
            botao.type = "button";
            botao.className = "botao-secundario";
            botao.textContent = "Tornar admin";
            botao.onclick = () => tornarAdmin(u.id, u.nome);
            acoes.appendChild(botao);
        }
        tr.appendChild(acoes);
    }
}

// PUT /api/usuarios.php?id= — 200, 401, 403, 404, 422
async function tornarAdmin(id, nome = "este usuário") {
    if (!confirm(`Tornar ${nome} administrador?`)) return;

    try {
        const resp = await fetch(`api/usuarios.php?id=${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ perfil: "admin" }),
        });

        if (resp.status === 401) {
            location.href = "login.php";
            return;
        }

        if (resp.status === 200) {
            const u = await resp.json();
            await carregar();                     // recarregar mostra o estado real
            // O perfil fica na sessão: só muda quando a pessoa entrar de novo
            mensagem(`${u.nome} agora é administrador (vale a partir do próximo login).`, "sucesso");
        } else if (resp.status === 403) {
            mensagem("Você não tem permissão.", "erro");
        } else if (resp.status === 404) {
            await carregar();
            mensagem("Usuário não existe.", "erro");
        } else if (resp.status === 422) {
            const r = await resp.json();
            mensagem(r.erros.join(" "), "erro");
        } else {
            mensagem("Erro no servidor (" + resp.status + "). Tente novamente.", "erro");
        }
    } catch (erro) {
        mensagem("Servidor indisponível.", "erro");
    }
}

// Estados: carregando, dados, sem login (401), sem permissão (403), erro e sem rede
async function carregar() {
    mensagem("Carregando...");

    try {
        const resp = await fetch(URL_DASHBOARD);

        if (resp.status === 401) {               // sessão expirada: volta ao login
            location.href = "login.php";
            return;
        }
        if (resp.status === 403) {
            $("#dash").hidden = true;
            mensagem("Você não tem permissão.", "erro");
            return;
        }
        if (!resp.ok) {
            $("#dash").hidden = true;
            mensagem("Erro no servidor (" + resp.status + "). Tente novamente.", "erro");
            return;
        }

        const dados = await resp.json();          // só lê o corpo depois do status
        mostrar(dados);
        $("#dash").hidden = false;
        mensagem("");
    } catch (erro) {
        // Sem rede, servidor desligado ou resposta que não é JSON
        $("#dash").hidden = true;
        mensagem("Servidor indisponível.", "erro");
    }
}

carregar();
