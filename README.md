# Admin Clínicas

Painel administrativo em PHP para o app de clínicas — projeto da disciplina Linguagem de Programação VIII (UNIFEG).

- **Etapa 1:** cadastro, login com sessão, área protegida, logout e exclusão restrita ao admin (403).
- **Etapa 2:** API própria em JSON para as clínicas, protegida pela mesma sessão, com coleção do Postman.
- **Etapa 3:** interface em `painel.php` + `js/app.js` que consome a API com `fetch`: lista, cadastro, edição e exclusão, tratando cada status.

## Como rodar no XAMPP

1. Copie a pasta do projeto para `C:\xampp\htdocs\AdminPHP` (no macOS: `/Applications/XAMPP/htdocs/AdminPHP`).
2. Inicie o **Apache** e o **MySQL** no painel do XAMPP.
3. Abra o phpMyAdmin (`http://localhost/phpmyadmin`), vá na aba **SQL** e rode o conteúdo de `banco.sql`.
4. Acesse `http://localhost/AdminPHP`.

Se o seu MySQL tiver senha ou outra porta, crie `config/local.php` (fica fora do git):

```php
<?php
return ["usuario" => "root", "senha" => "sua_senha"];
```

### Sem XAMPP (servidor embutido do PHP)

```bash
mysql -u root -p < banco.sql
php -S localhost:8000
```

## Usuários de teste

| Perfil | E-mail | Senha |
|---|---|---|
| admin | admin@clinica.com | admin123 |
| usuario | maria@clinica.com | user123 |

Novos usuários criados em `cadastro.php` entram com perfil `usuario`.

## API

Base: `http://localhost/AdminPHP/api`. Todas as respostas são JSON (`Content-Type: application/json; charset=utf-8`).
A autenticação usa a sessão: depois do login, o cliente reenvia o cookie `PHPSESSID` automaticamente.

### Quadro de endpoints

| Método | URL | Ação | Sucesso | Erros possíveis |
|---|---|---|---|---|
| POST | `/api/login.php` | Entrar | 200 | 401, 405, 422 |
| POST | `/api/logout.php` | Sair | 204 | 405 |
| GET | `/api/me.php` | Usuário logado | 200 | 401 |
| GET | `/api/clinicas.php` | Listar clínicas | 200 | 401 |
| GET | `/api/clinicas.php?id=3` | Buscar clínica | 200 | 400, 401, 404 |
| POST | `/api/clinicas.php` | Criar clínica | 201 | 400, 401, 422 |
| PUT | `/api/clinicas.php?id=3` | Atualizar clínica | 200 | 400, 401, 404, 422 |
| DELETE | `/api/clinicas.php?id=3` | Excluir clínica (**admin**) | 204 | 400, 401, 403, 404 |
| outro | `/api/clinicas.php` | — | — | 405 |

### Significado dos status

| Código | Quando |
|---|---|
| 200 | Consulta, atualização ou login bem-sucedidos |
| 201 | Clínica criada (corpo traz a clínica com o `id` novo) |
| 204 | Exclusão ou logout feitos (sem corpo) |
| 400 | JSON malformado ou `id` ausente/inválido na URL |
| 401 | Sem login, ou e-mail/senha inválidos no login |
| 403 | Logado, mas sem permissão (ex.: usuário comum tentando excluir) |
| 404 | Clínica inexistente |
| 405 | Método que o endpoint não trata |
| 422 | Dados inválidos — corpo traz `{"erros": [...]}` com todos os problemas |
| 500 | Erro interno ou banco fora do ar (sem detalhes técnicos) |

### Corpos

**Login** — `POST /api/login.php` (JSON ou `x-www-form-urlencoded`)

```json
{ "email": "admin@clinica.com", "senha": "admin123" }
```

**Clínica** — `POST` e `PUT /api/clinicas.php` (todos os campos obrigatórios)

```json
{
  "nome": "Clínica Vida Plena",
  "cnpj": "12.345.678/0001-90",
  "email": "contato@vidaplena.com",
  "telefone": "(35) 3551-1234",
  "cidade": "Guaxupé"
}
```

Regras (em `validarClinica()`, usada pela API): nome até 150 caracteres, CNPJ com 14 dígitos e único,
e-mail válido, telefone com DDD (10 ou 11 dígitos), cidade até 100 caracteres.
CNPJ e telefone podem vir com máscara; são gravados só com dígitos.

**Resposta de uma clínica**

```json
{
  "id": 1,
  "nome": "Clínica Vida Plena",
  "cnpj": "12345678000190",
  "email": "contato@vidaplena.com",
  "telefone": "3535511234",
  "cidade": "Guaxupé",
  "criado_em": "2026-11-11 10:00:00"
}
```

### Testando no Postman

1. **Import** → `postman/AdminClinicas.postman_collection.json`.
2. Se a pasta não for `AdminPHP`, ajuste a variável `baseUrl` da coleção.
3. Clique na coleção → **Run collection**. As pastas rodam na ordem: sem login (401), usuário comum (403),
   CRUD do admin (200/201/204), casos de erro (400/404/405/422) e logout.

O cookie de sessão é guardado pelo Postman entre as requisições, como faria o navegador.

## Interface (Etapa 3)

Depois do login, `painel.php` é a tela principal. O HTML fica na página e todo o comportamento
fica em `js/app.js`, que chama `api/clinicas.php` com `fetch` e `async/await`.

- **Mesma origem:** a página e a API estão no mesmo host e na mesma porta (`http://localhost/AdminPHP/...`).
  Por isso não há CORS e o cookie `PHPSESSID` vai junto em toda chamada, sem configuração.
- **Duas camadas de proteção:** a página usa `exigirLogin()` (redireciona) e a API usa `exigirLoginApi()` /
  `exigirPerfilApi("admin")` (responde 401/403 em JSON).
- **Status antes do corpo:** o `fetch` só falha sem rede; 401, 403, 404 e 422 chegam como respostas
  normais. O `app.js` confere `resp.status` / `resp.ok` antes de chamar `resp.json()`.
- **textContent:** todo dado vindo da API entra na tela com `textContent` (nunca `innerHTML`). Um nome como
  `<b>Teste</b>` aparece escrito, sem ser interpretado como HTML.

### Estados da tela

| Situação | Status | O que a tela faz |
|---|---|---|
| Carregando | — | Mostra "Carregando..." até a resposta chegar |
| Lista com dados | 200 | Desenha a tabela e atualiza o total |
| Lista vazia | 200 e `[]` | Mostra "Nenhuma clínica cadastrada." |
| Clínica criada / atualizada | 201 / 200 | Limpa o formulário, recarrega a lista e mostra mensagem de sucesso |
| Dados inválidos | 422 | Lista os erros do servidor acima do formulário |
| Sem login / sessão expirada | 401 | Leva para `login.php` |
| Sem permissão | 403 | Mostra "Você não tem permissão." |
| Não encontrado | 404 | Mostra "Registro não existe." e recarrega a lista |
| Clínica excluída | 204 | Recarrega a lista e mostra "Clínica excluída." |
| Sem rede ou servidor | exceção | Mostra "Servidor indisponível." |

O botão **Excluir** só aparece para o admin, mas quem protege a exclusão é o 403 da API. Para conferir,
entre como `maria@clinica.com`, abra o F12 → Console e rode `excluir(1)`: a tela mostra
"Você não tem permissão." e a aba Network mostra o `DELETE` com status 403.

### Prints das telas

| | |
|---|---|
| ![Login](docs/prints/01-login.png) Login | ![Painel do admin](docs/prints/02-painel-admin.png) Painel do admin (200) |
| ![Erros 422](docs/prints/03-erros-422.png) Dados inválidos (422) | ![Criada 201](docs/prints/04-criada-201.png) Clínica criada (201) |
| ![Editando](docs/prints/05-editando.png) Edição (PUT 200) | ![Excluída 204](docs/prints/06-excluida-204.png) Clínica excluída (204) |
| ![Usuário 403](docs/prints/07-usuario-403.png) Usuário comum: sem botão excluir e 403 | ![Servidor indisponível](docs/prints/08-servidor-indisponivel.png) Servidor indisponível |

## Estrutura

```
api/
  clinicas.php         GET/POST/PUT/DELETE de clínicas
  login.php            login que responde JSON
  logout.php           logout que responde 204
  me.php               usuário da sessão
config/conexao.php     conexão PDO
includes/
  api.php              responder(), lerJson(), exigirLoginApi(), exigirPerfilApi()
  auth.php             sessão, exigirLogin(), exigirPerfil()
  clinicas.php         SQL e validação das clínicas (usado por páginas e API)
  usuarios.php         inserirUsuario(), buscarUsuarioPorEmail(), autenticar()
  topo.php             cabeçalho das páginas logadas
js/app.js              interface: fetch, estados da tela, formulário e exclusão
login.php              password_verify + session_regenerate_id
cadastro.php           password_hash
painel.php             interface protegida (exigirLogin) que consome a API
clinicas.php           listagem renderizada no servidor (Etapa 1)
excluir.php            exclusão restrita (exigirPerfil("admin") → 403)
logout.php             encerra a sessão
banco.sql              tabelas usuarios e clinicas + dados de exemplo
postman/               coleção do Postman exportada
docs/prints/           prints das telas
```
