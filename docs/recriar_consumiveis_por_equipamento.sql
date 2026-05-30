-- =========================================================
-- MEDICORE - Recriar tabela de consumiveis por equipamento
-- Base de dados: db1230404
-- Executar este script se a tabela consumiveis antiga estava ligada a salas.
-- =========================================================

USE db1230404;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS consumiveis;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE consumiveis (
  id_consumivel INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_equipamento INT UNSIGNED NOT NULL,
  id_fornecedor INT UNSIGNED NULL,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  categoria VARCHAR(80) NOT NULL,
  quantidade_atual INT UNSIGNED NOT NULL DEFAULT 0,
  unidade VARCHAR(30) NOT NULL DEFAULT 'unidades',
  stock_minimo INT UNSIGNED NOT NULL DEFAULT 0,
  lote VARCHAR(80),
  data_validade DATE,
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_consumivel_equipamento (id_equipamento),
  KEY idx_consumivel_fornecedor (id_fornecedor),
  KEY idx_consumivel_codigo (codigo),
  KEY idx_consumivel_categoria (categoria),
  KEY idx_consumivel_stock (quantidade_atual, stock_minimo),
  KEY idx_consumivel_isActive (isActive),
  CONSTRAINT fk_consumivel_equipamento
    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_consumivel_fornecedor
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exemplos opcionais, assumindo que os equipamentos ja existem com estes codigos.
-- INSERT INTO consumiveis
--   (id_equipamento, codigo, nome, categoria, quantidade_atual, unidade, stock_minimo, observacoes)
-- SELECT id_equipamento, 'CON-001', 'Eletrodos descartaveis ECG', 'Eletrodos', 120, 'unidades', 30, 'Consumivel usado em exames de ECG.'
-- FROM equipamentos
-- WHERE codigo = 'EQ-004';
