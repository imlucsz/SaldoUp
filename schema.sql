CREATE DATABASE IF NOT EXISTS saldoup
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE saldoup;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

INSERT IGNORE INTO usuarios (id, nome, senha)
VALUES (1, 'lucas', '$2y$10$7cE8L4HfPyGGptFAWnsNSufg479C./6pItLFT9CCj5pSFbAHsTnPq');

CREATE TABLE IF NOT EXISTS transacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data DATE NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    usuario_id INT NOT NULL DEFAULT 1,
    INDEX idx_transacoes_usuario_id (usuario_id)
);

SET @add_usuario_id = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'transacoes'
       AND COLUMN_NAME = 'usuario_id') = 0,
    'ALTER TABLE transacoes ADD COLUMN usuario_id INT NOT NULL DEFAULT 1',
    'SELECT 1'
);
PREPARE add_usuario_id_statement FROM @add_usuario_id;
EXECUTE add_usuario_id_statement;
DEALLOCATE PREPARE add_usuario_id_statement;