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
    codigo VARCHAR(30) NOT NULL UNIQUE,

    designacao VARCHAR(150) NOT NULL,

    fabricante VARCHAR(120) NOT NULL,
    modelo VARCHAR(120) NOT NULL,
    numero_serie VARCHAR(120) NOT NULL UNIQUE,

    tipo_entrada VARCHAR(50),

    id_localizacao INT NOT NULL,

    estado VARCHAR(30) NOT NULL DEFAULT 'Ativo',
    criticidade VARCHAR(30) NOT NULL,
    operacional TINYINT(1) NOT NULL DEFAULT 1,

    data_fabrico DATE NULL,
    data_aquisicao DATE NULL,
    data_instalacao DATE NULL,
    valor_aquisicao DECIMAL(10,2) NULL,

    fim_garantia DATE NULL,

    contrato_manutencao VARCHAR(30) NULL,
    tipo_contrato VARCHAR(80) NULL,
    entidade_responsavel VARCHAR(150) NULL,

    ultima_manutencao DATE NULL,
    proxima_manutencao DATE NULL,
    periodicidade_manutencao VARCHAR(50) NULL,

    ultima_calibracao DATE NULL,
    proxima_calibracao DATE NULL,

    responsavel_tecnico VARCHAR(150) NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

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

    id_equipamento INT NOT NULL,
    id_fornecedor INT NOT NULL,

    contrato_associado VARCHAR(100) NULL,
    data_inicio DATE NULL,
    data_fim DATE NULL,

    observacoes TEXT,

    isActive TINYINT(1) NOT NULL DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_equipamento_fornecedor_equipamento
        FOREIGN KEY (id_equipamento)
        REFERENCES equipamentos(id_equipamento),

    CONSTRAINT fk_equipamento_fornecedor_fornecedor
        FOREIGN KEY (id_fornecedor)
        REFERENCES fornecedores(id_fornecedor),

    CONSTRAINT uk_equipamento_fornecedor
        UNIQUE (id_equipamento, id_fornecedor)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

