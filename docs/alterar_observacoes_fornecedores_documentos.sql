-- =========================================================
-- MEDICORE - Ajuste das observacoes dos fornecedores
-- Coloca as observacoes na tabela fornecedores e remove-as
-- dos documentos do fornecedor.
-- =========================================================

ALTER TABLE fornecedores
  ADD COLUMN observacoes VARCHAR(500) NULL AFTER pais;

ALTER TABLE documentos_fornecedores
  DROP COLUMN observacoes;


ALTER TABLE documentos_fornecedores
  MODIFY COLUMN tipo_documento ENUM(
    'Contrato de Fornecimento',
    'Contrato de Manutenção',
    'Contrato de Calibração',
    'Certificado Técnico',
    'Comprovativo fiscal',
    'Outro'
  ) NOT NULL;
