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
ADD COLUMN tipo_execucao ENUM('interna', 'externa', 'mista') NOT NULL DEFAULT 'externa' AFTER tipo_manutencao,
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
ADD COLUMN tipo_execucao ENUM('interna', 'externa', 'mista') NOT NULL DEFAULT 'externa' AFTER id_fornecedor_responsavel,
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
        'fornecedor',
        'sistema',
        'outro'
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
        WHEN id_fornecedor_responsavel IS NOT NULL AND tecnico_interno IS NOT NULL THEN 'mista'
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
        WHEN id_fornecedor_responsavel IS NOT NULL AND tecnico_interno IS NOT NULL THEN 'mista'
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
