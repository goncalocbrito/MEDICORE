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
  
