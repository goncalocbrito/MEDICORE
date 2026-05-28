-- =========================================================
-- MEDICORE - Inserts iniciais
-- Base de dados: db1230404
-- Executar depois de criar as tabelas.
-- =========================================================

USE db1230404;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- DADOS BASE
-- =========================================================

INSERT INTO utilizador_tipos (nome, descricao) VALUES
('Administrador', 'Acesso total ao sistema e ao backoffice.'),
('Engenheiro', 'Gestao tecnica de equipamentos, fornecedores, localizacoes e manutencoes.'),
('Enfermeiro', 'Consulta operacional e alteracao limitada da localizacao dos equipamentos.');

INSERT INTO menus_sistema (codigo, nome, icone, url) VALUES
('dashboard', 'Dashboard Tecnico', 'fa-chart-line', 'private/index.html'),
('equipamentos', 'Equipamentos', 'fa-stethoscope', 'private/views/equipamentos/lista_equipamentos.html'),
('acessorios', 'Acessorios', 'fa-plug-circle-bolt', 'private/views/equipamentos/acessorios.html'),
('consumiveis', 'Consumiveis', 'fa-boxes-stacked', 'private/views/equipamentos/consumiveis.html'),
('calibracoes_manutencoes', 'Calibracoes/Manutencoes', 'fa-screwdriver-wrench', 'private/views/calibracao_manutencao/calibracao_manutencao.html'),
('processos_finalizados', 'Processos Finalizados', 'fa-circle-check', 'private/views/calibracao_manutencao/processos_finalizados.html'),
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
-- UTILIZADORES
-- A password_hash deve ser substituida por hash real no PHP.
-- =========================================================

INSERT INTO utilizadores (
  codigo, nome, cartao_cidadao, email, telefone, servico, id_tipo_utilizador,
  estado, username, password_hash, data_ativacao, observacoes
) VALUES
('USR-001', 'Ana Martins', '12345678', 'ana.martins@medicore.pt', '+351 220 000 100', 'Administracao',
 (SELECT id_tipo_utilizador FROM utilizador_tipos WHERE nome = 'Administrador'),
 'Ativo', 'ana.martins', 'trocar_no_backend', '2026-01-01',
 'Utilizadora com permissoes administrativas completas.'),
('USR-002', 'Goncalo Brito', '87654321', 'g.brito@medicore.pt', '+351 220 000 200', 'Engenharia Biomedica',
 (SELECT id_tipo_utilizador FROM utilizador_tipos WHERE nome = 'Engenheiro'),
 'Ativo', 'g.brito', 'trocar_no_backend', '2026-01-01',
 'Engenheiro responsavel pela area tecnica.'),
('USR-003', 'Ines Ferreira', '11223344', 'ines.ferreira@medicore.pt', '+351 220 000 300', 'UCI',
 (SELECT id_tipo_utilizador FROM utilizador_tipos WHERE nome = 'Enfermeiro'),
 'Ativo', 'ines.ferreira', 'trocar_no_backend', '2026-01-01',
 'Enfermeira com acesso operacional aos equipamentos.');

INSERT INTO utilizador_permissoes (id_utilizador, id_menu, pode_aceder)
SELECT u.id_utilizador, m.id_menu, 1
FROM utilizadores u
CROSS JOIN menus_sistema m
WHERE u.codigo IN ('USR-001', 'USR-002');

INSERT INTO utilizador_permissoes (id_utilizador, id_menu, pode_aceder)
SELECT u.id_utilizador, m.id_menu, 1
FROM utilizadores u
JOIN menus_sistema m ON m.codigo IN ('dashboard', 'equipamentos', 'acessorios', 'consumiveis', 'localizacoes')
WHERE u.codigo = 'USR-003';

-- =========================================================
-- LOCALIZACOES
-- =========================================================

INSERT INTO localizacoes (
  codigo, departamento, edificio, piso, sala, tipo_espaco,
  responsavel, estado, capacidade_equipamentos, equipamentos_previstos,
  permite_equipamentos_criticos, observacoes
) VALUES
('LOC-001', 'Unidade de Cuidados Intensivos', 'Edificio A', '2', 'Sala 201', 'UCI',
 'Enf. Maria Costa', 'Ativa', 10, 8, 1,
 'Area critica com equipamentos de suporte de vida e monitorizacao continua.'),
('LOC-002', 'Urgencia', 'Edificio B', '0', 'Sala 1', 'Urgencia',
 'Dr. Joao Martins', 'Ativa', 12, 10, 1,
 'Sala de atendimento urgente com equipamentos de suporte clinico.'),
('LOC-003', 'Bloco Operatorio', 'Edificio C', '1', 'BO-02', 'Bloco Operatorio',
 'Enf. Ricardo Silva', 'Ativa', 8, 6, 1,
 'Espaco cirurgico com equipamentos criticos.'),
('LOC-004', 'Pediatria', 'Edificio A', '3', 'Sala 3', 'Enfermaria',
 'Enf. Carla Sousa', 'Ativa', 7, 5, 0,
 'Sala de pediatria com equipamentos de monitorizacao.'),
('LOC-005', 'Armazem Tecnico', 'Edificio D', '-1', 'ARM-01', 'Armazem',
 'Eng. Goncalo Brito', 'Ativa', 20, 12, 0,
 'Espaco para equipamentos de reserva, acessorios e materiais tecnicos.');

-- =========================================================
-- FORNECEDORES
-- =========================================================

INSERT INTO fornecedores (
  codigo, nome_empresa, nif, email, telefone, website, pessoa_contacto,
  telefone_contacto, email_contacto, cargo_contacto, morada, codigo_postal,
  localidade, pais, estado, contrato, observacoes
) VALUES
('FOR-001', 'Philips Medical Systems', '509123456', 'suporte@philips-med.pt', '+351 220 000 111',
 'https://www.philips.pt/healthcare', 'Joao Pereira', '+351 910 000 111', 'joao.pereira@philips-med.pt',
 'Gestor de Conta', 'Rua da Saude, 100', '4000-001', 'Porto', 'Portugal', 'Ativo', 'Sim',
 'Fabricante e suporte tecnico de equipamentos de monitorizacao.'),
('FOR-002', 'MedSupply Portugal', '514987321', 'comercial@medsupply.pt', '+351 221 234 567',
 'https://www.medsupply.pt', 'Marta Santos', '+351 910 000 222', 'marta.santos@medsupply.pt',
 'Comercial', 'Avenida Central, 50', '1000-001', 'Lisboa', 'Portugal', 'Ativo', 'Sim',
 'Distribuidor comercial e apoio a manutencao preventiva.'),
('FOR-003', 'Biomedical Solutions', '516111222', 'assistencia@biomedical.pt', '+351 239 000 222',
 'https://www.biomedical.pt', 'Rui Almeida', '+351 910 000 333', 'rui.almeida@biomedical.pt',
 'Tecnico Responsavel', 'Rua Tecnica, 20', '3000-001', 'Coimbra', 'Portugal', 'Ativo', 'Sim',
 'Empresa de assistencia tecnica hospitalar.'),
('FOR-004', 'CalibraMed', '517333444', 'geral@calibramed.pt', '+351 234 000 444',
 'https://www.calibramed.pt', 'Sofia Lima', '+351 910 000 444', 'sofia.lima@calibramed.pt',
 'Tecnica de Calibracao', 'Rua do Laboratorio, 12', '3800-001', 'Aveiro', 'Portugal', 'Ativo', 'Sim',
 'Fornecedor especializado em calibracao e certificados tecnicos.');

INSERT INTO fornecedor_tipo_assoc (id_fornecedor, id_tipo_fornecedor)
SELECT f.id_fornecedor, t.id_tipo_fornecedor
FROM fornecedores f
JOIN fornecedor_tipos t ON
  (f.codigo = 'FOR-001' AND t.nome = 'Fabricante') OR
  (f.codigo = 'FOR-002' AND t.nome = 'Distribuidor') OR
  (f.codigo = 'FOR-003' AND t.nome = 'Assistencia tecnica') OR
  (f.codigo = 'FOR-004' AND t.nome = 'Calibracao');

-- =========================================================
-- EQUIPAMENTOS
-- =========================================================

INSERT INTO equipamentos (
  codigo, designacao, id_categoria_equipamento, fabricante, modelo, numero_serie,
  ano_fabrico, data_aquisicao, custo_aquisicao, id_tipo_entrada,
  id_estado_equipamento, id_criticidade, id_localizacao, operacional, observacoes
) VALUES
('EQ-001', 'Monitor Multiparametrico',
 (SELECT id_categoria_equipamento FROM categorias_equipamento WHERE nome = 'Monitorizacao'),
 'Philips', 'IntelliVue MX450', 'SN-MX450-2024', 2023, '2024-01-15', 3500.00,
 (SELECT id_tipo_entrada FROM tipos_entrada WHERE nome = 'Compra'),
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Ativo'),
 (SELECT id_criticidade FROM criticidades WHERE nome = 'Alta'),
 (SELECT id_localizacao FROM localizacoes WHERE codigo = 'LOC-001'), 1,
 'Equipamento essencial para monitorizacao continua de parametros vitais.'),
('EQ-002', 'Ventilador Pulmonar',
 (SELECT id_categoria_equipamento FROM categorias_equipamento WHERE nome = 'Suporte de Vida'),
 'Drager', 'Evita V300', 'SN-EV300-1198', 2022, '2023-03-10', 18000.00,
 (SELECT id_tipo_entrada FROM tipos_entrada WHERE nome = 'Compra'),
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Em manutencao'),
 (SELECT id_criticidade FROM criticidades WHERE nome = 'Suporte de vida'),
 (SELECT id_localizacao FROM localizacoes WHERE codigo = 'LOC-002'), 0,
 'Equipamento em intervencao tecnica por falha no sistema de ventilacao.'),
('EQ-003', 'Desfibrilhador',
 (SELECT id_categoria_equipamento FROM categorias_equipamento WHERE nome = 'Emergencia'),
 'Zoll', 'R Series', 'SN-ZOLL-8821', 2021, '2022-06-20', 9200.00,
 (SELECT id_tipo_entrada FROM tipos_entrada WHERE nome = 'Compra'),
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Avariado'),
 (SELECT id_criticidade FROM criticidades WHERE nome = 'Alta'),
 (SELECT id_localizacao FROM localizacoes WHERE codigo = 'LOC-003'), 0,
 'Equipamento sinalizado como avariado ate avaliacao tecnica.'),
('EQ-004', 'Monitor de Sinais Vitais',
 (SELECT id_categoria_equipamento FROM categorias_equipamento WHERE nome = 'Monitorizacao'),
 'Philips', 'SureSigns VS4', 'SN-VS4-3310', 2024, '2025-01-12', 2100.00,
 (SELECT id_tipo_entrada FROM tipos_entrada WHERE nome = 'Compra'),
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Ativo'),
 (SELECT id_criticidade FROM criticidades WHERE nome = 'Media'),
 (SELECT id_localizacao FROM localizacoes WHERE codigo = 'LOC-004'), 1,
 'Monitor utilizado em pediatria.'),
('EQ-005', 'Bomba de Infusao',
 (SELECT id_categoria_equipamento FROM categorias_equipamento WHERE nome = 'Terapia'),
 'B. Braun', 'Infusomat Space', 'SN-INF-5201', 2022, '2023-09-05', 2800.00,
 (SELECT id_tipo_entrada FROM tipos_entrada WHERE nome = 'Compra'),
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Ativo'),
 (SELECT id_criticidade FROM criticidades WHERE nome = 'Alta'),
 (SELECT id_localizacao FROM localizacoes WHERE codigo = 'LOC-005'), 1,
 'Equipamento de reserva no armazem tecnico.');

-- =========================================================
-- ACESSORIOS
-- =========================================================

INSERT INTO acessorios (
  id_equipamento, codigo, nome, tipo, numero_serie, id_estado_equipamento,
  requer_verificacao_metrologica, proxima_intervencao, observacoes
) VALUES
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'), 'ACC-001', 'Cabo ECG 5 derivacoes', 'Cabo', 'ECG-5D-2024',
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Ativo'), 0, NULL,
 'Cabo ECG reutilizavel associado ao monitor multiparametrico.'),
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'), 'ACC-002', 'Sensor SpO2', 'Sensor', 'SPO2-4482',
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Ativo'), 1, '2026-09-12',
 'Sensor com verificacao metrologica periodica.'),
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'), 'ACC-003', 'Bracadeira NIBP adulto', 'Consumivel reutilizavel', 'NIBP-1120',
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Ativo'), 1, '2026-09-12',
 'Bracadeira associada ao monitor multiparametrico.'),
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-002'), 'ACC-004', 'Circuito respiratorio reutilizavel', 'Modulo', 'CIR-2201',
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Ativo'), 1, '2026-08-28',
 'Circuito respiratorio associado ao ventilador.'),
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-003'), 'ACC-005', 'Pas adulto', 'Modulo', 'PAS-8821',
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Avariado'), 1, NULL,
 'Pas associadas ao desfibrilhador.');

-- =========================================================
-- RELACAO EQUIPAMENTO-FORNECEDOR
-- =========================================================

INSERT INTO equipamento_fornecedor (id_equipamento, id_fornecedor, id_tipo_fornecedor, data_inicio, observacoes)
VALUES
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-001'),
 (SELECT id_tipo_fornecedor FROM fornecedor_tipos WHERE nome = 'Fabricante'),
 '2024-01-15', 'Fabricante do equipamento.'),
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-002'),
 (SELECT id_tipo_fornecedor FROM fornecedor_tipos WHERE nome = 'Distribuidor'),
 '2024-01-15', 'Fornecedor comercial.'),
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-002'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-003'),
 (SELECT id_tipo_fornecedor FROM fornecedor_tipos WHERE nome = 'Assistencia tecnica'),
 '2023-03-10', 'Assistencia tecnica contratada.'),
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-004'),
 (SELECT id_tipo_fornecedor FROM fornecedor_tipos WHERE nome = 'Calibracao'),
 '2026-01-01', 'Fornecedor de calibracao.');

-- =========================================================
-- DOCUMENTOS, GARANTIAS E CONTRATOS
-- =========================================================

INSERT INTO documentos (
  id_tipo_documento, nome, data_documento, data_validade, id_equipamento,
  ficheiro_nome, caminho_ficheiro, id_utilizador_criador
) VALUES
((SELECT id_tipo_documento FROM documento_tipos WHERE nome = 'Manual de utilizador'),
 'Manual Monitor IntelliVue MX450', '2024-01-15', NULL,
 (SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'),
 'manual_intellivue_mx450.pdf', 'uploads/documentos/manual_intellivue_mx450.pdf',
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-002')),
((SELECT id_tipo_documento FROM documento_tipos WHERE nome = 'Certificado de calibracao'),
 'Certificado de calibracao EQ-001', '2026-03-12', '2027-03-12',
 (SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'),
 'certificado_eq001_2026.pdf', 'uploads/documentos/certificado_eq001_2026.pdf',
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-002')),
((SELECT id_tipo_documento FROM documento_tipos WHERE nome = 'Relatorio tecnico'),
 'Relatorio tecnico ventilador EQ-002', '2026-05-24', NULL,
 (SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-002'),
 'relatorio_eq002_2026.pdf', 'uploads/documentos/relatorio_eq002_2026.pdf',
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-002'));

INSERT INTO garantias (
  id_equipamento, id_fornecedor, data_inicio, data_fim, entidade_responsavel, observacoes
) VALUES
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-002'),
 '2024-01-20', '2027-01-20', 'MedSupply Portugal', 'Garantia comercial de 3 anos.'),
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-004'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-001'),
 '2025-01-12', '2028-01-12', 'Philips Medical Systems', 'Garantia do fabricante.');

INSERT INTO contratos_manutencao (
  id_equipamento, id_fornecedor, id_documento, numero_contrato, tipo_contrato,
  periodicidade, data_inicio, data_fim, estado, observacoes
) VALUES
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-002'),
 NULL, 'CTR-2024-001', 'Manutencao preventiva anual', 'Semestral',
 '2024-01-20', '2027-01-20', 'Ativo', 'Contrato associado ao monitor multiparametrico.'),
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-002'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-003'),
 NULL, 'CTR-2023-014', 'Manutencao preventiva e corretiva', 'Semestral',
 '2023-03-10', '2026-03-10', 'Ativo', 'Contrato associado ao ventilador pulmonar.');

-- =========================================================
-- PEDIDOS DE CALIBRACAO/MANUTENCAO
-- =========================================================

INSERT INTO pedidos_calibracao_manutencao (
  codigo, id_equipamento, id_acessorio, id_tipo_procedimento, id_fornecedor,
  id_tecnico_responsavel, data_pedido, data_prevista, data_conclusao,
  id_estado_procedimento, observacoes
) VALUES
('PCM-001',
 (SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'),
 NULL,
 (SELECT id_tipo_procedimento FROM procedimento_tipos WHERE nome = 'Manutencao preventiva'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-002'),
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-002'),
 '2026-05-24', '2026-09-12', NULL,
 (SELECT id_estado_procedimento FROM procedimento_estados WHERE nome = 'Aguarda fornecedor'),
 'Pedido enviado para confirmacao de agenda.'),
('PCM-002',
 (SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-002'),
 NULL,
 (SELECT id_tipo_procedimento FROM procedimento_tipos WHERE nome = 'Manutencao corretiva'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-003'),
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-002'),
 '2026-05-24', '2026-05-28', NULL,
 (SELECT id_estado_procedimento FROM procedimento_estados WHERE nome = 'Em manutencao'),
 'Intervencao tecnica por falha no sistema de ventilacao.'),
('PCM-003',
 (SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-001'),
 (SELECT id_acessorio FROM acessorios WHERE codigo = 'ACC-002'),
 (SELECT id_tipo_procedimento FROM procedimento_tipos WHERE nome = 'Calibracao'),
 (SELECT id_fornecedor FROM fornecedores WHERE codigo = 'FOR-004'),
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-002'),
 '2026-05-24', '2026-09-12', NULL,
 (SELECT id_estado_procedimento FROM procedimento_estados WHERE nome = 'Em calibracao'),
 'Pedido de verificacao metrologica do sensor SpO2.');

INSERT INTO historico_estados_equipamento (
  id_equipamento, id_estado_anterior, id_estado_novo, id_utilizador, data_alteracao, observacoes
) VALUES
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-002'),
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Ativo'),
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Em manutencao'),
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-002'),
 '2026-05-24 10:30:00', 'Alteracao de estado apos abertura de pedido corretivo.'),
((SELECT id_equipamento FROM equipamentos WHERE codigo = 'EQ-003'),
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Ativo'),
 (SELECT id_estado_equipamento FROM estados_equipamento WHERE nome = 'Avariado'),
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-002'),
 '2026-05-24 11:10:00', 'Equipamento sinalizado como indisponivel.');

-- =========================================================
-- BACKOFFICE PUBLICO
-- =========================================================

INSERT INTO conteudos_publicos (
  chave, secao, titulo, texto, imagem, url, ordem, ativo, id_utilizador_atualizacao
) VALUES
('hero_titulo', 'sobre', 'Gestao Inteligente do Inventario Hospitalar',
 'O MEDICORE e uma aplicacao web para registo, organizacao e acompanhamento de equipamentos medicos em contexto hospitalar.',
 'assets/img/MEDICORE_Official_Logo.png', NULL, 1, 1,
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-001')),
('contacto_email', 'contacto', 'Email', 'geral@medicore.pt',
 NULL, 'mailto:geral@medicore.pt', 1, 1,
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-001')),
('contacto_telefone', 'contacto', 'Telefone', '+351 9xx xxx xxx',
 NULL, NULL, 2, 1,
 (SELECT id_utilizador FROM utilizadores WHERE codigo = 'USR-001'));

SET FOREIGN_KEY_CHECKS = 1;

