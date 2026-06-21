CREATE TABLE fornecedores (
    id_fornecedor INT AUTO_INCREMENT PRIMARY KEY,
    nome_empresa VARCHAR(180) NOT NULL,
    tipo_fornecedor ENUM('Manutenção', 'Comercial', 'Fabricante') NOT NULL,
    nif INT NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL,
    telefone INT,
    website VARCHAR(255),
    pessoa_contacto VARCHAR(150),
    telefone_contacto INT,
    email_contacto VARCHAR(150),
    morada VARCHAR(255),
    codigo_postal VARCHAR(20),
    localidade VARCHAR(100),
    pais VARCHAR(80) NOT NULL DEFAULT 'Portugal',
    observacoes VARCHAR(500),
    isActive TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE documentos_fornecedores (
    id_documento_fornecedor INT AUTO_INCREMENT PRIMARY KEY,
    id_fornecedor INT NOT NULL,

    tipo_documento ENUM(
        'Contrato de Fornecimento',
        'Contrato de Manutenção',
        'Contrato de Calibração',
        'Certificado Técnico',
        'Comprovativo fiscal',
        'Outro'
    ) NOT NULL,

    numero_documento VARCHAR(30) NOT NULL UNIQUE,
    nome_documento VARCHAR(150) NOT NULL,
    caminho_ficheiro VARCHAR(255) NOT NULL,

    data_documento DATE NULL,
    data_validade DATE NULL,

    isActive TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_documentos_fornecedores_fornecedor
        FOREIGN KEY (id_fornecedor)
        REFERENCES fornecedores(id_fornecedor)
);

CREATE TABLE localizacoes (
    id_localizacao INT AUTO_INCREMENT PRIMARY KEY,

    codigo VARCHAR(30) NOT NULL UNIQUE,

    departamento_nome VARCHAR(150) NOT NULL,
    departamento_sigla VARCHAR(20) NOT NULL,

    edificio VARCHAR(100) NOT NULL,
    piso VARCHAR(30) NOT NULL,
    sala VARCHAR(80) NOT NULL,

    tipo_espaco VARCHAR(80) NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'Ativa',

    capacidade_equipamentos INT,
    permite_equipamentos_criticos TINYINT(1) NOT NULL DEFAULT 0,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  CREATE TABLE familias_equipamento (
    id_familia_equipamento INT AUTO_INCREMENT PRIMARY KEY,

    codigo_familia VARCHAR(10) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  CREATE TABLE equipamentos (
    id_equipamento INT AUTO_INCREMENT PRIMARY KEY,

    id_familia_equipamento INT NOT NULL,
    numero_sequencial INT NOT NULL,
    codigo_equipamento VARCHAR(30) NOT NULL UNIQUE,

    designacao VARCHAR(150) NOT NULL,
    modelo VARCHAR(120) NOT NULL,
    numero_serie VARCHAR(120) NOT NULL UNIQUE,

    tipo_entrada ENUM('compra', 'doacao', 'emprestimo') NULL,
    valor_aquisicao DECIMAL(10,2) NULL,

    id_localizacao INT NOT NULL,

    estado ENUM(
        'ativo',
        'avariado',
        'em_manutencao',
        'em_calibracao',
        'inativo',
        'abatido'
    ) NOT NULL DEFAULT 'ativo',

    criticidade ENUM(
        'baixa',
        'media',
        'alta',
        'critica'
    ) NOT NULL DEFAULT 'media',

    periodicidade_manutencao ENUM(
        'semestral',
        'anual',
        'bienal',
        'trienal'
    ) NULL,

    periodicidade_calibracao ENUM(
        'semestral',
        'anual',
        'bienal',
        'trienal'
    ) NULL,

    data_fabrico DATE NULL,
    data_aquisicao DATE NULL,
    data_instalacao DATE NULL,

    responsavel_equipamento VARCHAR(150) NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    CONSTRAINT fk_equipamento_familia
        FOREIGN KEY (id_familia_equipamento)
        REFERENCES familias_equipamento(id_familia_equipamento),

    CONSTRAINT fk_equipamento_localizacao
        FOREIGN KEY (id_localizacao)
        REFERENCES localizacoes(id_localizacao),

    CONSTRAINT uk_familia_numero
        UNIQUE (id_familia_equipamento, numero_sequencial)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  CREATE TABLE equipamentos_fornecedores (
    id_equipamento_fornecedor INT AUTO_INCREMENT PRIMARY KEY,

    id_equipamento INT NOT NULL UNIQUE,

    id_fornecedor_fabricante INT NOT NULL,
    id_fornecedor_comercial INT NOT NULL,
    id_fornecedor_garantia INT NULL,

    data_inicio_garantia DATE NULL,
    data_fim_garantia DATE NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    CONSTRAINT fk_eq_forn_equipamento
        FOREIGN KEY (id_equipamento)
        REFERENCES equipamentos(id_equipamento),

    CONSTRAINT fk_eq_forn_fabricante
        FOREIGN KEY (id_fornecedor_fabricante)
        REFERENCES fornecedores(id_fornecedor),

    CONSTRAINT fk_eq_forn_comercial
        FOREIGN KEY (id_fornecedor_comercial)
        REFERENCES fornecedores(id_fornecedor),

    CONSTRAINT fk_eq_forn_garantia
        FOREIGN KEY (id_fornecedor_garantia)
        REFERENCES fornecedores(id_fornecedor)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  CREATE TABLE manutencoes_equipamento (
    id_manutencao INT AUTO_INCREMENT PRIMARY KEY,

    id_equipamento INT NOT NULL,

    tipo_manutencao ENUM(
        'preventiva',
        'corretiva'
    ) NOT NULL,

    id_fornecedor_responsavel INT NULL,
    tecnico_interno VARCHAR(150) NULL,

    data_manutencao DATE NOT NULL,
    proxima_manutencao DATE NULL,

    descricao_procedimento TEXT NOT NULL,

    resultado ENUM(
        'realizada',
        'realizada_com_observacoes',
        'nao_realizada'
    ) NOT NULL DEFAULT 'realizada',

    coberta_por_garantia TINYINT(1) NOT NULL DEFAULT 0,

    custo DECIMAL(10,2) NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    CONSTRAINT fk_manutencao_equipamento
        FOREIGN KEY (id_equipamento)
        REFERENCES equipamentos(id_equipamento),

    CONSTRAINT fk_manutencao_fornecedor
        FOREIGN KEY (id_fornecedor_responsavel)
        REFERENCES fornecedores(id_fornecedor)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  CREATE TABLE calibracoes_equipamento (
    id_calibracao INT AUTO_INCREMENT PRIMARY KEY,

    id_equipamento INT NOT NULL,

    id_fornecedor_responsavel INT NULL,
    tecnico_interno VARCHAR(150) NULL,

    data_calibracao DATE NOT NULL,
    proxima_calibracao DATE NULL,

    numero_certificado VARCHAR(120) NULL,

    resultado ENUM(
        'aprovado',
        'aprovado_com_restricoes',
        'reprovado'
    ) NOT NULL DEFAULT 'aprovado',

    procedimento TEXT NULL,

    coberta_por_garantia TINYINT(1) NOT NULL DEFAULT 0,

    custo DECIMAL(10,2) NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    CONSTRAINT fk_calibracao_equipamento
        FOREIGN KEY (id_equipamento)
        REFERENCES equipamentos(id_equipamento),

    CONSTRAINT fk_calibracao_fornecedor
        FOREIGN KEY (id_fornecedor_responsavel)
        REFERENCES fornecedores(id_fornecedor)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  CREATE TABLE documentos_equipamentos (
    id_documento_equipamento INT AUTO_INCREMENT PRIMARY KEY,

    id_equipamento INT NOT NULL,

    id_manutencao INT NULL,
    id_calibracao INT NULL,
    id_equipamento_fornecedor INT NULL,

    tipo_documento ENUM(
        'manual_instrucoes',
        'datasheet',
        'contrato',
        'garantia',
        'certificado_calibracao',
        'relatorio_calibracao',
        'relatorio_manutencao',
        'ficha_tecnica',
        'declaracao_conformidade',
        'fotografia',
        'outro'
    ) NOT NULL,

    nome_documento VARCHAR(150) NOT NULL,
    caminho_ficheiro VARCHAR(255) NOT NULL,

    data_documento DATE NULL,
    data_validade DATE NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    CONSTRAINT fk_documento_equipamento
        FOREIGN KEY (id_equipamento)
        REFERENCES equipamentos(id_equipamento),

    CONSTRAINT fk_documento_manutencao
        FOREIGN KEY (id_manutencao)
        REFERENCES manutencoes_equipamento(id_manutencao),

    CONSTRAINT fk_documento_calibracao
        FOREIGN KEY (id_calibracao)
        REFERENCES calibracoes_equipamento(id_calibracao),

    CONSTRAINT fk_documento_equipamento_fornecedor
        FOREIGN KEY (id_equipamento_fornecedor)
        REFERENCES equipamentos_fornecedores(id_equipamento_fornecedor)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
CREATE TABLE acessorios_equipamento (
    id_acessorio INT AUTO_INCREMENT PRIMARY KEY,

    id_equipamento INT NOT NULL,
    numero_sequencial INT NOT NULL,

    designacao VARCHAR(150) NOT NULL,

    tipo ENUM(
        'sensor',
        'cabo',
        'modulo',
        'consumivel_reutilizavel',
        'adaptador',
        'bateria',
        'outro'
    ) NOT NULL DEFAULT 'outro',

    fabricante VARCHAR(120) NULL,
    modelo VARCHAR(120) NULL,
    numero_serie VARCHAR(120) NULL,

    estado ENUM(
        'ativo',
        'inativo',
        'avariado',
        'em_manutencao',
        'em_calibracao',
        'abatido'
    ) NOT NULL DEFAULT 'ativo',

    requer_manutencao TINYINT(1) NOT NULL DEFAULT 0,

    periodicidade_manutencao ENUM(
        'semestral',
        'anual',
        'bienal',
        'trienal'
    ) NULL,

    requer_calibracao TINYINT(1) NOT NULL DEFAULT 0,

    periodicidade_calibracao ENUM(
        'semestral',
        'anual',
        'bienal',
        'trienal'
    ) NULL,

    id_fornecedor_garantia INT NULL,
    data_inicio_garantia DATE NULL,
    data_fim_garantia DATE NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    CONSTRAINT fk_acessorio_equipamento
        FOREIGN KEY (id_equipamento)
        REFERENCES equipamentos(id_equipamento),

    CONSTRAINT fk_acessorio_fornecedor_garantia
        FOREIGN KEY (id_fornecedor_garantia)
        REFERENCES fornecedores(id_fornecedor),

    CONSTRAINT uk_acessorio_numero_por_equipamento
        UNIQUE (id_equipamento, numero_sequencial),

    CONSTRAINT uk_acessorio_numero_serie
        UNIQUE (numero_serie)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  ALTER TABLE manutencoes_equipamento
ADD COLUMN id_acessorio INT NULL AFTER id_equipamento,
ADD CONSTRAINT fk_manutencao_acessorio
    FOREIGN KEY (id_acessorio)
    REFERENCES acessorios_equipamento(id_acessorio);
    
    ALTER TABLE calibracoes_equipamento
ADD COLUMN id_acessorio INT NULL AFTER id_equipamento,
ADD CONSTRAINT fk_calibracao_acessorio
    FOREIGN KEY (id_acessorio)
    REFERENCES acessorios_equipamento(id_acessorio);
    
    ALTER TABLE documentos_equipamentos
ADD COLUMN id_acessorio INT NULL AFTER id_equipamento,
ADD CONSTRAINT fk_documento_acessorio
    FOREIGN KEY (id_acessorio)
    REFERENCES acessorios_equipamento(id_acessorio);
    
CREATE TABLE consumiveis (
    id_consumivel INT AUTO_INCREMENT PRIMARY KEY,

    codigo_consumivel VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,

    categoria ENUM(
        'eletrodos',
        'papel_tecnico',
        'filtros',
        'circuitos_descartaveis',
        'gel_contacto',
        'sensores_descartaveis',
        'reagente_calibracao',
        'material_calibracao',
        'outro'
    ) NOT NULL DEFAULT 'outro',

    unidade VARCHAR(30) NOT NULL DEFAULT 'unidades',

    stock_atual DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock_minimo DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock_maximo DECIMAL(10,2) NULL,

    preco_unitario DECIMAL(10,2) NULL,

    localizacao_stock VARCHAR(120) NULL,
    referencia_fabricante VARCHAR(120) NULL,
    id_fornecedor_preferencial INT NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    FOREIGN KEY (id_fornecedor_preferencial)
        REFERENCES fornecedores(id_fornecedor)
);

CREATE TABLE consumiveis_equipamentos (
    id_consumivel_equipamento INT AUTO_INCREMENT PRIMARY KEY,

    id_consumivel INT NOT NULL,
    id_equipamento INT NOT NULL,

    necessario_utilizacao TINYINT(1) NOT NULL DEFAULT 1,
    necessario_calibracao TINYINT(1) NOT NULL DEFAULT 0,

    quantidade_prevista DECIMAL(10,2) NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    FOREIGN KEY (id_consumivel)
        REFERENCES consumiveis(id_consumivel),

    FOREIGN KEY (id_equipamento)
        REFERENCES equipamentos(id_equipamento),

    UNIQUE (id_consumivel, id_equipamento)
);

CREATE TABLE consumiveis_acessorios (
    id_consumivel_acessorio INT AUTO_INCREMENT PRIMARY KEY,

    id_consumivel INT NOT NULL,
    id_acessorio INT NOT NULL,

    necessario_utilizacao TINYINT(1) NOT NULL DEFAULT 1,
    necessario_calibracao TINYINT(1) NOT NULL DEFAULT 0,

    quantidade_prevista DECIMAL(10,2) NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    FOREIGN KEY (id_consumivel)
        REFERENCES consumiveis(id_consumivel),

    FOREIGN KEY (id_acessorio)
        REFERENCES acessorios_equipamento(id_acessorio),

    UNIQUE (id_consumivel, id_acessorio)
);

CREATE TABLE movimentos_stock_consumiveis (
    id_movimento_stock INT AUTO_INCREMENT PRIMARY KEY,

    id_consumivel INT NOT NULL,

    tipo_movimento ENUM(
        'entrada',
        'saida',
        'ajuste',
        'consumo_calibracao',
        'devolucao'
    ) NOT NULL,

    quantidade DECIMAL(10,2) NOT NULL,

    stock_anterior DECIMAL(10,2) NULL,
    stock_posterior DECIMAL(10,2) NULL,

    id_equipamento INT NULL,
    id_acessorio INT NULL,
    id_calibracao INT NULL,

    motivo VARCHAR(150) NULL,
    observacoes TEXT,

    data_movimento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    FOREIGN KEY (id_consumivel)
        REFERENCES consumiveis(id_consumivel),

    FOREIGN KEY (id_equipamento)
        REFERENCES equipamentos(id_equipamento),

    FOREIGN KEY (id_acessorio)
        REFERENCES acessorios_equipamento(id_acessorio),

    FOREIGN KEY (id_calibracao)
        REFERENCES calibracoes_equipamento(id_calibracao)
);

ALTER TABLE acessorios_equipamento
ADD COLUMN id_localizacao INT NULL AFTER id_equipamento,
ADD CONSTRAINT fk_acessorios_localizacao
    FOREIGN KEY (id_localizacao)
    REFERENCES localizacoes(id_localizacao);
    
ALTER TABLE acessorios_equipamento
MODIFY id_localizacao INT NOT NULL;

ALTER TABLE consumiveis
ADD COLUMN id_localizacao INT NOT NULL AFTER preco_unitario,
ADD CONSTRAINT fk_consumiveis_localizacao
    FOREIGN KEY (id_localizacao)
    REFERENCES localizacoes(id_localizacao);
    
ALTER TABLE consumiveis
DROP COLUMN localizacao_stock;

ALTER TABLE manutencoes_equipamento
ADD COLUMN codigo_processo VARCHAR(30) NULL AFTER id_manutencao,
ADD COLUMN tipo_execucao ENUM('interna', 'externa') NOT NULL DEFAULT 'externa' AFTER tipo_manutencao,
ADD COLUMN estado_processo ENUM(
    'aguarda_recolha',
    'procedimento_a_decorrer',
    'procedimento_efetuado',
    'emissao_relatorio',
    'processo_finalizado',
    'cancelado'
) NOT NULL DEFAULT 'aguarda_recolha' AFTER tipo_execucao,
ADD COLUMN data_abertura DATE NULL AFTER estado_processo,
ADD COLUMN data_prevista DATE NULL AFTER data_abertura,
ADD COLUMN data_recolha DATE NULL AFTER data_prevista,
ADD COLUMN data_inicio_procedimento DATE NULL AFTER data_recolha,
ADD COLUMN data_fim_procedimento DATE NULL AFTER data_inicio_procedimento,
ADD COLUMN data_emissao_relatorio DATE NULL AFTER data_fim_procedimento,
ADD COLUMN data_finalizacao DATE NULL AFTER data_emissao_relatorio,
ADD COLUMN numero_relatorio VARCHAR(120) NULL AFTER proxima_manutencao;

ALTER TABLE manutencoes_equipamento
MODIFY data_manutencao DATE NULL,
MODIFY descricao_procedimento TEXT NULL,
MODIFY resultado ENUM(
    'realizada',
    'realizada_com_observacoes',
    'nao_realizada'
) NULL DEFAULT NULL;

ALTER TABLE calibracoes_equipamento
ADD COLUMN codigo_processo VARCHAR(30) NULL AFTER id_calibracao,
ADD COLUMN tipo_execucao ENUM('interna', 'externa') NOT NULL DEFAULT 'externa' AFTER id_fornecedor_responsavel,
ADD COLUMN estado_processo ENUM(
    'aguarda_recolha',
    'procedimento_a_decorrer',
    'procedimento_efetuado',
    'emissao_relatorio',
    'processo_finalizado',
    'cancelado'
) NOT NULL DEFAULT 'aguarda_recolha' AFTER tipo_execucao,
ADD COLUMN data_abertura DATE NULL AFTER estado_processo,
ADD COLUMN data_prevista DATE NULL AFTER data_abertura,
ADD COLUMN data_recolha DATE NULL AFTER data_prevista,
ADD COLUMN data_inicio_procedimento DATE NULL AFTER data_recolha,
ADD COLUMN data_fim_procedimento DATE NULL AFTER data_inicio_procedimento,
ADD COLUMN data_emissao_relatorio DATE NULL AFTER data_fim_procedimento,
ADD COLUMN data_finalizacao DATE NULL AFTER data_emissao_relatorio;

ALTER TABLE calibracoes_equipamento
MODIFY data_calibracao DATE NULL,
MODIFY resultado ENUM(
    'aprovado',
    'aprovado_com_restricoes',
    'reprovado'
) NULL DEFAULT NULL,
MODIFY procedimento TEXT NULL;

CREATE TABLE historico_etapas_processos (
    id_historico_etapa INT AUTO_INCREMENT PRIMARY KEY,

    tipo_processo ENUM('manutencao', 'calibracao') NOT NULL,

    id_manutencao INT NULL,
    id_calibracao INT NULL,

    estado_anterior ENUM(
        'aguarda_recolha',
        'procedimento_a_decorrer',
        'procedimento_efetuado',
        'emissao_relatorio',
        'processo_finalizado',
        'cancelado'
    ) NULL,

    estado_novo ENUM(
        'aguarda_recolha',
        'procedimento_a_decorrer',
        'procedimento_efetuado',
        'emissao_relatorio',
        'processo_finalizado',
        'cancelado'
    ) NOT NULL,

    responsavel_etapa VARCHAR(150) NULL,

    tipo_responsavel ENUM(
        'interno',
        'fornecedor'
    ) NULL,

    id_fornecedor_responsavel INT NULL,

    observacoes TEXT NULL,

    data_registo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    atualizado_por VARCHAR(150) NULL,

    FOREIGN KEY (id_manutencao)
        REFERENCES manutencoes_equipamento(id_manutencao),

    FOREIGN KEY (id_calibracao)
        REFERENCES calibracoes_equipamento(id_calibracao),

    FOREIGN KEY (id_fornecedor_responsavel)
        REFERENCES fornecedores(id_fornecedor)
);

UPDATE manutencoes_equipamento
SET codigo_processo = CONCAT(
    'MAN-',
    YEAR(COALESCE(data_manutencao, criado_em, CURDATE())),
    '-',
    LPAD(id_manutencao, 4, '0')
)
WHERE codigo_processo IS NULL;

UPDATE calibracoes_equipamento
SET codigo_processo = CONCAT(
    'CAL-',
    YEAR(COALESCE(data_calibracao, criado_em, CURDATE())),
    '-',
    LPAD(id_calibracao, 4, '0')
)
WHERE codigo_processo IS NULL;

ALTER TABLE manutencoes_equipamento
ADD CONSTRAINT uk_manutencao_codigo_processo UNIQUE (codigo_processo);

ALTER TABLE calibracoes_equipamento
ADD CONSTRAINT uk_calibracao_codigo_processo UNIQUE (codigo_processo);

UPDATE manutencoes_equipamento
SET
    tipo_execucao = CASE
        WHEN id_fornecedor_responsavel IS NULL AND tecnico_interno IS NOT NULL THEN 'interna'
        ELSE 'externa'
    END,

    estado_processo = 'processo_finalizado',

    data_abertura = COALESCE(data_abertura, data_manutencao, DATE(criado_em), CURDATE()),
    data_prevista = COALESCE(data_prevista, data_manutencao, DATE(criado_em), CURDATE()),
    data_recolha = COALESCE(data_recolha, data_manutencao),
    data_inicio_procedimento = COALESCE(data_inicio_procedimento, data_manutencao),
    data_fim_procedimento = COALESCE(data_fim_procedimento, data_manutencao),
    data_emissao_relatorio = COALESCE(data_emissao_relatorio, data_manutencao),
    data_finalizacao = COALESCE(data_finalizacao, data_manutencao, DATE(criado_em), CURDATE()),

    numero_relatorio = COALESCE(
        numero_relatorio,
        CONCAT('REL-MAN-', YEAR(COALESCE(data_manutencao, criado_em, CURDATE())), '-', LPAD(id_manutencao, 4, '0'))
    )
WHERE isActive = 1;

UPDATE calibracoes_equipamento
SET
    tipo_execucao = CASE
        WHEN id_fornecedor_responsavel IS NULL AND tecnico_interno IS NOT NULL THEN 'interna'
        ELSE 'externa'
    END,

    estado_processo = 'processo_finalizado',

    data_abertura = COALESCE(data_abertura, data_calibracao, DATE(criado_em), CURDATE()),
    data_prevista = COALESCE(data_prevista, data_calibracao, DATE(criado_em), CURDATE()),
    data_recolha = COALESCE(data_recolha, data_calibracao),
    data_inicio_procedimento = COALESCE(data_inicio_procedimento, data_calibracao),
    data_fim_procedimento = COALESCE(data_fim_procedimento, data_calibracao),
    data_emissao_relatorio = COALESCE(data_emissao_relatorio, data_calibracao),
    data_finalizacao = COALESCE(data_finalizacao, data_calibracao, DATE(criado_em), CURDATE())
WHERE isActive = 1;

-- 1. Corrigir valores antigos antes de alterar o ENUM
UPDATE manutencoes_equipamento
SET tipo_execucao = CASE
    WHEN id_fornecedor_responsavel IS NOT NULL THEN 'externa'
    WHEN tecnico_interno IS NOT NULL THEN 'interna'
    ELSE 'externa'
END
WHERE tipo_execucao NOT IN ('interna', 'externa');

UPDATE calibracoes_equipamento
SET tipo_execucao = CASE
    WHEN id_fornecedor_responsavel IS NOT NULL THEN 'externa'
    WHEN tecnico_interno IS NOT NULL THEN 'interna'
    ELSE 'externa'
END
WHERE tipo_execucao NOT IN ('interna', 'externa');

UPDATE historico_etapas_processos
SET tipo_responsavel = CASE
    WHEN id_fornecedor_responsavel IS NOT NULL THEN 'fornecedor'
    ELSE 'interno'
END
WHERE tipo_responsavel NOT IN ('interno', 'fornecedor')
   OR tipo_responsavel IS NULL;

-- 2. Alterar os ENUM para aceitar apenas os valores pretendidos
ALTER TABLE manutencoes_equipamento
MODIFY tipo_execucao ENUM('interna', 'externa') NOT NULL DEFAULT 'externa';

ALTER TABLE calibracoes_equipamento
MODIFY tipo_execucao ENUM('interna', 'externa') NOT NULL DEFAULT 'externa';

ALTER TABLE historico_etapas_processos
MODIFY tipo_responsavel ENUM('interno', 'fornecedor') NULL;




-- =========================================================
-- MEDICORE | Utilizadores, permissões e histórico/auditoria
-- Executar depois das tabelas principais do projeto.
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- 1) Tabela principal de utilizadores
-- =========================================================
CREATE TABLE IF NOT EXISTS utilizadores (
    id_utilizador INT AUTO_INCREMENT PRIMARY KEY,

    codigo_utilizador VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(180) NOT NULL,

    tipo_utilizador ENUM('Administrador', 'Engenheiro', 'Enfermeiro') NOT NULL,
    estado ENUM('Ativo', 'Inativo', 'Pendente') NOT NULL DEFAULT 'Ativo',

    cartao_cidadao VARCHAR(30) NOT NULL UNIQUE,
    nif VARCHAR(20) NULL UNIQUE,
    data_nascimento DATE NULL,
    numero_mecanografico VARCHAR(50) NULL UNIQUE,

    email VARCHAR(150) NOT NULL UNIQUE,
    telefone VARCHAR(30) NULL,
    extensao VARCHAR(30) NULL,
    morada VARCHAR(255) NULL,
    codigo_postal VARCHAR(20) NULL,
    localidade VARCHAR(100) NULL,

    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,

    perfil_acesso ENUM('Acesso total', 'Gestão técnica', 'Consulta clínica') NULL,
    data_ativacao DATE NULL,
    validade_acesso DATE NULL,

    departamento VARCHAR(120) NULL,
    funcao VARCHAR(120) NULL,
    superior_hierarquico VARCHAR(150) NULL,
    edificio VARCHAR(80) NULL,
    piso VARCHAR(30) NULL,
    data_admissao DATE NULL,

    observacoes TEXT NULL,

    ultimo_login DATETIME NULL,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 2) Catálogo de permissões/autorizacões por módulo
-- =========================================================
CREATE TABLE IF NOT EXISTS permissoes_sistema (
    id_permissao INT AUTO_INCREMENT PRIMARY KEY,

    codigo_permissao VARCHAR(60) NOT NULL UNIQUE,
    nome_permissao VARCHAR(120) NOT NULL,
    descricao VARCHAR(255) NULL,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


INSERT IGNORE INTO permissoes_sistema (
    codigo_permissao,
    nome_permissao,
    descricao,
    isActive,
    atualizado_por
) VALUES
('dashboard', 'Dashboard Técnico', 'Permite consultar o painel técnico inicial.', 1, 'sistema'),
('equipamentos', 'Equipamentos', 'Permite aceder à gestão e consulta de equipamentos.', 1, 'sistema'),
('calibracoes', 'Calibrações/Manutenções', 'Permite aceder aos processos de calibração e manutenção.', 1, 'sistema'),
('localizacoes', 'Localizações', 'Permite aceder à gestão de localizações hospitalares.', 1, 'sistema'),
('fornecedores', 'Fornecedores', 'Permite aceder à gestão de fornecedores.', 1, 'sistema'),
('utilizadores', 'Utilizadores', 'Permite aceder à gestão de utilizadores e permissões.', 1, 'sistema'),
('acessorios', 'Acessórios', 'Permite aceder à gestão de acessórios de equipamentos.', 1, 'sistema'),
('consumiveis', 'Consumíveis', 'Permite aceder à gestão de consumíveis e stock.', 1, 'sistema'),
('documentos', 'Documentos', 'Permite aceder à gestão documental associada aos equipamentos.', 1, 'sistema'),
('backoffice', 'Backoffice', 'Permite aceder à gestão de conteúdos do front office.', 1, 'sistema');


-- =========================================================
-- 3) Permissões atribuídas a cada utilizador
--    Remover autorização = colocar isActive = 0.
--    Reativar autorização = colocar isActive = 1.
-- =========================================================
CREATE TABLE IF NOT EXISTS utilizadores_permissoes (
    id_utilizador_permissao INT AUTO_INCREMENT PRIMARY KEY,

    id_utilizador INT NOT NULL,
    id_permissao INT NOT NULL,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por VARCHAR(150) NULL,

    CONSTRAINT fk_utilizador_permissao_utilizador
        FOREIGN KEY (id_utilizador)
        REFERENCES utilizadores(id_utilizador),

    CONSTRAINT fk_utilizador_permissao_permissao
        FOREIGN KEY (id_permissao)
        REFERENCES permissoes_sistema(id_permissao),

    CONSTRAINT uk_utilizador_permissao
        UNIQUE (id_utilizador, id_permissao)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 4) Histórico/auditoria de utilizadores e autorizações
-- =========================================================
CREATE TABLE IF NOT EXISTS historico_utilizadores (
    id_historico_utilizador INT AUTO_INCREMENT PRIMARY KEY,

    id_utilizador_alvo INT NULL,
    codigo_utilizador VARCHAR(30) NULL,

    acao ENUM(
        'criacao_utilizador',
        'edicao_utilizador',
        'remocao_utilizador',
        'reativacao_utilizador',
        'adicao_autorizacao',
        'remocao_autorizacao',
        'reativacao_autorizacao',
        'edicao_autorizacao'
    ) NOT NULL,

    campo_alterado VARCHAR(100) NULL,
    valor_anterior TEXT NULL,
    valor_novo TEXT NULL,

    id_permissao INT NULL,
    codigo_permissao VARCHAR(60) NULL,

    observacoes TEXT NULL,

    realizado_por VARCHAR(150) NULL,
    data_registo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_historico_utilizadores_alvo
        FOREIGN KEY (id_utilizador_alvo)
        REFERENCES utilizadores(id_utilizador),

    CONSTRAINT fk_historico_utilizadores_permissao
        FOREIGN KEY (id_permissao)
        REFERENCES permissoes_sistema(id_permissao)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;


/* 19/06/2026*/

DELETE FROM utilizadores_permissoes
WHERE id_utilizador IN (
    SELECT id_utilizador
    FROM utilizadores
    WHERE tipo_utilizador = 'Enfermeiro'
);

DELETE FROM utilizadores
WHERE tipo_utilizador = 'Enfermeiro';

ALTER TABLE utilizadores
MODIFY tipo_utilizador ENUM('Administrador', 'Engenheiro') NOT NULL;

DELETE FROM utilizadores
WHERE tipo_utilizador = 'Enfermeiro';

ALTER TABLE utilizadores
MODIFY tipo_utilizador ENUM('Administrador', 'Engenheiro') NOT NULL;

DELETE FROM utilizadores
WHERE tipo_utilizador = 'Enfermeiro';

ALTER TABLE utilizadores
MODIFY tipo_utilizador ENUM('Administrador', 'Engenheiro') NOT NULL;

UPDATE equipamentos_fornecedores
SET id_fornecedor_garantia = COALESCE(
    id_fornecedor_garantia,
    id_fornecedor_fabricante,
    id_fornecedor_comercial
)
WHERE id_fornecedor_garantia IS NULL;

ALTER TABLE equipamentos_fornecedores
DROP FOREIGN KEY fk_eq_forn_fabricante;

ALTER TABLE equipamentos_fornecedores
DROP FOREIGN KEY fk_eq_forn_comercial;

ALTER TABLE equipamentos_fornecedores
DROP FOREIGN KEY fk_eq_forn_garantia;

ALTER TABLE equipamentos_fornecedores
DROP COLUMN id_fornecedor_fabricante,
DROP COLUMN id_fornecedor_comercial;

ALTER TABLE equipamentos_fornecedores
MODIFY id_fornecedor_garantia INT NOT NULL;

ALTER TABLE equipamentos_fornecedores
ADD CONSTRAINT fk_eq_forn_garantia
FOREIGN KEY (id_fornecedor_garantia)
REFERENCES fornecedores(id_fornecedor);

ALTER TABLE equipamentos_fornecedores
MODIFY id_fornecedor_garantia INT NULL;


/* 20/06/2026*/

CREATE TABLE manutencoes_acessorios (
    id_manutencao_acessorio INT AUTO_INCREMENT PRIMARY KEY,
    id_manutencao INT NOT NULL,
    id_acessorio INT NOT NULL,
    isActive TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_manutencao)
        REFERENCES manutencoes_equipamento(id_manutencao),

    FOREIGN KEY (id_acessorio)
        REFERENCES acessorios_equipamento(id_acessorio),

    UNIQUE (id_manutencao, id_acessorio)
);

CREATE TABLE calibracoes_acessorios (
    id_calibracao_acessorio INT AUTO_INCREMENT PRIMARY KEY,
    id_calibracao INT NOT NULL,
    id_acessorio INT NOT NULL,
    isActive TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_calibracao)
        REFERENCES calibracoes_equipamento(id_calibracao),

    FOREIGN KEY (id_acessorio)
        REFERENCES acessorios_equipamento(id_acessorio),

    UNIQUE (id_calibracao, id_acessorio)
);

CREATE TABLE manutencoes_consumiveis (
    id_manutencao_consumivel INT AUTO_INCREMENT PRIMARY KEY,
    id_manutencao INT NOT NULL,
    id_consumivel INT NOT NULL,
    quantidade_utilizada DECIMAL(10,2) NOT NULL DEFAULT 1,
    isActive TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_manutencao)
        REFERENCES manutencoes_equipamento(id_manutencao),

    FOREIGN KEY (id_consumivel)
        REFERENCES consumiveis(id_consumivel),

    UNIQUE (id_manutencao, id_consumivel)
);

CREATE TABLE calibracoes_consumiveis (
    id_calibracao_consumivel INT AUTO_INCREMENT PRIMARY KEY,
    id_calibracao INT NOT NULL,
    id_consumivel INT NOT NULL,
    quantidade_utilizada DECIMAL(10,2) NOT NULL DEFAULT 1,
    isActive TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_calibracao)
        REFERENCES calibracoes_equipamento(id_calibracao),

    FOREIGN KEY (id_consumivel)
        REFERENCES consumiveis(id_consumivel),

    UNIQUE (id_calibracao, id_consumivel)
);

ALTER TABLE manutencoes_equipamento
DROP FOREIGN KEY fk_manutencao_acessorio;

ALTER TABLE equipamentos_fornecedores
MODIFY id_fornecedor_garantia INT NULL;


CREATE TABLE historico_equipamentos (
    id_historico_equipamento INT AUTO_INCREMENT PRIMARY KEY,
    id_equipamento INT NOT NULL,
    id_localizacao INT NULL,
    id_utilizador INT NULL,
    tipo_evento ENUM(
        'criacao',
        'alteracao_localizacao',
        'transferencia_pendente',
        'transferencia_aprovada',
        'transferencia_rejeitada',
        'emprestimo_iniciado',
        'emprestimo_terminado',
        'manutencao',
        'calibracao',
        'alteracao_dados'
    ) NOT NULL,
    referencia_tabela VARCHAR(80) NULL,
    referencia_id INT NULL,
    descricao VARCHAR(500) NOT NULL,
    data_evento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    isActive TINYINT(1) NOT NULL DEFAULT 1,

    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento),
    FOREIGN KEY (id_localizacao) REFERENCES localizacoes(id_localizacao),
    FOREIGN KEY (id_utilizador) REFERENCES utilizadores(id_utilizador)
);

CREATE TABLE transferencias_equipamentos (
    id_transferencia INT AUTO_INCREMENT PRIMARY KEY,
    codigo_transferencia VARCHAR(30) NOT NULL UNIQUE,
    id_equipamento INT NOT NULL,
    id_localizacao_origem INT NOT NULL,
    id_localizacao_destino INT NOT NULL,
    id_utilizador_pedido INT NOT NULL,
    id_utilizador_aprovacao INT NULL,
    motivo VARCHAR(255) NULL,
    estado ENUM('pendente', 'aprovado', 'rejeitado') NOT NULL DEFAULT 'pendente',
    data_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao DATETIME NULL,
    observacoes VARCHAR(500) NULL,
    isActive TINYINT(1) NOT NULL DEFAULT 1,

    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento),
    FOREIGN KEY (id_localizacao_origem) REFERENCES localizacoes(id_localizacao),
    FOREIGN KEY (id_localizacao_destino) REFERENCES localizacoes(id_localizacao),
    FOREIGN KEY (id_utilizador_pedido) REFERENCES utilizadores(id_utilizador),
    FOREIGN KEY (id_utilizador_aprovacao) REFERENCES utilizadores(id_utilizador)
);

CREATE TABLE emprestimos_equipamentos (
    id_emprestimo INT AUTO_INCREMENT PRIMARY KEY,
    codigo_emprestimo VARCHAR(30) NOT NULL UNIQUE,
    id_equipamento INT NOT NULL,
    id_localizacao_origem INT NOT NULL,
    id_localizacao_destino INT NOT NULL,
    id_utilizador_pedido INT NOT NULL,
    id_utilizador_termino INT NULL,
    responsavel_emprestimo VARCHAR(150) NOT NULL,
    motivo VARCHAR(255) NULL,
    data_inicio DATE NOT NULL,
    data_prevista_devolucao DATE NOT NULL,
    data_termino DATE NULL,
    estado ENUM('ativo', 'terminado', 'atrasado', 'cancelado') NOT NULL DEFAULT 'ativo',
    observacoes VARCHAR(500) NULL,
    isActive TINYINT(1) NOT NULL DEFAULT 1,

    FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id_equipamento),
    FOREIGN KEY (id_localizacao_origem) REFERENCES localizacoes(id_localizacao),
    FOREIGN KEY (id_localizacao_destino) REFERENCES localizacoes(id_localizacao),
    FOREIGN KEY (id_utilizador_pedido) REFERENCES utilizadores(id_utilizador),
    FOREIGN KEY (id_utilizador_termino) REFERENCES utilizadores(id_utilizador)
);



SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- 1. Atualizar estados dos processos de manutenção
-- =========================================================

ALTER TABLE manutencoes_equipamento
MODIFY estado_processo ENUM(
    'aguarda_decisao',
    'aprovado',
    'reprovado',
    'cancelado',
    'aguarda_recolha',
    'procedimento_a_decorrer',
    'procedimento_efetuado',
    'emissao_relatorio',
    'devolucao_equipamento',
    'processo_finalizado'
) NOT NULL DEFAULT 'aguarda_decisao';


-- =========================================================
-- 2. Atualizar estados dos processos de calibração
-- =========================================================

ALTER TABLE calibracoes_equipamento
MODIFY estado_processo ENUM(
    'aguarda_decisao',
    'aprovado',
    'reprovado',
    'cancelado',
    'aguarda_recolha',
    'procedimento_a_decorrer',
    'procedimento_efetuado',
    'emissao_relatorio',
    'devolucao_equipamento',
    'processo_finalizado'
) NOT NULL DEFAULT 'aguarda_decisao';


-- =========================================================
-- 3. Atualizar histórico das etapas dos processos
-- =========================================================

ALTER TABLE historico_etapas_processos
MODIFY estado_anterior ENUM(
    'aguarda_decisao',
    'aprovado',
    'reprovado',
    'cancelado',
    'aguarda_recolha',
    'procedimento_a_decorrer',
    'procedimento_efetuado',
    'emissao_relatorio',
    'devolucao_equipamento',
    'processo_finalizado'
) NULL,
MODIFY estado_novo ENUM(
    'aguarda_decisao',
    'aprovado',
    'reprovado',
    'cancelado',
    'aguarda_recolha',
    'procedimento_a_decorrer',
    'procedimento_efetuado',
    'emissao_relatorio',
    'devolucao_equipamento',
    'processo_finalizado'
) NOT NULL;


-- =========================================================
-- 4. Adicionar decisão do administrador nas manutenções
-- =========================================================

ALTER TABLE manutencoes_equipamento
ADD COLUMN decisao_admin ENUM('pendente', 'aprovado', 'reprovado') NOT NULL DEFAULT 'pendente' AFTER estado_processo,
ADD COLUMN id_admin_decisao INT NULL AFTER decisao_admin,
ADD COLUMN data_decisao DATETIME NULL AFTER id_admin_decisao,
ADD COLUMN motivo_decisao VARCHAR(500) NULL AFTER data_decisao;

ALTER TABLE manutencoes_equipamento
ADD CONSTRAINT fk_manutencao_admin_decisao
FOREIGN KEY (id_admin_decisao)
REFERENCES utilizadores(id_utilizador);


-- =========================================================
-- 5. Adicionar decisão do administrador nas calibrações
-- =========================================================

ALTER TABLE calibracoes_equipamento
ADD COLUMN decisao_admin ENUM('pendente', 'aprovado', 'reprovado') NOT NULL DEFAULT 'pendente' AFTER estado_processo,
ADD COLUMN id_admin_decisao INT NULL AFTER decisao_admin,
ADD COLUMN data_decisao DATETIME NULL AFTER id_admin_decisao,
ADD COLUMN motivo_decisao VARCHAR(500) NULL AFTER data_decisao;

ALTER TABLE calibracoes_equipamento
ADD CONSTRAINT fk_calibracao_admin_decisao
FOREIGN KEY (id_admin_decisao)
REFERENCES utilizadores(id_utilizador);


-- =========================================================
-- 6. Atualizar documentos dos equipamentos
-- =========================================================

ALTER TABLE documentos_equipamentos
MODIFY tipo_documento ENUM(
    'manual_instrucoes',
    'datasheet',
    'contrato',
    'garantia',
    'contrato_aquisicao',
    'contrato_garantia',
    'contrato_manutencao',
    'contrato_calibracao',
    'certificado_calibracao',
    'relatorio_calibracao',
    'relatorio_manutencao',
    'ficha_tecnica',
    'declaracao_conformidade',
    'fotografia',
    'outro'
) NOT NULL;


-- =========================================================
-- 7. Melhorar histórico dos equipamentos
-- =========================================================

ALTER TABLE historico_equipamentos
ADD COLUMN id_localizacao_origem INT NULL AFTER id_localizacao,
ADD COLUMN id_localizacao_destino INT NULL AFTER id_localizacao_origem;

ALTER TABLE historico_equipamentos
ADD CONSTRAINT fk_historico_localizacao_origem
FOREIGN KEY (id_localizacao_origem)
REFERENCES localizacoes(id_localizacao);

ALTER TABLE historico_equipamentos
ADD CONSTRAINT fk_historico_localizacao_destino
FOREIGN KEY (id_localizacao_destino)
REFERENCES localizacoes(id_localizacao);


-- =========================================================
-- 8. Converter processos abertos antigos para nova etapa inicial
-- =========================================================

UPDATE manutencoes_equipamento
SET estado_processo = 'aguarda_decisao',
    decisao_admin = 'pendente'
WHERE estado_processo = 'aguarda_recolha'
  AND data_recolha IS NULL
  AND data_inicio_procedimento IS NULL
  AND data_finalizacao IS NULL;

UPDATE calibracoes_equipamento
SET estado_processo = 'aguarda_decisao',
    decisao_admin = 'pendente'
WHERE estado_processo = 'aguarda_recolha'
  AND data_recolha IS NULL
  AND data_inicio_procedimento IS NULL
  AND data_finalizacao IS NULL;


SET FOREIGN_KEY_CHECKS = 1;


-- =========================================================
-- 21/06/25
-- =========================================================

ALTER TABLE emprestimos_equipamentos
MODIFY estado ENUM('pendente','ativo','rejeitado','terminado','atrasado')
NOT NULL DEFAULT 'pendente';

ALTER TABLE emprestimos_equipamentos
ADD COLUMN id_utilizador_aprovacao INT NULL,
ADD COLUMN data_aprovacao DATETIME NULL;

ALTER TABLE historico_equipamentos
MODIFY tipo_evento ENUM(
    'criacao',
    'alteracao_localizacao',
    'transferencia_pendente',
    'transferencia_aprovada',
    'transferencia_rejeitada',
    'emprestimo_pendente',
    'emprestimo_iniciado',
    'emprestimo_rejeitado',
    'emprestimo_terminado',
    'manutencao',
    'calibracao',
    'alteracao_dados'
) NOT NULL;
