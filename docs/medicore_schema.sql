-- =========================================================
-- MEDICORE - Script SQL MySQL
-- Base de dados para inventario hospitalar de equipamentos medicos
-- =========================================================

USE db1230404;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS historico_estados_equipamento;
DROP TABLE IF EXISTS pedidos_calibracao_manutencao;
DROP TABLE IF EXISTS contratos_manutencao;
DROP TABLE IF EXISTS garantias;
DROP TABLE IF EXISTS documentos;
DROP TABLE IF EXISTS equipamento_fornecedor;
DROP TABLE IF EXISTS fornecedor_tipo_assoc;
DROP TABLE IF EXISTS acessorios;
DROP TABLE IF EXISTS equipamentos;
DROP TABLE IF EXISTS conteudos_publicos;
DROP TABLE IF EXISTS utilizador_permissoes;
DROP TABLE IF EXISTS fornecedores;
DROP TABLE IF EXISTS localizacoes;
DROP TABLE IF EXISTS procedimento_estados;
DROP TABLE IF EXISTS procedimento_tipos;
DROP TABLE IF EXISTS documento_tipos;
DROP TABLE IF EXISTS fornecedor_tipos;
DROP TABLE IF EXISTS criticidades;
DROP TABLE IF EXISTS estados_equipamento;
DROP TABLE IF EXISTS tipos_entrada;
DROP TABLE IF EXISTS categorias_equipamento;
DROP TABLE IF EXISTS menus_sistema;
DROP TABLE IF EXISTS utilizadores;
DROP TABLE IF EXISTS utilizador_tipos;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- UTILIZADORES, TIPOS E PERMISSOES
-- =========================================================

CREATE TABLE utilizador_tipos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE,
  descricao VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menus_sistema (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(50) NOT NULL UNIQUE,
  nome VARCHAR(100) NOT NULL,
  icone VARCHAR(100),
  url VARCHAR(255),
  ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE utilizadores (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  cartao_cidadao VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  telefone VARCHAR(30),
  servico VARCHAR(120),
  tipo_id INT UNSIGNED NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'Ativo',
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  data_ativacao DATE,
  validade_acesso DATE,
  ultimo_acesso DATETIME,
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_utilizador_isActive (isActive),
  CONSTRAINT fk_utilizadores_tipo
    FOREIGN KEY (tipo_id) REFERENCES utilizador_tipos(id)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE utilizador_permissoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  utilizador_id INT UNSIGNED NOT NULL,
  menu_id INT UNSIGNED NOT NULL,
  pode_aceder TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uk_utilizador_menu (utilizador_id, menu_id),
  CONSTRAINT fk_perm_utilizador
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_perm_menu
    FOREIGN KEY (menu_id) REFERENCES menus_sistema(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- LOCALIZACOES
-- =========================================================

CREATE TABLE localizacoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  departamento VARCHAR(150) NOT NULL,
  edificio VARCHAR(100) NOT NULL,
  piso VARCHAR(30) NOT NULL,
  sala VARCHAR(80) NOT NULL,
  tipo_espaco VARCHAR(80) NOT NULL,
  responsavel VARCHAR(150),
  estado VARCHAR(30) NOT NULL DEFAULT 'Ativa',
  capacidade_equipamentos INT UNSIGNED,
  equipamentos_previstos INT UNSIGNED,
  permite_equipamentos_criticos TINYINT(1) NOT NULL DEFAULT 0,
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_localizacao_departamento (departamento),
  KEY idx_localizacao_estado (estado),
  KEY idx_localizacao_isActive (isActive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABELAS DE APOIO DOS EQUIPAMENTOS
-- =========================================================

CREATE TABLE categorias_equipamento (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE,
  descricao VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tipos_entrada (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE estados_equipamento (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE criticidades (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE,
  descricao VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- FORNECEDORES
-- =========================================================

CREATE TABLE fornecedor_tipos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fornecedores (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome_empresa VARCHAR(180) NOT NULL,
  nif VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL,
  telefone VARCHAR(30),
  website VARCHAR(255),
  pessoa_contacto VARCHAR(150),
  telefone_contacto VARCHAR(30),
  email_contacto VARCHAR(150),
  cargo_contacto VARCHAR(100),
  morada VARCHAR(255),
  codigo_postal VARCHAR(20),
  localidade VARCHAR(100),
  pais VARCHAR(80) NOT NULL DEFAULT 'Portugal',
  estado VARCHAR(30) NOT NULL DEFAULT 'Ativo',
  contrato VARCHAR(100),
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_fornecedor_nome (nome_empresa),
  KEY idx_fornecedor_estado (estado),
  KEY idx_fornecedor_localidade (localidade),
  KEY idx_fornecedor_isActive (isActive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fornecedor_tipo_assoc (
  fornecedor_id INT UNSIGNED NOT NULL,
  tipo_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (fornecedor_id, tipo_id),
  CONSTRAINT fk_fta_fornecedor
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_fta_tipo
    FOREIGN KEY (tipo_id) REFERENCES fornecedor_tipos(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- EQUIPAMENTOS E ACESSORIOS
-- =========================================================

CREATE TABLE equipamentos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  designacao VARCHAR(150) NOT NULL,
  categoria_id INT UNSIGNED NOT NULL,
  fabricante VARCHAR(120) NOT NULL,
  modelo VARCHAR(120) NOT NULL,
  numero_serie VARCHAR(120) NOT NULL,
  ano_fabrico YEAR,
  data_aquisicao DATE,
  custo_aquisicao DECIMAL(12,2),
  tipo_entrada_id INT UNSIGNED,
  estado_id INT UNSIGNED NOT NULL,
  criticidade_id INT UNSIGNED NOT NULL,
  localizacao_id INT UNSIGNED NOT NULL,
  operacional TINYINT(1) NOT NULL DEFAULT 1,
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_eq_serie_fabricante_modelo (fabricante, modelo, numero_serie),
  KEY idx_equipamento_codigo (codigo),
  KEY idx_equipamento_designacao (designacao),
  KEY idx_equipamento_estado (estado_id),
  KEY idx_equipamento_localizacao (localizacao_id),
  KEY idx_equipamento_isActive (isActive),
  CONSTRAINT fk_eq_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias_equipamento(id)
    ON UPDATE CASCADE,
  CONSTRAINT fk_eq_tipo_entrada
    FOREIGN KEY (tipo_entrada_id) REFERENCES tipos_entrada(id)
    ON UPDATE CASCADE,
  CONSTRAINT fk_eq_estado
    FOREIGN KEY (estado_id) REFERENCES estados_equipamento(id)
    ON UPDATE CASCADE,
  CONSTRAINT fk_eq_criticidade
    FOREIGN KEY (criticidade_id) REFERENCES criticidades(id)
    ON UPDATE CASCADE,
  CONSTRAINT fk_eq_localizacao
    FOREIGN KEY (localizacao_id) REFERENCES localizacoes(id)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE acessorios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  equipamento_id INT UNSIGNED NOT NULL,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  tipo VARCHAR(100),
  numero_serie VARCHAR(120),
  estado_id INT UNSIGNED NOT NULL,
  requer_manutencao TINYINT(1) NOT NULL DEFAULT 0,
  requer_calibracao TINYINT(1) NOT NULL DEFAULT 0,
  proxima_intervencao DATE,
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_acessorio_equipamento (equipamento_id),
  KEY idx_acessorio_estado (estado_id),
  KEY idx_acessorio_isActive (isActive),
  CONSTRAINT fk_acessorio_equipamento
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_acessorio_estado
    FOREIGN KEY (estado_id) REFERENCES estados_equipamento(id)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE equipamento_fornecedor (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  equipamento_id INT UNSIGNED NOT NULL,
  fornecedor_id INT UNSIGNED NOT NULL,
  tipo_id INT UNSIGNED NOT NULL,
  data_inicio DATE,
  data_fim DATE,
  observacoes TEXT,
  UNIQUE KEY uk_eq_fornecedor_tipo (equipamento_id, fornecedor_id, tipo_id),
  CONSTRAINT fk_ef_equipamento
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_ef_fornecedor
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_ef_tipo
    FOREIGN KEY (tipo_id) REFERENCES fornecedor_tipos(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DOCUMENTOS, GARANTIAS E CONTRATOS
-- =========================================================

CREATE TABLE documento_tipos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documentos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo_id INT UNSIGNED NOT NULL,
  nome VARCHAR(180) NOT NULL,
  data_documento DATE,
  data_validade DATE,
  equipamento_id INT UNSIGNED,
  acessorio_id INT UNSIGNED,
  fornecedor_id INT UNSIGNED,
  ficheiro_nome VARCHAR(255),
  caminho_ficheiro VARCHAR(255),
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  criado_por INT UNSIGNED,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_documento_tipo (tipo_id),
  KEY idx_documento_equipamento (equipamento_id),
  KEY idx_documento_fornecedor (fornecedor_id),
  KEY idx_documento_isActive (isActive),
  CONSTRAINT fk_doc_tipo
    FOREIGN KEY (tipo_id) REFERENCES documento_tipos(id)
    ON UPDATE CASCADE,
  CONSTRAINT fk_doc_equipamento
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_doc_acessorio
    FOREIGN KEY (acessorio_id) REFERENCES acessorios(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_doc_fornecedor
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_doc_utilizador
    FOREIGN KEY (criado_por) REFERENCES utilizadores(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE garantias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  equipamento_id INT UNSIGNED NOT NULL,
  fornecedor_id INT UNSIGNED,
  documento_id INT UNSIGNED,
  data_inicio DATE NOT NULL,
  data_fim DATE NOT NULL,
  entidade_responsavel VARCHAR(150),
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_garantia_isActive (isActive),
  CONSTRAINT chk_garantia_datas CHECK (data_fim >= data_inicio),
  CONSTRAINT fk_garantia_equipamento
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_garantia_fornecedor
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_garantia_documento
    FOREIGN KEY (documento_id) REFERENCES documentos(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contratos_manutencao (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  equipamento_id INT UNSIGNED,
  fornecedor_id INT UNSIGNED NOT NULL,
  documento_id INT UNSIGNED,
  numero_contrato VARCHAR(80),
  tipo_contrato VARCHAR(100) NOT NULL,
  periodicidade VARCHAR(80),
  data_inicio DATE,
  data_fim DATE,
  estado VARCHAR(30) NOT NULL DEFAULT 'Ativo',
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_contrato_isActive (isActive),
  CONSTRAINT chk_contrato_datas CHECK (data_fim IS NULL OR data_inicio IS NULL OR data_fim >= data_inicio),
  CONSTRAINT fk_contrato_equipamento
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_contrato_fornecedor
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_contrato_documento
    FOREIGN KEY (documento_id) REFERENCES documentos(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- CALIBRACOES E MANUTENCOES
-- =========================================================

CREATE TABLE procedimento_tipos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE procedimento_estados (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedidos_calibracao_manutencao (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  equipamento_id INT UNSIGNED NOT NULL,
  acessorio_id INT UNSIGNED,
  procedimento_tipo_id INT UNSIGNED NOT NULL,
  fornecedor_id INT UNSIGNED,
  tecnico_responsavel_id INT UNSIGNED,
  data_pedido DATE NOT NULL,
  data_prevista DATE,
  data_conclusao DATE,
  estado_id INT UNSIGNED NOT NULL,
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_pedido_estado (estado_id),
  KEY idx_pedido_procedimento (procedimento_tipo_id),
  KEY idx_pedido_data_prevista (data_prevista),
  KEY idx_pedido_isActive (isActive),
  CONSTRAINT fk_pedido_equipamento
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_acessorio
    FOREIGN KEY (acessorio_id) REFERENCES acessorios(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_tipo
    FOREIGN KEY (procedimento_tipo_id) REFERENCES procedimento_tipos(id)
    ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_fornecedor
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_tecnico
    FOREIGN KEY (tecnico_responsavel_id) REFERENCES utilizadores(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_estado
    FOREIGN KEY (estado_id) REFERENCES procedimento_estados(id)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE historico_estados_equipamento (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  equipamento_id INT UNSIGNED NOT NULL,
  estado_anterior_id INT UNSIGNED,
  estado_novo_id INT UNSIGNED NOT NULL,
  utilizador_id INT UNSIGNED,
  data_alteracao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  observacoes TEXT,
  CONSTRAINT fk_hist_equipamento
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_hist_estado_anterior
    FOREIGN KEY (estado_anterior_id) REFERENCES estados_equipamento(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_hist_estado_novo
    FOREIGN KEY (estado_novo_id) REFERENCES estados_equipamento(id)
    ON UPDATE CASCADE,
  CONSTRAINT fk_hist_utilizador
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- BACKOFFICE DA PAGINA PUBLICA
-- =========================================================

CREATE TABLE conteudos_publicos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  chave VARCHAR(100) NOT NULL UNIQUE,
  secao VARCHAR(80) NOT NULL,
  titulo VARCHAR(180),
  texto TEXT,
  imagem VARCHAR(255),
  url VARCHAR(255),
  ordem INT NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  atualizado_por INT UNSIGNED,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_conteudo_secao (secao),
  CONSTRAINT fk_conteudo_utilizador
    FOREIGN KEY (atualizado_por) REFERENCES utilizadores(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DADOS BASE
-- =========================================================

INSERT INTO utilizador_tipos (nome, descricao) VALUES
('Administrador', 'Acesso total ao sistema e ao backoffice.'),
('Engenheiro', 'Gestao tecnica de equipamentos, fornecedores, localizacoes e manutencoes.'),
('Enfermeiro', 'Consulta de equipamentos e informacao operacional.');

INSERT INTO menus_sistema (codigo, nome, icone, url) VALUES
('dashboard', 'Dashboard Tecnico', 'fa-chart-line', 'private/index.html'),
('equipamentos', 'Equipamentos', 'fa-stethoscope', 'private/views/equipamentos/lista_equipamentos.html'),
('calibracoes_manutencoes', 'Calibracoes/Manutencoes', 'fa-screwdriver-wrench', 'private/views/calibracao_manutencao/calibracao_manutencao.html'),
('localizacoes', 'Localizacoes', 'fa-location-dot', 'private/views/localizacoes/lista_localizacoes.html'),
('fornecedores', 'Fornecedores', 'fa-truck-medical', 'private/views/fornecedores/lista_fornecedores.html'),
('utilizadores', 'Utilizadores', 'fa-user', 'private/views/utilizadores/lista_utilizadores.html'),
('backoffice', 'Backoffice', 'fa-pen-to-square', 'private/views/backoffice/backoffice.html');

INSERT INTO categorias_equipamento (nome, descricao) VALUES
('Monitorizacao', 'Equipamentos de monitorizacao de sinais vitais.'),
('Suporte de Vida', 'Equipamentos criticos de suporte clinico.'),
('Emergencia', 'Equipamentos usados em resposta a emergencia.'),
('Terapia', 'Equipamentos de apoio terapeutico.'),
('Laboratorio', 'Equipamentos usados em analises e exames laboratoriais.');

INSERT INTO tipos_entrada (nome) VALUES
('Compra'),
('Doacao'),
('Aluguer'),
('Emprestimo');

INSERT INTO estados_equipamento (nome) VALUES
('Ativo'),
('Em manutencao'),
('Inativo'),
('Em calibracao'),
('Em quarentena'),
('Avariado'),
('Abatido');

INSERT INTO criticidades (nome, descricao) VALUES
('Baixa', 'Equipamento de baixo impacto clinico.'),
('Media', 'Equipamento importante, mas com alternativas disponiveis.'),
('Alta', 'Equipamento relevante para a atividade clinica.'),
('Suporte de vida', 'Equipamento critico para suporte direto ao doente.');

INSERT INTO fornecedor_tipos (nome) VALUES
('Fabricante'),
('Distribuidor'),
('Assistencia tecnica'),
('Fornecedor de consumiveis'),
('Calibracao');

INSERT INTO documento_tipos (nome) VALUES
('Manual de utilizador'),
('Manual de servico'),
('Certificado de calibracao'),
('Contrato de manutencao'),
('Fatura ou guia de aquisicao'),
('Declaracao de conformidade'),
('Relatorio tecnico'),
('Documento de garantia');

INSERT INTO procedimento_tipos (nome) VALUES
('Calibracao'),
('Manutencao preventiva'),
('Manutencao corretiva');

INSERT INTO procedimento_estados (nome) VALUES
('Aguarda fornecedor'),
('Em manutencao'),
('Em calibracao'),
('Efetuada'),
('Cancelada');

-- =========================================================
-- DADOS DE EXEMPLO ALINHADOS COM O FRONTEND
-- =========================================================

INSERT INTO utilizadores (
  codigo, nome, cartao_cidadao, email, telefone, servico, tipo_id,
  estado, username, password_hash, data_ativacao, observacoes
) VALUES
('USR-001', 'Ana Martins', '12345678', 'ana.martins@medicore.pt', '+351 220 000 100', 'Administracao',
 (SELECT id FROM utilizador_tipos WHERE nome = 'Administrador'), 'Ativo', 'ana.martins', 'trocar_no_backend', '2026-01-01',
 'Utilizadora com permissoes administrativas completas.'),
('USR-002', 'Goncalo Brito', '87654321', 'g.brito@medicore.pt', '+351 220 000 200', 'Engenharia Biomedica',
 (SELECT id FROM utilizador_tipos WHERE nome = 'Engenheiro'), 'Ativo', 'g.brito', 'trocar_no_backend', '2026-01-01',
 'Engenheiro responsavel pela area tecnica.');

INSERT INTO utilizador_permissoes (utilizador_id, menu_id, pode_aceder)
SELECT u.id, m.id, 1
FROM utilizadores u
CROSS JOIN menus_sistema m
WHERE u.codigo IN ('USR-001', 'USR-002');

INSERT INTO localizacoes (
  codigo, departamento, edificio, piso, sala, tipo_espaco,
  responsavel, estado, capacidade_equipamentos, permite_equipamentos_criticos, observacoes
) VALUES
('LOC-001', 'Unidade de Cuidados Intensivos', 'Edificio A', '2', 'Sala 201', 'UCI', 'Enf. Maria Costa', 'Ativa', 10, 1,
 'Area critica com equipamentos de suporte de vida e monitorizacao continua.'),
('LOC-002', 'Urgencia', 'Edificio B', '0', 'Sala 1', 'Urgencia', 'Dr. Joao Martins', 'Ativa', 12, 1,
 'Sala de atendimento urgente com equipamentos de suporte clinico.'),
('LOC-003', 'Bloco Operatorio', 'Edificio C', '1', 'BO-02', 'Bloco Operatorio', 'Enf. Ricardo Silva', 'Ativa', 8, 1,
 'Espaco cirurgico com equipamentos criticos.');

INSERT INTO fornecedores (
  codigo, nome_empresa, nif, email, telefone, website, pessoa_contacto,
  localidade, estado, contrato, observacoes
) VALUES
('FOR-001', 'Philips Medical Systems', '509123456', 'suporte@philips-med.pt', '+351 220 000 111',
 'https://www.philips.pt/healthcare', 'Joao Pereira', 'Porto', 'Ativo', 'Sim',
 'Fabricante e suporte tecnico de equipamentos de monitorizacao.'),
('FOR-002', 'MedSupply Portugal', '514987321', 'comercial@medsupply.pt', '+351 221 234 567',
 'https://www.medsupply.pt', 'Marta Santos', 'Lisboa', 'Ativo', 'Sim',
 'Distribuidor comercial e apoio a manutencao preventiva.'),
('FOR-003', 'Biomedical Solutions', '516111222', 'assistencia@biomedical.pt', '+351 239 000 222',
 'https://www.biomedical.pt', 'Rui Almeida', 'Coimbra', 'Ativo', 'Sim',
 'Empresa de assistencia tecnica hospitalar.');

INSERT INTO fornecedor_tipo_assoc (fornecedor_id, tipo_id)
SELECT f.id, t.id
FROM fornecedores f
JOIN fornecedor_tipos t ON
  (f.codigo = 'FOR-001' AND t.nome = 'Fabricante') OR
  (f.codigo = 'FOR-002' AND t.nome = 'Distribuidor') OR
  (f.codigo = 'FOR-003' AND t.nome = 'Assistencia tecnica');

INSERT INTO equipamentos (
  codigo, designacao, categoria_id, fabricante, modelo, numero_serie,
  ano_fabrico, data_aquisicao, custo_aquisicao, tipo_entrada_id,
  estado_id, criticidade_id, localizacao_id, operacional, observacoes
) VALUES
('EQ-001', 'Monitor Multiparametrico',
 (SELECT id FROM categorias_equipamento WHERE nome = 'Monitorizacao'), 'Philips', 'IntelliVue MX450', 'SN-MX450-2024',
 2023, '2024-01-15', 3500.00, (SELECT id FROM tipos_entrada WHERE nome = 'Compra'),
 (SELECT id FROM estados_equipamento WHERE nome = 'Ativo'), (SELECT id FROM criticidades WHERE nome = 'Alta'),
 (SELECT id FROM localizacoes WHERE codigo = 'LOC-001'), 1,
 'Equipamento essencial para monitorizacao continua de parametros vitais.'),
('EQ-002', 'Ventilador Pulmonar',
 (SELECT id FROM categorias_equipamento WHERE nome = 'Suporte de Vida'), 'Drager', 'Evita V300', 'SN-EV300-1198',
 2022, '2023-03-10', 18000.00, (SELECT id FROM tipos_entrada WHERE nome = 'Compra'),
 (SELECT id FROM estados_equipamento WHERE nome = 'Em manutencao'), (SELECT id FROM criticidades WHERE nome = 'Suporte de vida'),
 (SELECT id FROM localizacoes WHERE codigo = 'LOC-002'), 0,
 'Equipamento em intervencao tecnica por falha no sistema de ventilacao.'),
('EQ-003', 'Desfibrilhador',
 (SELECT id FROM categorias_equipamento WHERE nome = 'Emergencia'), 'Zoll', 'R Series', 'SN-ZOLL-8821',
 2021, '2022-06-20', 9200.00, (SELECT id FROM tipos_entrada WHERE nome = 'Compra'),
 (SELECT id FROM estados_equipamento WHERE nome = 'Avariado'), (SELECT id FROM criticidades WHERE nome = 'Alta'),
 (SELECT id FROM localizacoes WHERE codigo = 'LOC-003'), 0,
 'Equipamento sinalizado como avariado ate avaliacao tecnica.');

INSERT INTO acessorios (
  equipamento_id, codigo, nome, tipo, numero_serie, estado_id,
  requer_manutencao, requer_calibracao, proxima_intervencao, observacoes
) VALUES
((SELECT id FROM equipamentos WHERE codigo = 'EQ-001'), 'EQ-001.01', 'Sensor SpO2', 'Sensor', 'SPO2-4482',
 (SELECT id FROM estados_equipamento WHERE nome = 'Ativo'), 0, 1, '2026-09-12',
 'Sensor associado ao monitor multiparametrico.'),
((SELECT id FROM equipamentos WHERE codigo = 'EQ-001'), 'EQ-001.02', 'Cabo ECG 5 derivacoes', 'Cabo', 'ECG-5D-2024',
 (SELECT id FROM estados_equipamento WHERE nome = 'Ativo'), 1, 0, '2026-09-12',
 'Cabo ECG reutilizavel associado ao equipamento principal.');

INSERT INTO equipamento_fornecedor (equipamento_id, fornecedor_id, tipo_id, data_inicio, observacoes)
VALUES
((SELECT id FROM equipamentos WHERE codigo = 'EQ-001'), (SELECT id FROM fornecedores WHERE codigo = 'FOR-001'),
 (SELECT id FROM fornecedor_tipos WHERE nome = 'Fabricante'), '2024-01-15', 'Fabricante do equipamento.'),
((SELECT id FROM equipamentos WHERE codigo = 'EQ-001'), (SELECT id FROM fornecedores WHERE codigo = 'FOR-002'),
 (SELECT id FROM fornecedor_tipos WHERE nome = 'Distribuidor'), '2024-01-15', 'Fornecedor comercial.'),
((SELECT id FROM equipamentos WHERE codigo = 'EQ-002'), (SELECT id FROM fornecedores WHERE codigo = 'FOR-003'),
 (SELECT id FROM fornecedor_tipos WHERE nome = 'Assistencia tecnica'), '2023-03-10', 'Assistencia tecnica contratada.');

INSERT INTO documentos (
  tipo_id, nome, data_documento, data_validade, equipamento_id, ficheiro_nome, caminho_ficheiro, criado_por
) VALUES
((SELECT id FROM documento_tipos WHERE nome = 'Manual de utilizador'), 'Manual Monitor IntelliVue MX450', '2024-01-15', NULL,
 (SELECT id FROM equipamentos WHERE codigo = 'EQ-001'), 'manual_intellivue_mx450.pdf', 'uploads/documentos/manual_intellivue_mx450.pdf',
 (SELECT id FROM utilizadores WHERE codigo = 'USR-002')),
((SELECT id FROM documento_tipos WHERE nome = 'Certificado de calibracao'), 'Certificado de calibracao EQ-001', '2026-03-12', '2027-03-12',
 (SELECT id FROM equipamentos WHERE codigo = 'EQ-001'), 'certificado_eq001_2026.pdf', 'uploads/documentos/certificado_eq001_2026.pdf',
 (SELECT id FROM utilizadores WHERE codigo = 'USR-002'));

INSERT INTO garantias (
  equipamento_id, fornecedor_id, data_inicio, data_fim, entidade_responsavel, observacoes
) VALUES
((SELECT id FROM equipamentos WHERE codigo = 'EQ-001'), (SELECT id FROM fornecedores WHERE codigo = 'FOR-002'),
 '2024-01-20', '2027-01-20', 'MedSupply Portugal', 'Garantia comercial de 3 anos.');

INSERT INTO contratos_manutencao (
  equipamento_id, fornecedor_id, numero_contrato, tipo_contrato, periodicidade,
  data_inicio, data_fim, estado, observacoes
) VALUES
((SELECT id FROM equipamentos WHERE codigo = 'EQ-001'), (SELECT id FROM fornecedores WHERE codigo = 'FOR-002'),
 'CTR-2024-001', 'Manutencao preventiva anual', 'Semestral', '2024-01-20', '2027-01-20', 'Ativo',
 'Contrato associado ao monitor multiparametrico.');

INSERT INTO pedidos_calibracao_manutencao (
  codigo, equipamento_id, acessorio_id, procedimento_tipo_id, fornecedor_id,
  tecnico_responsavel_id, data_pedido, data_prevista, estado_id, observacoes
) VALUES
('PCM-001', (SELECT id FROM equipamentos WHERE codigo = 'EQ-001'), NULL,
 (SELECT id FROM procedimento_tipos WHERE nome = 'Manutencao preventiva'), (SELECT id FROM fornecedores WHERE codigo = 'FOR-002'),
 (SELECT id FROM utilizadores WHERE codigo = 'USR-002'), '2026-05-24', '2026-09-12',
 (SELECT id FROM procedimento_estados WHERE nome = 'Aguarda fornecedor'),
 'Pedido enviado para confirmacao de agenda.'),
('PCM-002', (SELECT id FROM equipamentos WHERE codigo = 'EQ-002'), NULL,
 (SELECT id FROM procedimento_tipos WHERE nome = 'Manutencao corretiva'), (SELECT id FROM fornecedores WHERE codigo = 'FOR-003'),
 (SELECT id FROM utilizadores WHERE codigo = 'USR-002'), '2026-05-24', '2026-05-28',
 (SELECT id FROM procedimento_estados WHERE nome = 'Em manutencao'),
 'Intervencao tecnica por falha no sistema de ventilacao.');

INSERT INTO conteudos_publicos (chave, secao, titulo, texto, imagem, url, ordem, ativo, atualizado_por)
VALUES
('hero_titulo', 'sobre', 'Gestao Inteligente do Inventario Hospitalar',
 'O MEDICORE e uma aplicacao web para registo, organizacao e acompanhamento de equipamentos medicos em contexto hospitalar.',
 'assets/img/MEDICORE_Official_Logo.png', NULL, 1, 1, (SELECT id FROM utilizadores WHERE codigo = 'USR-001')),
('contacto_email', 'contacto', 'Email', 'geral@medicore.pt', NULL, 'mailto:geral@medicore.pt', 1, 1,
 (SELECT id FROM utilizadores WHERE codigo = 'USR-001')),
('contacto_telefone', 'contacto', 'Telefone', '+351 9xx xxx xxx', NULL, NULL, 2, 1,
 (SELECT id FROM utilizadores WHERE codigo = 'USR-001'));
