CREATE DATABASE IF NOT EXISTS portal_academia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portal_academia;

CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(80) NOT NULL,
    cpf BIGINT NULL,
    sexo VARCHAR(1) NULL,
    email VARCHAR(80) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('aluno','personal','admin') NOT NULL DEFAULT 'aluno',
    descricao VARCHAR(255) NULL,
    telefone VARCHAR(20) NULL,
    data_nascimento DATE NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo','inativo','suspenso') DEFAULT 'ativo'
);

-- Tabela de planos
CREATE TABLE planos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao VARCHAR(255),
    preco DECIMAL(10,2) NOT NULL,
    duracao INT COMMENT 'duração em dias',
    inclui_personal BOOLEAN DEFAULT FALSE,
    status ENUM('ativo','inativo') DEFAULT 'ativo'
);

-- Tabela de matriculas
CREATE TABLE matriculas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plano_id INT NOT NULL,
    data_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_fim DATETIME NULL,
    status ENUM('ativa','trancada','cancelada','expirada') DEFAULT 'ativa',
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (plano_id) REFERENCES planos(id) ON DELETE RESTRICT
);

-- Tabela de agenda (slots)
CREATE TABLE agenda (
    id_agenda INT PRIMARY KEY AUTO_INCREMENT,
    personal_id INT NOT NULL,
    data_hora DATETIME NOT NULL,
    duracao_minutos INT DEFAULT 60,
    status ENUM('disponivel','agendado','cancelado','concluido') DEFAULT 'disponivel',
    aluno_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (personal_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de pagamentos
CREATE TABLE pagamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    matricula_id INT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    metodo_pagamento VARCHAR(50),
    status ENUM('pendente','pago','cancelado') DEFAULT 'pendente',
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE SET NULL
);

CREATE INDEX idx_usuarios_tipo ON usuarios(tipo);
CREATE INDEX idx_agenda_personal ON agenda(personal_id);

