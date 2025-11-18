-- bancodedados.sql

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS petshop_db;
USE petshop_db;

-- tabela: usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1
);

-- tabela: categorias 
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
);

-- tabela: produtos 
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(200) NOT NULL,
    categoria_id INT NOT NULL,
    preco_venda DECIMAL(10,2) NOT NULL,
    estoque_atual INT DEFAULT 0,
    estoque_minimo INT DEFAULT 5,
    ativo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- tabela: movimentacoes 
CREATE TABLE movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo_movimentacao ENUM('ENTRADA', 'SAIDA') NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao DATE NOT NULL,
    estoque_anterior INT NOT NULL,
    estoque_posterior INT NOT NULL,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Inserir: usuarios
INSERT INTO usuarios (nome, email, senha, ativo) VALUES
('Administrador', 'admin@petshop.com', '$2y$10$fPJWOc0KWoFN6BdCw.yz2.4PRaxRtnSy4iHDxR2fjKI3pkBVymqna', 1),
('Gerente', 'gerente@petshop.com', '$2y$10$7Csw8NcBXKmEKQQEpoMjc.z4ONLpBy2NyGl5mseFCL7ZJUFrbjh3W', 1),
('Vendedor', 'vendedor@petshop.com', '$2y$10$tG/GxNkKc.3mIIsyCEb/YOucrGsmlcTbk2b.acrRJnIcEJN56oKmu', 1);

-- Inserir: categorias
INSERT INTO categorias (nome) VALUES
('Ração'),
('Medicamento'),
('Brinquedo'),
('Acessório');

-- Inserir: produtos
INSERT INTO produtos (codigo, nome, categoria_id, preco_venda, estoque_atual, estoque_minimo) VALUES
('RAC001', 'Ração Premium Cães Adultos 15kg', 1, 129.90, 0, 10),
('MED001', 'Antipulgas e Carrapatos', 2, 189.90, 0, 10),
('BRI001', 'Bolinha de Borracha Resistente', 3, 32.90, 0, 20),
('ACE001', 'Coleira Ajustável Nylon', 4, 24.90, 0, 15);

-- Inserir: movimentacoes
INSERT INTO movimentacoes (produto_id, usuario_id, tipo_movimentacao, quantidade, data_movimentacao, estoque_anterior, estoque_posterior) VALUES
(1, 1, 'ENTRADA', 50, '2025-11-01', 0, 50),
(2, 1, 'ENTRADA', 40, '2025-11-01', 0, 40),
(3, 2, 'ENTRADA', 25, '2025-11-02', 0, 25),
(4, 1, 'ENTRADA', 30, '2025-11-03', 0, 30),
(1, 3, 'SAIDA', 5, '2025-11-05', 50, 45),
(2, 3, 'SAIDA', 8, '2025-11-06', 40, 32),
(3, 2, 'SAIDA', 7, '2025-11-07', 25, 18),
(4, 3, 'SAIDA', 5, '2025-11-08', 30, 25);