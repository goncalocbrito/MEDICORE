================================================================================
 MEDICORE - Sistema de Gestão de Equipamentos Médicos
================================================================================

NOME DO PROJETO   : MEDICORE
NOME DO ESTUDANTE : Gonçalo Alexandre Camacho Brito
NÚMERO DE ALUNO   : 1230404
UNIDADE CURRICULAR: Sistemas de Informação e Bases de Dados Aplicados à Saúde
ANO LETIVO        : 2025/2026

================================================================================
 DESCRIÇÃO DA APLICAÇÃO
================================================================================

O MEDICORE é uma aplicação web desenvolvida para a gestão de equipamentos
médicos em ambiente hospitalar. O sistema permite registar e acompanhar o
ciclo de vida completo dos equipamentos, desde a sua aquisição até ao abate,
incluindo a gestão de acessórios, consumíveis, calibrações, manutenções,
mobilidade (empréstimos e transferências entre departamentos) e reporte de
avarias.

Funcionalidades principais:
  - Gestão de equipamentos, acessórios e consumíveis
  - Processos de calibração e manutenção com fluxo de aprovação
  - Mobilidade de equipamentos (empréstimos e transferências)
  - Reporte e acompanhamento de avarias
  - Gestão de fornecedores e localizações
  - Página pública configurável (backoffice)
  - Dois perfis de acesso: Administrador e Engenheiro

Tecnologias utilizadas:
  - PHP 8.1 (PDO + bcrypt), MySQL 8, Apache (Laragon)
  - Bootstrap 5, DataTables, Font Awesome

================================================================================
 ESTRUTURA DE DIRETORIAS
================================================================================

medicore/
├── config/               Configuração da ligação à base de dados
├── docs/                 Scripts SQL e documentação
│   ├── create_tables_medicore.sql        Script DDL (criação das tabelas)
│   ├── INSERTS.sql                       Dados de teste
│   └── modelo_relacional_medicore.dbml   Modelo relacional (DBML)
├── private/              Área restrita (requer autenticação)
│   ├── assets/           CSS, JS e imagens privadas
│   ├── includes/         Componentes reutilizáveis (nav, sidebar, footer)
│   └── views/            Páginas da aplicação por módulo
│       ├── avarias/
│       ├── backoffice/
│       ├── calibracao_manutencao/
│       ├── dashboard/
│       ├── equipamentos/
│       ├── fornecedores/
│       ├── localizacoes/
│       ├── mobilidade/
│       └── utilizadores/
├── public/               Área pública
│   ├── assets/           CSS, JS e imagens públicas
│   └── login.php         Página de autenticação
├── index.php             Redireciona para a página pública
└── README.txt            Este ficheiro

================================================================================
 INSTRUÇÕES DE INSTALAÇÃO E EXECUÇÃO
================================================================================

PRÉ-REQUISITOS:
  - Laragon (com Apache e PHP 8.x)
  - Acesso à base de dados remota do ISEP (VPN ativa se necessário)

PASSOS:
  1. Colocar a pasta do projeto em:
       C:\laragon\www\sibdas\1230404\medicore\

  2. Garantir que o Laragon está em execução (Apache ativo).

  3. Aceder à base de dados e executar os seguintes ficheiros SQL
     (disponíveis na pasta /docs), por esta ordem:
       a) create_tables_medicore.sql  → cria todas as tabelas
       b) INSERTS.sql                 → popula a base de dados com dados de teste

  4. Configurações da base de dados (ficheiro config/config.php):
       Host     : vsgate-s1.dei.isep.ipp.pt
       Porta    : 10464
       Base de dados : db1230404
       Utilizador    : 1230404
       Password      : brito_404

  5. Abrir o browser e aceder a:
       http://127.0.0.1/sibdas/1230404/medicore/

================================================================================
 CREDENCIAIS DE ACESSO
================================================================================

  PERFIL ADMINISTRADOR
  --------------------
  Utilizador : admin
  Password   : admin123
  Acesso a   : Gestão completa do sistema — utilizadores, equipamentos,
               fornecedores, localizações, aprovação de processos de
               calibração/manutenção, backoffice da página pública.

  PERFIL ENGENHEIRO
  -----------------
  Utilizador : jferreira
  Password   : engenheiro123
  Acesso a   : Dashboard, equipamentos, calibrações/manutenções,
               mobilidade (empréstimos e transferências), reportar avarias,
               fornecedores.

================================================================================
 INSTRUÇÕES PARA REALIZAÇÃO DOS PRINCIPAIS TESTES
================================================================================

1. PÁGINA PÚBLICA
   - Aceder a: http://127.0.0.1/sibdas/1230404/medicore/
   - Verificar apresentação da página pública (navbar, slides, hospitais, contactos).
   - Clicar em "Área Restrita" para aceder ao login.

2. LOGIN
   - Utilizar os botões de acesso rápido "Administrador" ou "Engenheiro"
     para preencher automaticamente as credenciais.

3. FLUXO DE CALIBRAÇÃO / MANUTENÇÃO (teste completo)
   - Entrar como Engenheiro.
   - Ir a Calibrações/Manutenções → Novo Processo.
   - Selecionar equipamento, tipo de processo, fornecedor e acessórios.
   - Avançar as etapas: Aguarda recolha → Procedimento a decorrer
     → Emissão do relatório → Devolução do equipamento.
   - Preencher os Dados Finais (resultado, descrição, data).
   - Entrar como Administrador.
   - Ir a Calibrações/Manutenções → Aprovação de Processos.
   - Na secção "Processos para Encerrar", abrir o processo e clicar
     em "Encerrar Processo".

4. REPORTAR AVARIA
   - Entrar como Engenheiro.
   - Ir a Reportar Avaria → Nova Avaria.
   - Selecionar equipamento e acessórios avariados (seleção múltipla).

5. MOBILIDADE
   - Entrar como Engenheiro.
   - Testar Empréstimo e Transferência de equipamentos entre localizações.

6. BACKOFFICE DA PÁGINA PÚBLICA
   - Entrar como Administrador.
   - Ir a Backoffice → editar textos, imagens e cartões da página pública.
   - Clicar em "Pré-visualizar" para verificar as alterações.

7. GESTÃO DE FORNECEDORES
   - Criar, consultar e eliminar fornecedores.
   - Verificar que o NIF é único (tentativa de duplicado gera erro).

================================================================================
 INFORMAÇÃO ADICIONAL
================================================================================

  - O projeto utiliza encriptação AES-256-CBC para ocultar IDs nas URLs.
  - As passwords são armazenadas com hashing bcrypt (password_hash do PHP).
  - O sistema implementa soft-delete (campo isActive) em todas as entidades
    principais — os registos nunca são eliminados fisicamente da base de dados.
  - A pasta /docs contém os scripts SQL e o guia de submissão.
  - Ferramentas de IA utilizadas no desenvolvimento: Claude Code (Anthropic)
    e ChatGPT (OpenAI), para replicação de código, verificação de erros,
    sugestão de estilos e apoio no desenvolvimento geral.

================================================================================
