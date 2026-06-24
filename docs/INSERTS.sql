-- =============================================================================
-- MEDICORE - Script de Dados de Teste
-- Número de Aluno: 1230404
-- Unidade Curricular: Sistemas de Informação e Bases de Dados Aplicados à Saúde
-- Ano Letivo: 2025/2026
-- =============================================================================
-- ATENÇÃO: Executar APÓS o script create_tables_medicore.sql
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- UTILIZADORES
-- Passwords: admin → admin123 | jferreira → engenheiro123 | msantos → engenheiro123
-- =============================================================================
INSERT INTO `utilizadores`
    (`id_utilizador`, `codigo_utilizador`, `nome`, `tipo_utilizador`, `estado`,
     `cartao_cidadao`, `nif`, `data_nascimento`, `email`, `telefone`,
     `morada`, `codigo_postal`, `localidade`, `username`, `password_hash`, `isActive`)
VALUES
(1, 'ADM-001', 'Administrador do Sistema', 'Administrador', 'Ativo',
 '12345678', '123456789', '1980-05-15', 'admin@medicore.pt', '912000001',
 'Rua do Hospital, nº 1', '4000-001', 'Porto', 'admin',
 '$2y$10$XglSiUDsUENNtiJ7xzkfsut5t/ryISlDIxoQP81sMmzozOnRzRU5K', 1),

(2, 'ENG-001', 'João Ferreira', 'Engenheiro', 'Ativo',
 '23456789', '234567890', '1990-03-22', 'jferreira@medicore.pt', '912000002',
 'Avenida da Engenharia, nº 45', '4100-001', 'Porto', 'jferreira',
 '$2y$10$vArUmO2fsD7AVrzfFFbzsOCucBymnt2whswY0FgCRHt3qxbeqTOZK', 1),

(3, 'ENG-002', 'Maria Santos', 'Engenheiro', 'Ativo',
 '34567890', '345678901', '1988-07-10', 'msantos@medicore.pt', '912000003',
 'Rua das Flores, nº 12', '4200-001', 'Porto', 'msantos',
 '$2y$10$vArUmO2fsD7AVrzfFFbzsOCucBymnt2whswY0FgCRHt3qxbeqTOZK', 1);


-- =============================================================================
-- LOCALIZAÇÕES
-- =============================================================================
INSERT INTO `localizacoes`
    (`id_localizacao`, `codigo`, `departamento_nome`, `departamento_sigla`,
     `edificio`, `piso`, `sala`, `tipo_espaco`, `estado`,
     `capacidade_equipamentos`, `permite_equipamentos_criticos`, `isActive`)
VALUES
(1, 'URG-A0-01', 'Urgência Geral',                    'URG', 'Bloco A', 'Piso 0', 'Sala 01',  'Sala de Tratamento', 'Ativa', 10, 1, 1),
(2, 'CAR-B1-01', 'Cardiologia',                       'CAR', 'Bloco B', 'Piso 1', 'Sala 01',  'Sala de Exames',     'Ativa',  8, 1, 1),
(3, 'NEU-C2-01', 'Neurologia',                        'NEU', 'Bloco C', 'Piso 2', 'Sala 01',  'Sala de Consultas',  'Ativa',  6, 0, 1),
(4, 'ARM-D0-01', 'Armazém de Equipamentos',           'ARM', 'Bloco D', 'Piso 0', 'Armazém',  'Armazém',            'Ativa', 50, 0, 1),
(5, 'PED-B2-01', 'Pediatria',                         'PED', 'Bloco B', 'Piso 2', 'Sala 01',  'Sala de Tratamento', 'Ativa',  8, 1, 1),
(6, 'UCI-A1-01', 'Unidade de Cuidados Intensivos',    'UCI', 'Bloco A', 'Piso 1', 'UCI 1',    'UCI',                'Ativa', 12, 1, 1);


-- =============================================================================
-- FAMÍLIAS DE EQUIPAMENTO
-- =============================================================================
INSERT INTO `familias_equipamento`
    (`id_familia_equipamento`, `codigo_familia`, `nome`, `descricao`, `isActive`)
VALUES
(1, 'ECG',  'Eletrocardiógrafos',  'Equipamentos para registo de atividade elétrica cardíaca', 1),
(2, 'VENT', 'Ventiladores',        'Equipamentos de suporte ventilatório invasivo e não-invasivo', 1),
(3, 'MON',  'Monitores de Sinais', 'Monitores multiparamétricos de sinais vitais', 1),
(4, 'DEF',  'Desfibrilhadores',    'Equipamentos de desfibrilhação e monitorização cardíaca', 1),
(5, 'OXI',  'Oxímetros de Pulso',  'Equipamentos de medição de saturação de oxigénio', 1),
(6, 'INF',  'Bombas Infusoras',    'Equipamentos de administração controlada de medicação', 1);


-- =============================================================================
-- FORNECEDORES
-- =============================================================================
INSERT INTO `fornecedores`
    (`id_fornecedor`, `nome_empresa`, `tipo_fornecedor`, `nif`, `telefone`,
     `email_fornecedor`, `pessoa_responsavel`, `telefone_contacto`, `email_contacto`,
     `morada`, `codigo_postal`, `localidade`, `pais`, `isActive`)
VALUES
(1, 'Philips Healthcare Portugal', 'Fabricante',  500100001, 218000001,
   'geral@philips-healthcare.pt', 'Carlos Mendes', 912100001, 'carlos.mendes@philips-healthcare.pt',
   'Rua Filipe Folque, nº 12', '1050-113', 'Lisboa', 'Portugal', 1),

(2, 'Siemens Healthineers Portugal', 'Calibração', 500200002, 218000002,
   'geral@siemens-healthineers.pt', 'Ana Costa', 912200002, 'ana.costa@siemens-healthineers.pt',
   'Avenida José Malhoa, nº 16', '1099-017', 'Lisboa', 'Portugal', 1),

(3, 'MedEquip Manutenção Hospitalar', 'Manutenção', 500300003, 222000003,
   'geral@medequip.pt', 'Rui Oliveira', 912300003, 'rui.oliveira@medequip.pt',
   'Rua de Santa Catarina, nº 200', '4000-450', 'Porto', 'Portugal', 1),

(4, 'BioMedical Soluções', 'Comercial', 500400004, 219000004,
   'geral@biomedical.pt', 'Sofia Lopes', 912400004, 'sofia.lopes@biomedical.pt',
   'Estrada de Alfragide, nº 67', '2614-503', 'Amadora', 'Portugal', 1),

(5, 'GE Healthcare Portugal', 'Fabricante', 500500005, 217000005,
   'geral@ge-healthcare.pt', 'Pedro Nunes', 912500005, 'pedro.nunes@ge-healthcare.pt',
   'Rua Ramalho Ortigão, nº 51', '1099-090', 'Lisboa', 'Portugal', 1);


-- =============================================================================
-- CONSUMÍVEIS
-- =============================================================================
INSERT INTO `consumiveis`
    (`id_consumivel`, `codigo_consumivel`, `nome`, `categoria`,
     `stock_atual`, `stock_minimo`, `stock_maximo`, `preco_unitario`,
     `id_localizacao`, `id_fornecedor_preferencial`, `isActive`)
VALUES
(1, 'CONS-001', 'Papel Termossensível ECG (50 mm)',      'papel_tecnico',        50.00, 10.00, 200.00,  3.50, 4, 4, 1),
(2, 'CONS-002', 'Eletrodos Descartáveis Adulto (pack 50)', 'eletrodos',         120.00, 20.00, 500.00, 12.00, 4, 4, 1),
(3, 'CONS-003', 'Gel de Contacto ECG (250ml)',            'gel_contacto',        30.00,  5.00, 100.00,  4.80, 4, 4, 1),
(4, 'CONS-004', 'Reagente de Calibração Multimarca',      'reagente_calibracao',  8.00,  2.00,  20.00, 45.00, 4, 2, 1);


-- =============================================================================
-- EQUIPAMENTOS
-- =============================================================================
INSERT INTO `equipamentos`
    (`id_equipamento`, `id_familia_equipamento`, `numero_sequencial`, `codigo_equipamento`,
     `designacao`, `modelo`, `marca`, `numero_serie`, `tipo_entrada`, `valor_aquisicao`,
     `id_localizacao`, `estado`, `criticidade`,
     `periodicidade_manutencao`, `periodicidade_calibracao`,
     `id_responsavel`, `data_fabrico`, `data_aquisicao`, `data_instalacao`, `isActive`)
VALUES
(1, 1, 1, 'ECG-001', 'Eletrocardiógrafo 12 Derivações', 'PageWriter TC30', 'Philips',
   'SN-ECG-001-2021', 'compra', 8500.00, 2, 'ativo', 'alta', 'anual', 'anual',
   2, '2020-06-01', '2021-01-15', '2021-02-01', 1),

(2, 1, 2, 'ECG-002', 'Eletrocardiógrafo Portátil', 'PageWriter TC10', 'Philips',
   'SN-ECG-002-2021', 'compra', 5200.00, 1, 'ativo', 'media', 'anual', 'anual',
   2, '2020-09-01', '2021-03-10', '2021-03-20', 1),

(3, 3, 1, 'MON-001', 'Monitor Multiparamétrico', 'IntelliVue MX450', 'Philips',
   'SN-MON-001-2020', 'compra', 15000.00, 6, 'ativo', 'critica', 'semestral', 'anual',
   2, '2019-05-01', '2020-02-20', '2020-03-01', 1),

(4, 3, 2, 'MON-002', 'Monitor de Sinais Vitais', 'IntelliVue MX40', 'Philips',
   'SN-MON-002-2022', 'compra', 9800.00, 5, 'ativo', 'alta', 'anual', 'anual',
   2, '2021-11-01', '2022-01-10', '2022-01-25', 1),

(5, 4, 1, 'DEF-001', 'Desfibrilhador Semi-Automático', 'HeartStart FR3', 'Philips',
   'SN-DEF-001-2021', 'compra', 3200.00, 1, 'ativo', 'critica', 'semestral', 'semestral',
   2, '2021-01-01', '2021-06-01', '2021-06-15', 1),

(6, 2, 1, 'VENT-001', 'Ventilador de Cuidados Intensivos', 'Evita V500', 'Dräger',
   'SN-VENT-001-2019', 'compra', 42000.00, 6, 'ativo', 'critica', 'semestral', 'anual',
   2, '2018-03-01', '2019-01-10', '2019-02-01', 1),

(7, 6, 1, 'INF-001', 'Bomba Infusora Volumétrica', 'Infusomat Space', 'B.Braun',
   'SN-INF-001-2022', 'compra', 2800.00, 6, 'ativo', 'alta', 'anual', 'anual',
   3, '2022-04-01', '2022-07-01', '2022-07-15', 1),

(8, 5, 1, 'OXI-001', 'Oxímetro de Pulso de Mesa', 'Rad-97', 'Masimo',
   'SN-OXI-001-2023', 'compra', 1800.00, 3, 'ativo', 'media', 'anual', 'bienal',
   3, '2022-12-01', '2023-02-01', '2023-02-10', 1);


-- =============================================================================
-- ACESSÓRIOS DE EQUIPAMENTO
-- =============================================================================
INSERT INTO `acessorios_equipamento`
    (`id_acessorio`, `id_equipamento`, `id_localizacao`, `numero_sequencial`,
     `designacao`, `tipo`, `fabricante`, `id_fornecedor`, `modelo`, `numero_serie`,
     `data_aquisicao`, `estado`, `requer_calibracao`, `periodicidade_calibracao`, `isActive`)
VALUES
(1, 1, 2, 1, 'Cabo de Derivações para Membros',  'cabo',     'Philips', 1, 'M1510A',          'SN-CABO-ECG001-A',  '2021-02-01', 'ativo', 0, NULL,   1),
(2, 1, 2, 2, 'Cabo de Derivações Precordiais',   'cabo',     'Philips', 1, 'M1514A',          'SN-CABO-ECG001-B',  '2021-02-01', 'ativo', 0, NULL,   1),
(3, 3, 6, 1, 'Sensor SpO2 Adulto',               'sensor',   'Philips', 1, 'M1196A',          'SN-SENS-MON001-A',  '2020-03-01', 'ativo', 1, 'anual',1),
(4, 3, 6, 2, 'Manguito de Pressão Adulto',       'adaptador','Philips', 1, 'M1574A',          'SN-MANG-MON001-A',  '2020-03-01', 'ativo', 0, NULL,   1),
(5, 5, 1, 1, 'Eletrodos Adulto DEA',             'sensor',   'Philips', 1, 'M3714A',          'SN-EL-DEF001-A',    '2021-06-15', 'ativo', 0, NULL,   1),
(6, 6, 6, 1, 'Circuito de Paciente Adulto',      'modulo',   'Dräger',  NULL,'EvitaXL-CKT-A', 'SN-CKT-VENT001-A',  '2019-02-01', 'ativo', 0, NULL,   1),
(7, 2, 1, 1, 'Cabo USB / Transferência de Dados','cabo',     'Philips', 1, 'M1520A',          'SN-CABO-ECG002-A',  '2021-03-20', 'ativo', 0, NULL,   1);


-- =============================================================================
-- CONSUMÍVEIS POR EQUIPAMENTO
-- =============================================================================
INSERT INTO `consumiveis_equipamentos`
    (`id_consumivel`, `id_equipamento`, `necessario_utilizacao`, `necessario_calibracao`,
     `quantidade_prevista`, `isActive`)
VALUES
(1, 1, 1, 0, 1.00, 1),
(2, 1, 1, 0, 10.00, 1),
(3, 1, 1, 0, 1.00, 1),
(4, 1, 0, 1, 1.00, 1),
(1, 2, 1, 0, 1.00, 1),
(2, 2, 1, 0, 10.00, 1),
(2, 3, 1, 0, 5.00, 1),
(2, 4, 1, 0, 5.00, 1);


-- =============================================================================
-- EQUIPAMENTOS — FORNECEDORES / GARANTIA
-- =============================================================================
INSERT INTO `equipamentos_fornecedores`
    (`id_equipamento_fornecedor`, `id_equipamento`, `id_fornecedor_garantia`,
     `data_inicio_garantia`, `data_fim_garantia`, `isActive`)
VALUES
(1, 1, 1, '2021-01-15', '2024-01-15', 1),
(2, 2, 1, '2021-03-10', '2024-03-10', 1),
(3, 3, 1, '2020-02-20', '2023-02-20', 1),
(4, 4, 1, '2022-01-10', '2025-01-10', 1),
(5, 5, 1, '2021-06-01', '2024-06-01', 1),
(6, 6, 5, '2019-01-10', '2022-01-10', 1),
(7, 7, 4, '2022-07-01', '2025-07-01', 1),
(8, 8, 4, '2023-02-01', '2026-02-01', 1);


-- =============================================================================
-- CALIBRAÇÕES
-- =============================================================================
INSERT INTO `calibracoes_equipamento`
    (`id_calibracao`, `codigo_processo`, `id_equipamento`, `id_acessorio`,
     `id_fornecedor_responsavel`, `tipo_execucao`, `estado_processo`,
     `decisao_admin`, `id_admin_decisao`, `data_decisao`,
     `data_abertura`, `data_prevista`, `data_recolha`,
     `data_inicio_procedimento`, `data_fim_procedimento`,
     `data_emissao_relatorio`, `data_devolucao`, `data_finalizacao`,
     `data_calibracao`, `proxima_calibracao`,
     `numero_certificado`, `resultado`, `procedimento`, `custo`, `isActive`)
VALUES
(1, 'CAL-2024-001', 1, NULL, 2, 'externa', 'processo_finalizado',
   'aprovado', 1, '2024-02-05 10:00:00',
   '2024-02-01', '2024-02-28', '2024-02-08',
   '2024-02-10', '2024-02-15', '2024-02-20', '2024-02-22', '2024-02-23',
   '2024-02-15', '2025-02-15',
   'CERT-2024-001', 'aprovado',
   'Calibração completa efetuada segundo norma IEC 60601. Todos os parâmetros dentro dos limites.', 350.00, 1),

(2, 'CAL-2025-001', 3, 3, 2, 'externa', 'aguarda_decisao',
   'pendente', NULL, NULL,
   '2025-01-10', '2025-02-10', NULL,
   NULL, NULL, NULL, NULL, NULL,
   NULL, NULL, NULL, NULL, NULL, 350.00, 1),

(3, 'CAL-2025-002', 2, NULL, 2, 'externa', 'devolucao_equipamento',
   'aprovado', 1, '2025-03-05 09:00:00',
   '2025-03-01', '2025-03-31', '2025-03-08',
   '2025-03-10', '2025-03-18', '2025-03-22', '2025-03-25', NULL,
   '2025-03-18', '2026-03-18',
   'CERT-2025-002', 'aprovado',
   'Calibração efetuada com sucesso. Parâmetros de medição verificados e ajustados.', 280.00, 1);


-- =============================================================================
-- MANUTENÇÕES
-- =============================================================================
INSERT INTO `manutencoes_equipamento`
    (`id_manutencao`, `codigo_processo`, `id_equipamento`, `id_acessorio`,
     `tipo_manutencao`, `tipo_execucao`, `estado_processo`,
     `decisao_admin`, `id_admin_decisao`, `data_decisao`,
     `data_abertura`, `data_prevista`, `data_recolha`,
     `data_inicio_procedimento`, `data_fim_procedimento`,
     `data_emissao_relatorio`, `data_devolucao`, `data_finalizacao`,
     `id_fornecedor_responsavel`, `numero_relatorio`,
     `descricao_procedimento`, `resultado`, `custo`, `isActive`)
VALUES
(1, 'MAN-2024-001', 3, NULL, 'preventiva', 'externa', 'processo_finalizado',
   'aprovado', 1, '2024-04-05 14:00:00',
   '2024-04-01', '2024-04-30', '2024-04-07',
   '2024-04-10', '2024-04-20', '2024-04-25', '2024-04-27', '2024-04-28',
   3, 'REL-MAN-2024-001',
   'Manutenção preventiva anual realizada. Substituição de filtros, limpeza interna e verificação de todos os módulos.', 'aprovado', 520.00, 1),

(2, 'MAN-2025-001', 5, 5, 'corretiva', 'externa', 'aguarda_decisao',
   'pendente', NULL, NULL,
   '2025-04-10', '2025-05-10', NULL,
   NULL, NULL, NULL, NULL, NULL,
   3, NULL, NULL, NULL, 0.00, 1),

(3, 'MAN-2025-002', 6, NULL, 'preventiva', 'externa', 'devolucao_equipamento',
   'aprovado', 1, '2025-05-05 11:00:00',
   '2025-05-01', '2025-05-31', '2025-05-07',
   '2025-05-10', '2025-05-22', '2025-05-26', '2025-05-28', NULL,
   3, 'REL-MAN-2025-002',
   'Manutenção preventiva semestral. Revisão completa do sistema ventilatório, calibração de sensores de fluxo e pressão.', 'aprovado', 780.00, 1);


-- =============================================================================
-- ACESSÓRIOS EM CALIBRAÇÕES
-- =============================================================================
INSERT INTO `calibracoes_acessorios` (`id_calibracao`, `id_acessorio`, `isActive`)
VALUES
(1, 1, 1),
(2, 3, 1);


-- =============================================================================
-- CONSUMÍVEIS EM CALIBRAÇÕES
-- =============================================================================
INSERT INTO `calibracoes_consumiveis` (`id_calibracao`, `id_consumivel`, `quantidade_utilizada`, `isActive`)
VALUES
(1, 4, 1.00, 1),
(2, 4, 1.00, 1),
(3, 4, 1.00, 1);


-- =============================================================================
-- ACESSÓRIOS EM MANUTENÇÕES
-- =============================================================================
INSERT INTO `manutencoes_acessorios` (`id_manutencao`, `id_acessorio`, `isActive`)
VALUES
(2, 5, 1);


-- =============================================================================
-- AVARIAS REPORTADAS
-- =============================================================================
INSERT INTO `avarias_reportadas`
    (`id_avaria`, `codigo_avaria`, `id_equipamento`, `id_acessorio`,
     `id_utilizador_reportou`, `descricao_avaria`, `estado`, `isActive`)
VALUES
(1, 'AVA-2025-001', 2, 7, 2,
   'Cabo USB não reconhecido pelo sistema — impossível transferir registos ECG para o sistema informático.',
   'reportada', 1),

(2, 'AVA-2025-002', 5, 5, 3,
   'Eletrodos apresentam má adesão à pele do paciente. Sinal instável durante utilização.',
   'em_analise', 1);


-- =============================================================================
-- EMPRÉSTIMOS
-- =============================================================================
INSERT INTO `emprestimos_equipamentos`
    (`id_emprestimo`, `codigo_emprestimo`, `id_equipamento`,
     `id_localizacao_origem`, `id_localizacao_destino`,
     `id_utilizador_pedido`, `responsavel_emprestimo`,
     `motivo`, `data_inicio`, `data_prevista_devolucao`, `estado`, `isActive`)
VALUES
(1, 'EMP-2025-001', 8, 3, 5, 2, 'João Ferreira',
   'Necessidade temporária de monitorização de SpO2 em Pediatria',
   '2025-06-01', '2025-06-15', 'terminado', 1),

(2, 'EMP-2025-002', 4, 5, 1, 3, 'Maria Santos',
   'Monitor substituto para a Urgência durante manutenção do equipamento principal',
   '2025-06-10', '2025-06-20', 'ativo', 1);


-- =============================================================================
-- TRANSFERÊNCIAS
-- =============================================================================
INSERT INTO `transferencias_equipamentos`
    (`id_transferencia`, `codigo_transferencia`, `id_equipamento`,
     `id_localizacao_origem`, `id_localizacao_destino`,
     `id_utilizador_pedido`, `id_utilizador_aprovacao`,
     `motivo`, `estado`, `data_pedido`, `data_aprovacao`, `isActive`)
VALUES
(1, 'TRF-2025-001', 7, 6, 5, 3, 1,
   'Transferência para Pediatria — necessidade de bomba infusora adicional.',
   'aprovado', '2025-05-15 09:00:00', '2025-05-16 10:30:00', 1),

(2, 'TRF-2025-002', 1, 2, 3, 2, NULL,
   'Transferência do ECG para a Neurologia para exames de rotina.',
   'pendente', '2025-06-20 14:00:00', NULL, 1);


-- =============================================================================
-- PÁGINA PÚBLICA — CONFIGURAÇÃO
-- =============================================================================
INSERT INTO `pagina_publica_config`
    (`id_config`, `navbar_logo`, `navbar_link_sobre`, `navbar_link_equipa`,
     `navbar_link_funcional`, `navbar_link_hospitais`, `navbar_link_contacto`,
     `navbar_btn_restrita`, `sobre_titulo`, `sobre_texto`, `contacto_texto`,
     `rodape_localizacao`, `rodape_horario_semana`, `rodape_email`, `rodape_telefone`)
VALUES
(1,
 'assets/img/MEDICORE_logotipo_branco.png',
 'Sobre', 'Nossa Equipa', 'Funcionalidades', 'Hospitais e Clínicas', 'Contacto', 'Área Restrita',
 'Gestão Inteligente do Inventário Hospitalar',
 'O MEDICORE é uma plataforma web desenvolvida para hospitais e clínicas que pretendem gerir de forma eficiente o seu inventário de equipamentos médicos. Permite o controlo de equipamentos, acessórios, calibrações, manutenções, mobilidade e avarias — tudo num único sistema integrado.',
 'Para mais informações sobre o MEDICORE, entre em contacto através dos seguintes meios. A nossa equipa terá todo o gosto em responder às suas questões.',
 'Rua do Hospital, nº 1, 4000-001 Porto, Portugal',
 '2ª a 6ª Feira: 9h — 18h',
 'geral@medicore.pt',
 '+351 919 323 121');


-- =============================================================================
-- PÁGINA PÚBLICA — SLIDES
-- =============================================================================
INSERT INTO `pagina_publica_slides`
    (`id_slide`, `ordem`, `imagem`, `titulo`, `descricao`, `isActive`)
VALUES
(1, 1, 'assets/img/slide1.jpg',
   'Gestão Completa de Equipamentos',
   'Controle todos os equipamentos médicos da sua instituição numa única plataforma intuitiva e segura.', 1),
(2, 2, 'assets/img/slide2.jpg',
   'Calibrações e Manutenções',
   'Acompanhe o ciclo completo de calibrações e manutenções, desde a abertura do processo até ao encerramento.', 1),
(3, 3, 'assets/img/slide3.jpg',
   'Mobilidade de Equipamentos',
   'Gira empréstimos e transferências de equipamentos entre departamentos com facilidade.', 1);


-- =============================================================================
-- PÁGINA PÚBLICA — HOSPITAIS / CLÍNICAS
-- =============================================================================
INSERT INTO `pagina_publica_hospitais`
    (`id_hospital`, `ordem`, `nome`, `descricao`, `imagem`, `isActive`)
VALUES
(1, 1, 'Hospital de São João — Porto',
   'Um dos maiores hospitais públicos de Portugal, com mais de 1000 camas e equipamentos de última geração.',
   'assets/img/hospital1.jpg', 1),
(2, 2, 'Centro Hospitalar e Universitário do Porto',
   'Centro de excelência clínica e académica no norte de Portugal, referência em cuidados especializados.',
   'assets/img/hospital2.jpg', 1),
(3, 3, 'Hospital da Luz — Lisboa',
   'Hospital privado de referência com as mais avançadas tecnologias de diagnóstico e tratamento.',
   'assets/img/hospital3.jpg', 1),
(4, 4, 'Clínica CUF — Porto',
   'Clínica moderna com ampla rede de especialidades médicas e equipamentos de diagnóstico.',
   'assets/img/hospital4.jpg', 1);


-- =============================================================================
-- MOVIMENTOS DE STOCK (consumo em calibração)
-- =============================================================================
INSERT INTO `movimentos_stock_consumiveis`
    (`id_consumivel`, `tipo_movimento`, `quantidade`,
     `stock_anterior`, `stock_posterior`,
     `id_equipamento`, `id_calibracao`,
     `motivo`, `atualizado_por`)
VALUES
(4, 'consumo_calibracao', 1.00, 9.00, 8.00, 1, 1,
   'Consumo de reagente na calibração CAL-2024-001', 'admin');


SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- FIM DO SCRIPT DE DADOS DE TESTE
-- =============================================================================
