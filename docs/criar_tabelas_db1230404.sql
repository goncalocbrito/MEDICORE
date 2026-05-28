-- =========================================================
-- MEDICORE - Criacao de tabelas
-- Base de dados: db1230404
-- Este script cria apenas as tabelas. Nao contem DROP nem INSERT.
-- =========================================================

USE db1230404;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- UTILIZADORES E PERMISSOES
-- =========================================================

CREATE TABLE IF NOT EXISTS utilizador_tipos (
  id_tipo_utilizador INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE,
  descricao VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menus_sistema (
  id_menu INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(50) NOT NULL UNIQUE,
  nome VARCHAR(100) NOT NULL,
  icone VARCHAR(100),
  url VARCHAR(255),
  ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS utilizadores (
  id_utilizador INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  cartao_cidadao VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  telefone VARCHAR(30),
  servico VARCHAR(120),
  id_tipo_utilizador INT UNSIGNED NOT NULL,
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
    FOREIGN KEY (id_tipo_utilizador) REFERENCES utilizador_tipos(id_tipo_utilizador)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS utilizador_permissoes (
  id_permissao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_utilizador INT UNSIGNED NOT NULL,
  id_menu INT UNSIGNED NOT NULL,
  pode_aceder TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uk_utilizador_menu (id_utilizador, id_menu),
  CONSTRAINT fk_perm_utilizador
    FOREIGN KEY (id_utilizador) REFERENCES utilizadores(id_utilizador)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_perm_menu
    FOREIGN KEY (id_menu) REFERENCES menus_sistema(id_menu)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- LOCALIZACOES
-- =========================================================

CREATE TABLE IF NOT EXISTS localizacoes (
  id_localizacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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

CREATE TABLE IF NOT EXISTS categorias_equipamento (
  id_categoria_equipamento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE,
  descricao VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tipos_entrada (
  id_tipo_entrada INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estados_equipamento (
  id_estado_equipamento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS criticidades (
  id_criticidade INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE,
  descricao VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- FORNECEDORES
-- =========================================================

CREATE TABLE IF NOT EXISTS fornecedor_tipos (
  id_tipo_fornecedor INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fornecedores (
  id_fornecedor INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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

CREATE TABLE IF NOT EXISTS fornecedor_tipo_assoc (
  id_fornecedor INT UNSIGNED NOT NULL,
  id_tipo_fornecedor INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_fornecedor, id_tipo_fornecedor),
  CONSTRAINT fk_fta_fornecedor
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_fta_tipo
    FOREIGN KEY (id_tipo_fornecedor) REFERENCES fornecedor_tipos(id_tipo_fornecedor)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- EQUIPAMENTOS E ACESSORIOS
-- =========================================================

CREATE TABLE IF NOT EXISTS equipamentos (
  id_equipamento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  designacao VARCHAR(150) NOT NULL,
  id_categoria_equipamento INT UNSIGNED NOT NULL,
  fabricante VARCHAR(120) NOT NULL,
  modelo VARCHAR(120) NOT NULL,
  numero_serie VARCHAR(120) NOT NULL,
  ano_fabrico YEAR,
  data_aquisicao DATE,
  custo_aquisicao DECIMAL(12,2),
  id_tipo_entrada INT UNSIGNED,
  id_estado_equipamento INT UNSIGNED NOT NULL,
  id_criticidade INT UNSIGNED NOT NULL,
  id_localizacao INT UNSIGNED NOT NULL,
  operacional TINYINT(1) NOT NULL DEFAULT 1,
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_eq_serie_fabricante_modelo (fabricante, modelo, numero_serie),
  KEY idx_equipamento_codigo (codigo),
  KEY idx_equipamento_designacao (designacao),
  KEY idx_equipamento_estado (id_estado_equipamento),
  KEY idx_equipamento_localizacao (id_localizacao),
  KEY idx_equipamento_isActive (isActive),
  CONSTRAINT fk_eq_categoria
    FOREIGN KEY (id_categoria_equipamento) REFERENCES categorias_equipamento(id_categoria_equipamento)
    ON UPDATE CASCADE,
  CONSTRAINT fk_eq_tipo_entrada
    FOREIGN KEY (id_tipo_entrada) REFERENCES tipos_entrada(id_tipo_entrada)
    ON UPDATE CASCADE,
  CONSTRAINT fk_eq_estado
    FOREIGN KEY (id_estado_equipamento) REFERENCES estados_equipamento(id_estado_equipamento)
    ON UPDATE CASCADE,
  CONSTRAINT fk_eq_criticidade
    FOREIGN KEY (id_criticidade) REFERENCES criticidades(id_criticidade)
    ON UPDATE CASCADE,
  CONSTRAINT fk_eq_localizacao
    FOREIGN KEY (id_localizacao) REFERENCES localizacoes(id_localizacao)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS acessorios (
  id_acessorio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_equipamento INT UNSIGNED NOT NULL,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  tipo VARCHAR(100),
  numero_serie VARCHAR(120),
  id_estado_equipamento INT UNSIGNED NOT NULL,
  requer_verificacao_metrologica TINYINT(1) NOT NULL DEFAULT 0,
  proxima_intervencao DATE,
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_acessorio_equipamento (id_equipamento),
  KEY idx_acessorio_estado (id_estado_equipamento),
  KEY idx_acessorio_isActive (isActive),
  CONSTRAINT fk_acessorio_equipamento
    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_acessorio_estado
    FOREIGN KEY (id_estado_equipamento) REFERENCES estados_equipamento(id_estado_equipamento)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS equipamento_fornecedor (
  id_equipamento_fornecedor INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_equipamento INT UNSIGNED NOT NULL,
  id_fornecedor INT UNSIGNED NOT NULL,
  id_tipo_fornecedor INT UNSIGNED NOT NULL,
  data_inicio DATE,
  data_fim DATE,
  observacoes TEXT,
  UNIQUE KEY uk_eq_fornecedor_tipo (id_equipamento, id_fornecedor, id_tipo_fornecedor),
  CONSTRAINT fk_ef_equipamento
    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_ef_fornecedor
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_ef_tipo
    FOREIGN KEY (id_tipo_fornecedor) REFERENCES fornecedor_tipos(id_tipo_fornecedor)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DOCUMENTOS, GARANTIAS E CONTRATOS
-- =========================================================

CREATE TABLE IF NOT EXISTS documento_tipos (
  id_tipo_documento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documentos (
  id_documento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_tipo_documento INT UNSIGNED NOT NULL,
  nome VARCHAR(180) NOT NULL,
  data_documento DATE,
  data_validade DATE,
  id_equipamento INT UNSIGNED,
  id_acessorio INT UNSIGNED,
  id_fornecedor INT UNSIGNED,
  ficheiro_nome VARCHAR(255),
  caminho_ficheiro VARCHAR(255),
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  id_utilizador_criador INT UNSIGNED,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_documento_tipo (id_tipo_documento),
  KEY idx_documento_equipamento (id_equipamento),
  KEY idx_documento_fornecedor (id_fornecedor),
  KEY idx_documento_isActive (isActive),
  CONSTRAINT fk_doc_tipo
    FOREIGN KEY (id_tipo_documento) REFERENCES documento_tipos(id_tipo_documento)
    ON UPDATE CASCADE,
  CONSTRAINT fk_doc_equipamento
    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_doc_acessorio
    FOREIGN KEY (id_acessorio) REFERENCES acessorios(id_acessorio)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_doc_fornecedor
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_doc_utilizador
    FOREIGN KEY (id_utilizador_criador) REFERENCES utilizadores(id_utilizador)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS garantias (
  id_garantia INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_equipamento INT UNSIGNED NOT NULL,
  id_fornecedor INT UNSIGNED,
  id_documento INT UNSIGNED,
  data_inicio DATE NOT NULL,
  data_fim DATE NOT NULL,
  entidade_responsavel VARCHAR(150),
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_garantia_isActive (isActive),
  CONSTRAINT chk_garantia_datas CHECK (data_fim >= data_inicio),
  CONSTRAINT fk_garantia_equipamento
    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_garantia_fornecedor
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_garantia_documento
    FOREIGN KEY (id_documento) REFERENCES documentos(id_documento)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contratos_manutencao (
  id_contrato_manutencao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_equipamento INT UNSIGNED,
  id_fornecedor INT UNSIGNED NOT NULL,
  id_documento INT UNSIGNED,
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
    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_contrato_fornecedor
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_contrato_documento
    FOREIGN KEY (id_documento) REFERENCES documentos(id_documento)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- CALIBRACOES E MANUTENCOES
-- =========================================================

CREATE TABLE IF NOT EXISTS procedimento_tipos (
  id_tipo_procedimento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS procedimento_estados (
  id_estado_procedimento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos_calibracao_manutencao (
  id_pedido_manutencao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  id_equipamento INT UNSIGNED NOT NULL,
  id_acessorio INT UNSIGNED,
  id_tipo_procedimento INT UNSIGNED NOT NULL,
  id_fornecedor INT UNSIGNED,
  id_tecnico_responsavel INT UNSIGNED,
  data_pedido DATE NOT NULL,
  data_prevista DATE,
  data_conclusao DATE,
  id_estado_procedimento INT UNSIGNED NOT NULL,
  observacoes TEXT,
  isActive TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_pedido_estado (id_estado_procedimento),
  KEY idx_pedido_procedimento (id_tipo_procedimento),
  KEY idx_pedido_data_prevista (data_prevista),
  KEY idx_pedido_isActive (isActive),
  CONSTRAINT fk_pedido_equipamento
    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_acessorio
    FOREIGN KEY (id_acessorio) REFERENCES acessorios(id_acessorio)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_tipo
    FOREIGN KEY (id_tipo_procedimento) REFERENCES procedimento_tipos(id_tipo_procedimento)
    ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_fornecedor
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_tecnico
    FOREIGN KEY (id_tecnico_responsavel) REFERENCES utilizadores(id_utilizador)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_estado
    FOREIGN KEY (id_estado_procedimento) REFERENCES procedimento_estados(id_estado_procedimento)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS historico_estados_equipamento (
  id_historico_estado INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_equipamento INT UNSIGNED NOT NULL,
  id_estado_anterior INT UNSIGNED,
  id_estado_novo INT UNSIGNED NOT NULL,
  id_utilizador INT UNSIGNED,
  data_alteracao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  observacoes TEXT,
  CONSTRAINT fk_hist_equipamento
    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_hist_estado_anterior
    FOREIGN KEY (id_estado_anterior) REFERENCES estados_equipamento(id_estado_equipamento)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_hist_estado_novo
    FOREIGN KEY (id_estado_novo) REFERENCES estados_equipamento(id_estado_equipamento)
    ON UPDATE CASCADE,
  CONSTRAINT fk_hist_utilizador
    FOREIGN KEY (id_utilizador) REFERENCES utilizadores(id_utilizador)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- BACKOFFICE DA PAGINA PUBLICA
-- =========================================================

CREATE TABLE IF NOT EXISTS conteudos_publicos (
  id_conteudo_publico INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  chave VARCHAR(100) NOT NULL UNIQUE,
  secao VARCHAR(80) NOT NULL,
  titulo VARCHAR(180),
  texto TEXT,
  imagem VARCHAR(255),
  url VARCHAR(255),
  ordem INT NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  id_utilizador_atualizacao INT UNSIGNED,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_conteudo_secao (secao),
  CONSTRAINT fk_conteudo_utilizador
    FOREIGN KEY (id_utilizador_atualizacao) REFERENCES utilizadores(id_utilizador)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

