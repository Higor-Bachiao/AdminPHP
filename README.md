# Admin Clínicas

Painel administrativo em PHP para o app de clínicas — projeto da disciplina Linguagem de Programação VIII (UNIFEG).

**Etapa 1:** cadastro, login com sessão, área protegida e logout.

## Como rodar no XAMPP

1. Copie a pasta do projeto para `C:\xampp\htdocs\AdminPHP` (no macOS: `/Applications/XAMPP/htdocs/AdminPHP`).
2. Inicie o **Apache** e o **MySQL** no painel do XAMPP.
3. Abra o phpMyAdmin (`http://localhost/phpmyadmin`), vá na aba **SQL** e rode o conteúdo de `banco.sql`.
4. Acesse `http://localhost/AdminPHP`.

Se o seu MySQL tiver senha ou outra porta, crie `config/local.php`:

```php
<?php
return ["usuario" => "root", "senha" => "sua_senha"];
```

### Sem XAMPP (servidor embutido do PHP)

```bash
mysql -u root < banco.sql
php -S localhost:8000
```

## Usuário de teste

| Perfil | E-mail | Senha |
|---|---|---|
| admin | admin@clinica.com | admin123 |

Novos usuários criados em `cadastro.php` entram com perfil `usuario`.

## Estrutura

```
config/conexao.php     conexão PDO
includes/auth.php      sessão, exigirLogin(), exigirPerfil()
includes/usuarios.php  inserirUsuario(), buscarUsuarioPorEmail()
login.php              password_verify + session_regenerate_id
cadastro.php           password_hash
painel.php             página protegida
logout.php             encerra a sessão
banco.sql              tabela usuarios + admin inicial
```
