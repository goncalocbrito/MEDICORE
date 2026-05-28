-- =========================================================
-- MEDICORE - Soft delete
-- Base de dados: db1230404
-- Acrescenta isActive às tabelas com remoção lógica.
-- =========================================================

USE db1230404;

-- Utilizadores
ALTER TABLE utilizadores
  ADD COLUMN isActive TINYINT(1) NOT NULL DEFAULT 1 AFTER observacoes,
  ADD INDEX idx_utilizador_isActive (isActive);

-- Localizações
ALTER TABLE localizacoes
  ADD COLUMN isActive TINYINT(1) NOT NULL DEFAULT 1 AFTER observacoes,
  ADD INDEX idx_localizacao_isActive (isActive);

-- Fornecedores
ALTER TABLE fornecedores
  ADD COLUMN isActive TINYINT(1) NOT NULL DEFAULT 1 AFTER observacoes,
  ADD INDEX idx_fornecedor_isActive (isActive);

-- Equipamentos
ALTER TABLE equipamentos
  ADD COLUMN isActive TINYINT(1) NOT NULL DEFAULT 1 AFTER observacoes,
  ADD INDEX idx_equipamento_isActive (isActive);

-- Acessórios
ALTER TABLE acessorios
  ADD COLUMN isActive TINYINT(1) NOT NULL DEFAULT 1 AFTER observacoes,
  ADD INDEX idx_acessorio_isActive (isActive);

-- Documentos
ALTER TABLE documentos
  ADD COLUMN isActive TINYINT(1) NOT NULL DEFAULT 1 AFTER observacoes,
  ADD INDEX idx_documento_isActive (isActive);

-- Garantias
ALTER TABLE garantias
  ADD COLUMN isActive TINYINT(1) NOT NULL DEFAULT 1 AFTER observacoes,
  ADD INDEX idx_garantia_isActive (isActive);

-- Contratos de manutenção
ALTER TABLE contratos_manutencao
  ADD COLUMN isActive TINYINT(1) NOT NULL DEFAULT 1 AFTER observacoes,
  ADD INDEX idx_contrato_isActive (isActive);

-- Pedidos de calibração/manutenção
ALTER TABLE pedidos_calibracao_manutencao
  ADD COLUMN isActive TINYINT(1) NOT NULL DEFAULT 1 AFTER observacoes,
  ADD INDEX idx_pedido_isActive (isActive);

