-- Banco do painel administrativo das clínicas
-- Rodar no phpMyAdmin (aba SQL) ou: mysql -u root < banco.sql

CREATE DATABASE IF NOT EXISTS clinica_admin
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE clinica_admin;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  perfil VARCHAR(20) NOT NULL DEFAULT 'usuario',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Administrador inicial
-- e-mail: admin@clinica.com | senha: admin123
INSERT IGNORE INTO usuarios (nome, email, senha_hash, perfil) VALUES
  ('Administrador', 'admin@clinica.com',
   '$2y$12$spgWfSrKVErBMEE.MeQ7v.syMF4qrDYqJ4bxixa5nQn.bmq1daT52', 'admin');
