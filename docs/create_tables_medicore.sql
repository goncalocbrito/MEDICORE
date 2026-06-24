CREATE TABLE `acessorios_equipamento` (
	`id_acessorio` INT(10) NOT NULL AUTO_INCREMENT,
	`id_equipamento` INT(10) NOT NULL,
	`id_localizacao` INT(10) NOT NULL,
	`numero_sequencial` INT(10) NOT NULL,
	`designacao` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`tipo` ENUM('sensor','cabo','modulo','consumivel_reutilizavel','adaptador','bateria','outro') NOT NULL DEFAULT 'outro' COLLATE 'utf8mb4_unicode_ci',
	`fabricante` VARCHAR(120) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`id_fornecedor` INT(10) NULL DEFAULT NULL,
	`modelo` VARCHAR(120) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`numero_serie` VARCHAR(120) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`data_aquisicao` DATE NULL DEFAULT NULL,
	`estado` ENUM('ativo','inativo','avariado','em_manutencao','em_calibracao','abatido') NOT NULL DEFAULT 'ativo' COLLATE 'utf8mb4_unicode_ci',
	`requer_manutencao` TINYINT(1) NOT NULL DEFAULT '0',
	`periodicidade_manutencao` ENUM('semestral','anual','bienal','trienal') NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`requer_calibracao` TINYINT(1) NOT NULL DEFAULT '0',
	`periodicidade_calibracao` ENUM('semestral','anual','bienal','trienal') NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`id_fornecedor_garantia` INT(10) NULL DEFAULT NULL,
	`data_inicio_garantia` DATE NULL DEFAULT NULL,
	`data_fim_garantia` DATE NULL DEFAULT NULL,
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id_acessorio`) USING BTREE,
	UNIQUE INDEX `uk_acessorio_numero_por_equipamento` (`id_equipamento`, `numero_sequencial`) USING BTREE,
	UNIQUE INDEX `uk_acessorio_numero_serie` (`numero_serie`) USING BTREE,
	INDEX `fk_acessorio_fornecedor_garantia` (`id_fornecedor_garantia`) USING BTREE,
	INDEX `fk_acessorios_localizacao` (`id_localizacao`) USING BTREE,
	INDEX `fk_acessorio_fornecedor` (`id_fornecedor`) USING BTREE,
	CONSTRAINT `fk_acessorios_localizacao` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id_localizacao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_acessorio_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_acessorio_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id_fornecedor`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_acessorio_fornecedor_garantia` FOREIGN KEY (`id_fornecedor_garantia`) REFERENCES `fornecedores` (`id_fornecedor`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=29
;

CREATE TABLE `avarias_reportadas` (
	`id_avaria` INT(10) NOT NULL AUTO_INCREMENT,
	`codigo_avaria` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_bin',
	`id_equipamento` INT(10) NOT NULL,
	`id_acessorio` INT(10) NULL DEFAULT NULL,
	`id_utilizador_reportou` INT(10) NOT NULL,
	`descricao_avaria` VARCHAR(500) NOT NULL COLLATE 'utf8mb4_bin',
	`estado` ENUM('reportada','em_analise','convertida_manutencao','cancelada') NOT NULL DEFAULT 'reportada' COLLATE 'utf8mb4_bin',
	`id_manutencao` INT(10) NULL DEFAULT NULL,
	`data_reporte` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id_avaria`) USING BTREE,
	UNIQUE INDEX `codigo_avaria` (`codigo_avaria`) USING BTREE,
	INDEX `id_equipamento` (`id_equipamento`) USING BTREE,
	INDEX `id_acessorio` (`id_acessorio`) USING BTREE,
	INDEX `id_utilizador_reportou` (`id_utilizador_reportou`) USING BTREE,
	INDEX `id_manutencao` (`id_manutencao`) USING BTREE,
	CONSTRAINT `avarias_reportadas_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `avarias_reportadas_ibfk_2` FOREIGN KEY (`id_acessorio`) REFERENCES `acessorios_equipamento` (`id_acessorio`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `avarias_reportadas_ibfk_3` FOREIGN KEY (`id_utilizador_reportou`) REFERENCES `utilizadores` (`id_utilizador`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `avarias_reportadas_ibfk_4` FOREIGN KEY (`id_manutencao`) REFERENCES `manutencoes_equipamento` (`id_manutencao`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
AUTO_INCREMENT=3
;

CREATE TABLE `calibracoes_acessorios` (
	`id_calibracao_acessorio` INT(10) NOT NULL AUTO_INCREMENT,
	`id_calibracao` INT(10) NOT NULL,
	`id_acessorio` INT(10) NOT NULL,
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_calibracao_acessorio`) USING BTREE,
	UNIQUE INDEX `id_calibracao` (`id_calibracao`, `id_acessorio`) USING BTREE,
	INDEX `id_acessorio` (`id_acessorio`) USING BTREE,
	CONSTRAINT `calibracoes_acessorios_ibfk_1` FOREIGN KEY (`id_calibracao`) REFERENCES `calibracoes_equipamento` (`id_calibracao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `calibracoes_acessorios_ibfk_2` FOREIGN KEY (`id_acessorio`) REFERENCES `acessorios_equipamento` (`id_acessorio`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
AUTO_INCREMENT=3
;

CREATE TABLE `calibracoes_consumiveis` (
	`id_calibracao_consumivel` INT(10) NOT NULL AUTO_INCREMENT,
	`id_calibracao` INT(10) NOT NULL,
	`id_consumivel` INT(10) NOT NULL,
	`quantidade_utilizada` DECIMAL(10,2) NOT NULL DEFAULT '1.00',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_calibracao_consumivel`) USING BTREE,
	UNIQUE INDEX `id_calibracao` (`id_calibracao`, `id_consumivel`) USING BTREE,
	INDEX `id_consumivel` (`id_consumivel`) USING BTREE,
	CONSTRAINT `calibracoes_consumiveis_ibfk_1` FOREIGN KEY (`id_calibracao`) REFERENCES `calibracoes_equipamento` (`id_calibracao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `calibracoes_consumiveis_ibfk_2` FOREIGN KEY (`id_consumivel`) REFERENCES `consumiveis` (`id_consumivel`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
;

CREATE TABLE `calibracoes_equipamento` (
	`id_calibracao` INT(10) NOT NULL AUTO_INCREMENT,
	`codigo_processo` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`id_equipamento` INT(10) NOT NULL,
	`id_acessorio` INT(10) NULL DEFAULT NULL,
	`id_fornecedor_responsavel` INT(10) NULL DEFAULT NULL,
	`tipo_execucao` ENUM('interna','externa') NOT NULL DEFAULT 'externa' COLLATE 'utf8mb4_unicode_ci',
	`estado_processo` ENUM('aguarda_decisao','aprovado','reprovado','cancelado','aguarda_recolha','procedimento_a_decorrer','procedimento_efetuado','emissao_relatorio','devolucao_equipamento','processo_finalizado') NOT NULL DEFAULT 'aguarda_decisao' COLLATE 'utf8mb4_unicode_ci',
	`decisao_admin` ENUM('pendente','aprovado','reprovado') NOT NULL DEFAULT 'pendente' COLLATE 'utf8mb4_unicode_ci',
	`id_admin_decisao` INT(10) NULL DEFAULT NULL,
	`data_decisao` DATETIME NULL DEFAULT NULL,
	`motivo_decisao` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`data_abertura` DATE NULL DEFAULT NULL,
	`data_prevista` DATE NULL DEFAULT NULL,
	`data_recolha` DATE NULL DEFAULT NULL,
	`data_inicio_procedimento` DATE NULL DEFAULT NULL,
	`data_fim_procedimento` DATE NULL DEFAULT NULL,
	`data_emissao_relatorio` DATE NULL DEFAULT NULL,
	`data_devolucao` DATE NULL DEFAULT NULL,
	`data_finalizacao` DATE NULL DEFAULT NULL,
	`tecnico_interno` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`data_calibracao` DATE NULL DEFAULT NULL,
	`proxima_calibracao` DATE NULL DEFAULT NULL,
	`numero_certificado` VARCHAR(120) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`resultado` ENUM('aprovado','aprovado_com_restricoes','reprovado') NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`procedimento` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`coberta_por_garantia` TINYINT(1) NOT NULL DEFAULT '0',
	`custo` DECIMAL(10,2) NULL DEFAULT NULL,
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id_calibracao`) USING BTREE,
	UNIQUE INDEX `uk_calibracao_codigo_processo` (`codigo_processo`) USING BTREE,
	INDEX `fk_calibracao_equipamento` (`id_equipamento`) USING BTREE,
	INDEX `fk_calibracao_fornecedor` (`id_fornecedor_responsavel`) USING BTREE,
	INDEX `fk_calibracao_acessorio` (`id_acessorio`) USING BTREE,
	INDEX `fk_calibracao_admin_decisao` (`id_admin_decisao`) USING BTREE,
	CONSTRAINT `fk_calibracao_acessorio` FOREIGN KEY (`id_acessorio`) REFERENCES `acessorios_equipamento` (`id_acessorio`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_calibracao_admin_decisao` FOREIGN KEY (`id_admin_decisao`) REFERENCES `utilizadores` (`id_utilizador`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_calibracao_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_calibracao_fornecedor` FOREIGN KEY (`id_fornecedor_responsavel`) REFERENCES `fornecedores` (`id_fornecedor`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=17
;

CREATE TABLE `consumiveis` (
	`id_consumivel` INT(10) NOT NULL AUTO_INCREMENT,
	`id_equipamento` INT(10) NULL DEFAULT NULL,
	`codigo_consumivel` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_bin',
	`nome` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_bin',
	`categoria` ENUM('eletrodos','papel_tecnico','filtros','circuitos_descartaveis','gel_contacto','sensores_descartaveis','reagente_calibracao','material_calibracao','outro') NOT NULL DEFAULT 'outro' COLLATE 'utf8mb4_bin',
	`stock_atual` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
	`stock_minimo` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
	`stock_maximo` DECIMAL(10,2) NULL DEFAULT NULL,
	`preco_unitario` DECIMAL(10,2) NULL DEFAULT NULL,
	`id_localizacao` INT(10) NULL DEFAULT NULL,
	`id_fornecedor_preferencial` INT(10) NULL DEFAULT NULL,
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id_consumivel`) USING BTREE,
	UNIQUE INDEX `codigo_consumivel` (`codigo_consumivel`) USING BTREE,
	INDEX `id_fornecedor_preferencial` (`id_fornecedor_preferencial`) USING BTREE,
	INDEX `fk_consumiveis_localizacao` (`id_localizacao`) USING BTREE,
	CONSTRAINT `consumiveis_ibfk_1` FOREIGN KEY (`id_fornecedor_preferencial`) REFERENCES `fornecedores` (`id_fornecedor`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_consumiveis_localizacao` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id_localizacao`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
AUTO_INCREMENT=5
;

CREATE TABLE `consumiveis_equipamentos` (
	`id_consumivel_equipamento` INT(10) NOT NULL AUTO_INCREMENT,
	`id_consumivel` INT(10) NOT NULL,
	`id_equipamento` INT(10) NOT NULL,
	`necessario_utilizacao` TINYINT(1) NOT NULL DEFAULT '1',
	`necessario_calibracao` TINYINT(1) NOT NULL DEFAULT '0',
	`quantidade_prevista` DECIMAL(10,2) NULL DEFAULT NULL,
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id_consumivel_equipamento`) USING BTREE,
	UNIQUE INDEX `id_consumivel` (`id_consumivel`, `id_equipamento`) USING BTREE,
	INDEX `id_equipamento` (`id_equipamento`) USING BTREE,
	CONSTRAINT `consumiveis_equipamentos_ibfk_1` FOREIGN KEY (`id_consumivel`) REFERENCES `consumiveis` (`id_consumivel`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `consumiveis_equipamentos_ibfk_2` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
;

CREATE TABLE `documentos_equipamentos` (
	`id_documento_equipamento` INT(10) NOT NULL AUTO_INCREMENT,
	`id_equipamento` INT(10) NOT NULL,
	`id_acessorio` INT(10) NULL DEFAULT NULL,
	`id_manutencao` INT(10) NULL DEFAULT NULL,
	`id_calibracao` INT(10) NULL DEFAULT NULL,
	`id_equipamento_fornecedor` INT(10) NULL DEFAULT NULL,
	`tipo_documento` ENUM('manual_instrucoes','datasheet','contrato','garantia','contrato_aquisicao','contrato_garantia','contrato_manutencao','contrato_calibracao','certificado_calibracao','relatorio_calibracao','relatorio_manutencao','ficha_tecnica','declaracao_conformidade','fotografia','outro') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nome_documento` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`caminho_ficheiro` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`data_documento` DATE NULL DEFAULT NULL,
	`data_validade` DATE NULL DEFAULT NULL,
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id_documento_equipamento`) USING BTREE,
	INDEX `fk_documento_equipamento` (`id_equipamento`) USING BTREE,
	INDEX `fk_documento_manutencao` (`id_manutencao`) USING BTREE,
	INDEX `fk_documento_calibracao` (`id_calibracao`) USING BTREE,
	INDEX `fk_documento_equipamento_fornecedor` (`id_equipamento_fornecedor`) USING BTREE,
	INDEX `fk_documento_acessorio` (`id_acessorio`) USING BTREE,
	CONSTRAINT `fk_documento_acessorio` FOREIGN KEY (`id_acessorio`) REFERENCES `acessorios_equipamento` (`id_acessorio`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_documento_calibracao` FOREIGN KEY (`id_calibracao`) REFERENCES `calibracoes_equipamento` (`id_calibracao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_documento_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_documento_equipamento_fornecedor` FOREIGN KEY (`id_equipamento_fornecedor`) REFERENCES `equipamentos_fornecedores` (`id_equipamento_fornecedor`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_documento_manutencao` FOREIGN KEY (`id_manutencao`) REFERENCES `manutencoes_equipamento` (`id_manutencao`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=20
;

CREATE TABLE `emprestimos_equipamentos` (
	`id_emprestimo` INT(10) NOT NULL AUTO_INCREMENT,
	`codigo_emprestimo` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_bin',
	`id_equipamento` INT(10) NOT NULL,
	`id_localizacao_origem` INT(10) NOT NULL,
	`id_localizacao_destino` INT(10) NOT NULL,
	`id_utilizador_pedido` INT(10) NOT NULL,
	`id_utilizador_termino` INT(10) NULL DEFAULT NULL,
	`responsavel_emprestimo` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_bin',
	`motivo` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`data_inicio` DATE NOT NULL,
	`data_prevista_devolucao` DATE NOT NULL,
	`data_termino` DATE NULL DEFAULT NULL,
	`estado` ENUM('pendente','ativo','rejeitado','terminado','atrasado') NOT NULL DEFAULT 'pendente' COLLATE 'utf8mb4_bin',
	`observacoes` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`id_utilizador_aprovacao` INT(10) NULL DEFAULT NULL,
	`data_aprovacao` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id_emprestimo`) USING BTREE,
	UNIQUE INDEX `codigo_emprestimo` (`codigo_emprestimo`) USING BTREE,
	INDEX `id_equipamento` (`id_equipamento`) USING BTREE,
	INDEX `id_localizacao_origem` (`id_localizacao_origem`) USING BTREE,
	INDEX `id_localizacao_destino` (`id_localizacao_destino`) USING BTREE,
	INDEX `id_utilizador_pedido` (`id_utilizador_pedido`) USING BTREE,
	INDEX `id_utilizador_termino` (`id_utilizador_termino`) USING BTREE,
	CONSTRAINT `emprestimos_equipamentos_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `emprestimos_equipamentos_ibfk_2` FOREIGN KEY (`id_localizacao_origem`) REFERENCES `localizacoes` (`id_localizacao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `emprestimos_equipamentos_ibfk_3` FOREIGN KEY (`id_localizacao_destino`) REFERENCES `localizacoes` (`id_localizacao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `emprestimos_equipamentos_ibfk_4` FOREIGN KEY (`id_utilizador_pedido`) REFERENCES `utilizadores` (`id_utilizador`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `emprestimos_equipamentos_ibfk_5` FOREIGN KEY (`id_utilizador_termino`) REFERENCES `utilizadores` (`id_utilizador`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
AUTO_INCREMENT=6
;

CREATE TABLE `equipamentos` (
	`id_equipamento` INT(10) NOT NULL AUTO_INCREMENT,
	`id_familia_equipamento` INT(10) NOT NULL,
	`numero_sequencial` INT(10) NOT NULL,
	`codigo_equipamento` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`designacao` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`modelo` VARCHAR(120) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`marca` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`numero_serie` VARCHAR(120) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`tipo_entrada` ENUM('compra','doacao','emprestimo') NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`valor_aquisicao` DECIMAL(10,2) NULL DEFAULT NULL,
	`id_localizacao` INT(10) NOT NULL,
	`estado` ENUM('ativo','avariado','em_manutencao','em_calibracao','inativo','abatido') NOT NULL DEFAULT 'ativo' COLLATE 'utf8mb4_unicode_ci',
	`criticidade` ENUM('baixa','media','alta','critica') NOT NULL DEFAULT 'media' COLLATE 'utf8mb4_unicode_ci',
	`periodicidade_manutencao` ENUM('semestral','anual','bienal','trienal') NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`periodicidade_calibracao` ENUM('semestral','anual','bienal','trienal') NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`id_responsavel` INT(10) NULL DEFAULT NULL,
	`data_fabrico` DATE NULL DEFAULT NULL,
	`data_aquisicao` DATE NULL DEFAULT NULL,
	`data_instalacao` DATE NULL DEFAULT NULL,
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id_equipamento`) USING BTREE,
	UNIQUE INDEX `codigo_equipamento` (`codigo_equipamento`) USING BTREE,
	UNIQUE INDEX `numero_serie` (`numero_serie`) USING BTREE,
	UNIQUE INDEX `uk_familia_numero` (`id_familia_equipamento`, `numero_sequencial`) USING BTREE,
	INDEX `fk_equipamento_localizacao` (`id_localizacao`) USING BTREE,
	INDEX `fk_equipamentos_responsavel` (`id_responsavel`) USING BTREE,
	CONSTRAINT `fk_equipamentos_responsavel` FOREIGN KEY (`id_responsavel`) REFERENCES `utilizadores` (`id_utilizador`) ON UPDATE CASCADE ON DELETE SET NULL,
	CONSTRAINT `fk_equipamento_familia` FOREIGN KEY (`id_familia_equipamento`) REFERENCES `familias_equipamento` (`id_familia_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_equipamento_localizacao` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id_localizacao`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=15
;

CREATE TABLE `equipamentos_fornecedores` (
	`id_equipamento_fornecedor` INT(10) NOT NULL AUTO_INCREMENT,
	`id_equipamento` INT(10) NOT NULL,
	`id_fornecedor_garantia` INT(10) NULL DEFAULT NULL,
	`data_inicio_garantia` DATE NULL DEFAULT NULL,
	`data_fim_garantia` DATE NULL DEFAULT NULL,
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id_equipamento_fornecedor`) USING BTREE,
	UNIQUE INDEX `id_equipamento` (`id_equipamento`) USING BTREE,
	INDEX `fk_eq_forn_garantia` (`id_fornecedor_garantia`) USING BTREE,
	CONSTRAINT `fk_eq_forn_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_eq_forn_garantia` FOREIGN KEY (`id_fornecedor_garantia`) REFERENCES `fornecedores` (`id_fornecedor`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=27
;

CREATE TABLE `familias_equipamento` (
	`id_familia_equipamento` INT(10) NOT NULL AUTO_INCREMENT,
	`codigo_familia` VARCHAR(10) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nome` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`descricao` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id_familia_equipamento`) USING BTREE,
	UNIQUE INDEX `codigo_familia` (`codigo_familia`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=11
;

CREATE TABLE `fornecedores` (
	`id_fornecedor` INT(10) NOT NULL AUTO_INCREMENT,
	`nome_empresa` VARCHAR(180) NOT NULL COLLATE 'utf8mb4_bin',
	`tipo_fornecedor` ENUM('Manutenção','Comercial','Fabricante','Calibração') NOT NULL COLLATE 'utf8mb4_bin',
	`nif` INT(10) NOT NULL,
	`telefone` INT(10) NULL DEFAULT NULL,
	`email_fornecedor` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`pessoa_responsavel` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`telefone_contacto` INT(10) NULL DEFAULT NULL,
	`email_contacto` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`morada` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`codigo_postal` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`localidade` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`pais` VARCHAR(80) NOT NULL DEFAULT 'Portugal' COLLATE 'utf8mb4_bin',
	`observacoes` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id_fornecedor`) USING BTREE,
	UNIQUE INDEX `nif` (`nif`) USING BTREE,
	UNIQUE INDEX `uk_fornecedor_nif` (`nif`) USING BTREE
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
AUTO_INCREMENT=14
;

CREATE TABLE `historico_equipamentos` (
	`id_historico_equipamento` INT(10) NOT NULL AUTO_INCREMENT,
	`id_equipamento` INT(10) NOT NULL,
	`id_localizacao` INT(10) NULL DEFAULT NULL,
	`id_localizacao_origem` INT(10) NULL DEFAULT NULL,
	`id_localizacao_destino` INT(10) NULL DEFAULT NULL,
	`id_utilizador` INT(10) NULL DEFAULT NULL,
	`tipo_evento` ENUM('criacao','alteracao_localizacao','transferencia_pendente','transferencia_aprovada','transferencia_rejeitada','emprestimo_pendente','emprestimo_iniciado','emprestimo_rejeitado','emprestimo_terminado','manutencao','calibracao','alteracao_dados') NOT NULL COLLATE 'utf8mb4_bin',
	`referencia_tabela` VARCHAR(80) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`referencia_id` INT(10) NULL DEFAULT NULL,
	`descricao` VARCHAR(500) NOT NULL COLLATE 'utf8mb4_bin',
	`data_evento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id_historico_equipamento`) USING BTREE,
	INDEX `id_equipamento` (`id_equipamento`) USING BTREE,
	INDEX `id_localizacao` (`id_localizacao`) USING BTREE,
	INDEX `id_utilizador` (`id_utilizador`) USING BTREE,
	INDEX `fk_historico_localizacao_origem` (`id_localizacao_origem`) USING BTREE,
	INDEX `fk_historico_localizacao_destino` (`id_localizacao_destino`) USING BTREE,
	CONSTRAINT `fk_historico_localizacao_destino` FOREIGN KEY (`id_localizacao_destino`) REFERENCES `localizacoes` (`id_localizacao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_historico_localizacao_origem` FOREIGN KEY (`id_localizacao_origem`) REFERENCES `localizacoes` (`id_localizacao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `historico_equipamentos_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `historico_equipamentos_ibfk_2` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id_localizacao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `historico_equipamentos_ibfk_3` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizadores` (`id_utilizador`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
AUTO_INCREMENT=31
;


CREATE TABLE `historico_utilizadores` (
	`id_historico_utilizador` INT(10) NOT NULL AUTO_INCREMENT,
	`id_utilizador_alvo` INT(10) NULL DEFAULT NULL,
	`codigo_utilizador` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`acao` ENUM('criacao_utilizador','edicao_utilizador','remocao_utilizador','reativacao_utilizador') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`campo_alterado` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`valor_anterior` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`valor_novo` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`realizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`data_registo` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_historico_utilizador`) USING BTREE,
	INDEX `fk_historico_utilizadores_alvo` (`id_utilizador_alvo`) USING BTREE,
	CONSTRAINT `fk_historico_utilizadores_alvo` FOREIGN KEY (`id_utilizador_alvo`) REFERENCES `utilizadores` (`id_utilizador`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=2
;

CREATE TABLE `localizacoes` (
	`id_localizacao` INT(10) NOT NULL AUTO_INCREMENT,
	`codigo` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`departamento_nome` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`departamento_sigla` VARCHAR(20) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`edificio` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`piso` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`sala` VARCHAR(80) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`tipo_espaco` VARCHAR(80) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`estado` VARCHAR(30) NOT NULL DEFAULT 'Ativa' COLLATE 'utf8mb4_unicode_ci',
	`capacidade_equipamentos` INT(10) NULL DEFAULT NULL,
	`permite_equipamentos_criticos` TINYINT(1) NOT NULL DEFAULT '0',
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id_localizacao`) USING BTREE,
	UNIQUE INDEX `codigo` (`codigo`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=32
;

CREATE TABLE `manutencoes_acessorios` (
	`id_manutencao_acessorio` INT(10) NOT NULL AUTO_INCREMENT,
	`id_manutencao` INT(10) NOT NULL,
	`id_acessorio` INT(10) NOT NULL,
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_manutencao_acessorio`) USING BTREE,
	UNIQUE INDEX `id_manutencao` (`id_manutencao`, `id_acessorio`) USING BTREE,
	INDEX `id_acessorio` (`id_acessorio`) USING BTREE,
	CONSTRAINT `manutencoes_acessorios_ibfk_1` FOREIGN KEY (`id_manutencao`) REFERENCES `manutencoes_equipamento` (`id_manutencao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `manutencoes_acessorios_ibfk_2` FOREIGN KEY (`id_acessorio`) REFERENCES `acessorios_equipamento` (`id_acessorio`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
AUTO_INCREMENT=3
;

CREATE TABLE `manutencoes_consumiveis` (
	`id_manutencao_consumivel` INT(10) NOT NULL AUTO_INCREMENT,
	`id_manutencao` INT(10) NOT NULL,
	`id_consumivel` INT(10) NOT NULL,
	`quantidade_utilizada` DECIMAL(10,2) NOT NULL DEFAULT '1.00',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_manutencao_consumivel`) USING BTREE,
	UNIQUE INDEX `id_manutencao` (`id_manutencao`, `id_consumivel`) USING BTREE,
	INDEX `id_consumivel` (`id_consumivel`) USING BTREE,
	CONSTRAINT `manutencoes_consumiveis_ibfk_1` FOREIGN KEY (`id_manutencao`) REFERENCES `manutencoes_equipamento` (`id_manutencao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `manutencoes_consumiveis_ibfk_2` FOREIGN KEY (`id_consumivel`) REFERENCES `consumiveis` (`id_consumivel`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
;

CREATE TABLE `manutencoes_equipamento` (
	`id_manutencao` INT(10) NOT NULL AUTO_INCREMENT,
	`codigo_processo` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`id_equipamento` INT(10) NOT NULL,
	`id_acessorio` INT(10) NULL DEFAULT NULL,
	`tipo_manutencao` ENUM('preventiva','corretiva') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`tipo_execucao` ENUM('interna','externa') NOT NULL DEFAULT 'externa' COLLATE 'utf8mb4_unicode_ci',
	`estado_processo` ENUM('aguarda_decisao','aprovado','reprovado','cancelado','aguarda_recolha','procedimento_a_decorrer','procedimento_efetuado','emissao_relatorio','devolucao_equipamento','processo_finalizado') NOT NULL DEFAULT 'aguarda_decisao' COLLATE 'utf8mb4_unicode_ci',
	`decisao_admin` ENUM('pendente','aprovado','reprovado') NOT NULL DEFAULT 'pendente' COLLATE 'utf8mb4_unicode_ci',
	`id_admin_decisao` INT(10) NULL DEFAULT NULL,
	`data_decisao` DATETIME NULL DEFAULT NULL,
	`motivo_decisao` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`data_abertura` DATE NULL DEFAULT NULL,
	`data_prevista` DATE NULL DEFAULT NULL,
	`data_recolha` DATE NULL DEFAULT NULL,
	`data_inicio_procedimento` DATE NULL DEFAULT NULL,
	`data_fim_procedimento` DATE NULL DEFAULT NULL,
	`data_emissao_relatorio` DATE NULL DEFAULT NULL,
	`data_devolucao` DATE NULL DEFAULT NULL,
	`data_finalizacao` DATE NULL DEFAULT NULL,
	`id_fornecedor_responsavel` INT(10) NULL DEFAULT NULL,
	`tecnico_interno` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`data_manutencao` DATE NULL DEFAULT NULL,
	`proxima_manutencao` DATE NULL DEFAULT NULL,
	`numero_relatorio` VARCHAR(120) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`descricao_procedimento` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`resultado` ENUM('aprovado','aprovado_com_restricoes','reprovado') NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`coberta_por_garantia` TINYINT(1) NOT NULL DEFAULT '0',
	`custo` DECIMAL(10,2) NULL DEFAULT NULL,
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id_manutencao`) USING BTREE,
	UNIQUE INDEX `uk_manutencao_codigo_processo` (`codigo_processo`) USING BTREE,
	INDEX `fk_manutencao_equipamento` (`id_equipamento`) USING BTREE,
	INDEX `fk_manutencao_fornecedor` (`id_fornecedor_responsavel`) USING BTREE,
	INDEX `fk_manutencao_acessorio` (`id_acessorio`) USING BTREE,
	INDEX `fk_manutencao_admin_decisao` (`id_admin_decisao`) USING BTREE,
	CONSTRAINT `fk_manutencao_admin_decisao` FOREIGN KEY (`id_admin_decisao`) REFERENCES `utilizadores` (`id_utilizador`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_manutencao_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `fk_manutencao_fornecedor` FOREIGN KEY (`id_fornecedor_responsavel`) REFERENCES `fornecedores` (`id_fornecedor`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=32
;

CREATE TABLE `movimentos_stock_consumiveis` (
	`id_movimento_stock` INT(10) NOT NULL AUTO_INCREMENT,
	`id_consumivel` INT(10) NOT NULL,
	`tipo_movimento` ENUM('entrada','saida','ajuste','consumo_calibracao','devolucao') NOT NULL COLLATE 'utf8mb4_bin',
	`quantidade` DECIMAL(10,2) NOT NULL,
	`stock_anterior` DECIMAL(10,2) NULL DEFAULT NULL,
	`stock_posterior` DECIMAL(10,2) NULL DEFAULT NULL,
	`id_equipamento` INT(10) NULL DEFAULT NULL,
	`id_acessorio` INT(10) NULL DEFAULT NULL,
	`id_calibracao` INT(10) NULL DEFAULT NULL,
	`motivo` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`data_movimento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id_movimento_stock`) USING BTREE,
	INDEX `id_consumivel` (`id_consumivel`) USING BTREE,
	INDEX `id_equipamento` (`id_equipamento`) USING BTREE,
	INDEX `id_acessorio` (`id_acessorio`) USING BTREE,
	INDEX `id_calibracao` (`id_calibracao`) USING BTREE,
	CONSTRAINT `movimentos_stock_consumiveis_ibfk_1` FOREIGN KEY (`id_consumivel`) REFERENCES `consumiveis` (`id_consumivel`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `movimentos_stock_consumiveis_ibfk_2` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `movimentos_stock_consumiveis_ibfk_3` FOREIGN KEY (`id_acessorio`) REFERENCES `acessorios_equipamento` (`id_acessorio`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `movimentos_stock_consumiveis_ibfk_4` FOREIGN KEY (`id_calibracao`) REFERENCES `calibracoes_equipamento` (`id_calibracao`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
AUTO_INCREMENT=6
;

CREATE TABLE `pagina_publica_config` (
	`id_config` INT(10) NOT NULL AUTO_INCREMENT,
	`navbar_logo` VARCHAR(255) NOT NULL DEFAULT 'assets/img/MEDICORE_logotipo_branco.png' COLLATE 'utf8mb4_unicode_ci',
	`navbar_link_sobre` VARCHAR(80) NOT NULL DEFAULT 'Sobre' COLLATE 'utf8mb4_unicode_ci',
	`navbar_link_equipa` VARCHAR(80) NOT NULL DEFAULT 'Nossa Equipa' COLLATE 'utf8mb4_unicode_ci',
	`navbar_link_funcional` VARCHAR(80) NOT NULL DEFAULT 'Funcionalidades' COLLATE 'utf8mb4_unicode_ci',
	`navbar_link_hospitais` VARCHAR(80) NOT NULL DEFAULT 'Hospitais e Clínicas' COLLATE 'utf8mb4_unicode_ci',
	`navbar_link_contacto` VARCHAR(80) NOT NULL DEFAULT 'Contacto' COLLATE 'utf8mb4_unicode_ci',
	`navbar_btn_restrita` VARCHAR(80) NOT NULL DEFAULT 'Área Restrita' COLLATE 'utf8mb4_unicode_ci',
	`sobre_titulo` VARCHAR(255) NOT NULL DEFAULT 'Gestão Inteligente do Inventário Hospitalar' COLLATE 'utf8mb4_unicode_ci',
	`sobre_texto` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`contacto_texto` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`rodape_localizacao` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`rodape_horario_semana` VARCHAR(100) NOT NULL DEFAULT '2ª a 6ª Feira: 9h — 18h' COLLATE 'utf8mb4_unicode_ci',
	`rodape_email` VARCHAR(150) NOT NULL DEFAULT 'geral@medicore.pt' COLLATE 'utf8mb4_unicode_ci',
	`rodape_telefone` VARCHAR(30) NOT NULL DEFAULT '+351 919 323 121' COLLATE 'utf8mb4_unicode_ci',
	`atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id_config`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=2
;

CREATE TABLE `pagina_publica_hospitais` (
	`id_hospital` INT(10) NOT NULL AUTO_INCREMENT,
	`ordem` INT(10) NOT NULL DEFAULT '0',
	`nome` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`descricao` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`imagem` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_hospital`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=5
;

CREATE TABLE `pagina_publica_slides` (
	`id_slide` INT(10) NOT NULL AUTO_INCREMENT,
	`ordem` INT(10) NOT NULL DEFAULT '0',
	`imagem` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`titulo` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`descricao` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_slide`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=4
;

CREATE TABLE `transferencias_equipamentos` (
	`id_transferencia` INT(10) NOT NULL AUTO_INCREMENT,
	`codigo_transferencia` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_bin',
	`id_equipamento` INT(10) NOT NULL,
	`id_localizacao_origem` INT(10) NOT NULL,
	`id_localizacao_destino` INT(10) NOT NULL,
	`id_utilizador_pedido` INT(10) NOT NULL,
	`id_utilizador_aprovacao` INT(10) NULL DEFAULT NULL,
	`motivo` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`estado` ENUM('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente' COLLATE 'utf8mb4_bin',
	`data_pedido` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`data_aprovacao` DATETIME NULL DEFAULT NULL,
	`observacoes` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id_transferencia`) USING BTREE,
	UNIQUE INDEX `codigo_transferencia` (`codigo_transferencia`) USING BTREE,
	INDEX `id_equipamento` (`id_equipamento`) USING BTREE,
	INDEX `id_localizacao_origem` (`id_localizacao_origem`) USING BTREE,
	INDEX `id_localizacao_destino` (`id_localizacao_destino`) USING BTREE,
	INDEX `id_utilizador_pedido` (`id_utilizador_pedido`) USING BTREE,
	INDEX `id_utilizador_aprovacao` (`id_utilizador_aprovacao`) USING BTREE,
	CONSTRAINT `transferencias_equipamentos_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id_equipamento`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `transferencias_equipamentos_ibfk_2` FOREIGN KEY (`id_localizacao_origem`) REFERENCES `localizacoes` (`id_localizacao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `transferencias_equipamentos_ibfk_3` FOREIGN KEY (`id_localizacao_destino`) REFERENCES `localizacoes` (`id_localizacao`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `transferencias_equipamentos_ibfk_4` FOREIGN KEY (`id_utilizador_pedido`) REFERENCES `utilizadores` (`id_utilizador`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `transferencias_equipamentos_ibfk_5` FOREIGN KEY (`id_utilizador_aprovacao`) REFERENCES `utilizadores` (`id_utilizador`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_bin'
ENGINE=InnoDB
AUTO_INCREMENT=6
;


CREATE TABLE `utilizadores` (
	`id_utilizador` INT(10) NOT NULL AUTO_INCREMENT,
	`codigo_utilizador` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nome` VARCHAR(180) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`tipo_utilizador` ENUM('Administrador','Engenheiro') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`estado` ENUM('Ativo','Inativo','Pendente') NOT NULL DEFAULT 'Ativo' COLLATE 'utf8mb4_unicode_ci',
	`cartao_cidadao` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nif` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`data_nascimento` DATE NULL DEFAULT NULL,
	`email` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`telefone` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`morada` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`codigo_postal` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`localidade` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`username` VARCHAR(80) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`password_hash` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`foto_perfil` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`ultimo_login` DATETIME NULL DEFAULT NULL,
	`isActive` TINYINT(1) NOT NULL DEFAULT '1',
	`criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`atualizado_por` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id_utilizador`) USING BTREE,
	UNIQUE INDEX `codigo_utilizador` (`codigo_utilizador`) USING BTREE,
	UNIQUE INDEX `cartao_cidadao` (`cartao_cidadao`) USING BTREE,
	UNIQUE INDEX `email` (`email`) USING BTREE,
	UNIQUE INDEX `username` (`username`) USING BTREE,
	UNIQUE INDEX `nif` (`nif`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=5
;
