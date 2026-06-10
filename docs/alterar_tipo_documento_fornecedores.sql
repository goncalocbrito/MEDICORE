-- =========================================================
-- MEDICORE - Atualizar tipos de documentos dos fornecedores
-- Use este script se a tabela documentos_fornecedores ja existir.
-- =========================================================

-- 1) Permite temporariamente os valores antigos e os novos.
ALTER TABLE documentos_fornecedores
  MODIFY COLUMN tipo_documento ENUM(
    'Contrato de fornecimento',
    'Contrato de manutenção',
    'Contrato de manutencao',
    'Certificado',
    'Certificado tecnico',
    'Contrato de Fornecimento',
    'Contrato de Manutenção',
    'Contrato de Calibração',
    'Certificado Técnico',
    'Comprovativo fiscal',
    'Outro'
  ) NOT NULL;

-- 2) Converte registos antigos para os nomes finais.
UPDATE documentos_fornecedores
SET tipo_documento = 'Contrato de Fornecimento'
WHERE tipo_documento = 'Contrato de fornecimento';

UPDATE documentos_fornecedores
SET tipo_documento = 'Contrato de Manutenção'
WHERE tipo_documento IN ('Contrato de manutenção', 'Contrato de manutencao');

UPDATE documentos_fornecedores
SET tipo_documento = 'Certificado Técnico'
WHERE tipo_documento IN ('Certificado', 'Certificado tecnico');

-- 3) Fecha o ENUM apenas com os valores finais usados pelo frontend.
ALTER TABLE documentos_fornecedores
  MODIFY COLUMN tipo_documento ENUM(
    'Contrato de Fornecimento',
    'Contrato de Manutenção',
    'Contrato de Calibração',
    'Certificado Técnico',
    'Comprovativo fiscal',
    'Outro'
  ) NOT NULL;
