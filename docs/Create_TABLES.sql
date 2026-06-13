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
