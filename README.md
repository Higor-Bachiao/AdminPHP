# Admin Clínicas

Painel administrativo em PHP para o app de clínicas — projeto da disciplina Linguagem de Programação VIII (UNIFEG).

- **Etapa 1:** cadastro, login com sessão, área protegida, logout e exclusão restrita ao admin (403).
- **Etapa 2:** API própria em JSON para as clínicas, protegida pela mesma sessão, com coleção do Postman.

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
login.php              password_verify + session_regenerate_id
cadastro.php           password_hash
painel.php             página protegida
clinicas.php           listagem protegida (exigirLogin)
excluir.php            exclusão restrita (exigirPerfil("admin") → 403)
logout.php             encerra a sessão
banco.sql              tabelas usuarios e clinicas + dados de exemplo
postman/               coleção do Postman exportada
```
