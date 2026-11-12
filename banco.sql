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

-- Tema do projeto: clínicas
CREATE TABLE IF NOT EXISTS clinicas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  cnpj CHAR(14) NOT NULL UNIQUE,          -- só dígitos
  email VARCHAR(150) NOT NULL,
  telefone VARCHAR(11) NOT NULL,          -- só dígitos (DDD + número)
  cidade VARCHAR(100) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Usuários de teste
-- admin@clinica.com / admin123  (perfil admin)
-- maria@clinica.com / user123   (perfil usuario)
INSERT IGNORE INTO usuarios (nome, email, senha_hash, perfil) VALUES
  ('Administrador', 'admin@clinica.com',
   '$2y$12$spgWfSrKVErBMEE.MeQ7v.syMF4qrDYqJ4bxixa5nQn.bmq1daT52', 'admin'),
  ('Maria Souza', 'maria@clinica.com',
   '$2y$12$N8eyLY49BCNK7mP9og0SIe858h3cY0eicAOHF9gNRA17FpbUePuNW', 'usuario');

-- Clínicas de exemplo
INSERT IGNORE INTO clinicas (nome, cnpj, email, telefone, cidade) VALUES
  ('Clínica Vida Plena',    '12345678000190', 'contato@vidaplena.com',  '3535511234', 'Guaxupé'),
  ('Centro Médico São José', '98765432000110', 'sjose@centromedico.com', '35999887766', 'São Sebastião do Paraíso'),
  ('Odonto Sorriso',        '11222333000144', 'ola@odontosorriso.com',  '1633221100', 'Franca');
