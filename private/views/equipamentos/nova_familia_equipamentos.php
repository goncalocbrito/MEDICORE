<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

require_once __DIR__ . '/../../../config/config.php';

$pdo = new PDO(
    'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$errosFamilia = [];

$codigoFamilia = '';
$nomeFamilia = '';
$descricaoFamilia = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoFamilia = trim($_POST['codigoFamilia'] ?? '');
    $nomeFamilia = trim($_POST['nomeFamilia'] ?? '');
    $descricaoFamilia = trim($_POST['descricaoFamilia'] ?? '');

    /*
       Normaliza o código:
       - se o utilizador escrever 4, fica 04
       - se escrever 04, mantém 04
    */
    if ($codigoFamilia !== '' && is_numeric($codigoFamilia)) {
        $codigoFamilia = str_pad((int)$codigoFamilia, 2, '0', STR_PAD_LEFT);
    }

    if ($codigoFamilia === '') {
        $errosFamilia[] = 'O campo "Código da Família" é obrigatório.';
    } elseif (!preg_match('/^[0-9]{2}$/', $codigoFamilia)) {
        $errosFamilia[] = 'O código da família deve ter 2 algarismos. Exemplo: 04.';
    }

    if ($nomeFamilia === '') {
        $errosFamilia[] = 'O campo "Nome da Família" é obrigatório.';
    }

    if (empty($errosFamilia)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO familias_equipamento (
                    codigo_familia,
                    nome,
                    descricao,
                    isActive
                ) VALUES (
                    :codigo_familia,
                    :nome,
                    :descricao,
                    1
                )
            ");

            $stmt->execute([
                ':codigo_familia' => $codigoFamilia,
                ':nome' => $nomeFamilia,
                ':descricao' => $descricaoFamilia !== '' ? $descricaoFamilia : null
            ]);

            header('Location: lista_familia_equipamentos.php?criado=1');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errosFamilia[] = 'Já existe uma família com esse código.';
            } else {
                $errosFamilia[] = 'Ocorreu um erro ao criar a família.';
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/nav.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="conteudo-private ficha-equipamento-page novo-equipamento-page">

    <div class="form-actions">
        <a href="lista_familia_equipamentos.php" class="btn btn-cancelar">
            <i class="fa-solid fa-xmark me-2"></i> Cancelar
        </a>

        <button type="reset"
                class="btn btn-limpar"
                form="formNovaFamiliaEquipamentos">
            <i class="fa-solid fa-eraser me-2"></i> Limpar
        </button>

        <button type="submit"
                class="btn btn-guardar"
                form="formNovaFamiliaEquipamentos">
            <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Família
        </button>
    </div>

    <form class="form-equipamento form-ficha-equipamento"
          id="formNovaFamiliaEquipamentos"
          action="nova_familia_equipamentos.php"
          method="post">

        <?php if (!empty($errosFamilia)): ?>
            <div class="form-alerta-erros" role="alert">
                <strong>
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Não foi possível criar a família.
                </strong>

                <ul>
                    <?php foreach ($errosFamilia as $erro): ?>
                        <li><?php echo htmlspecialchars($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="ficha-area">
            <div class="secao-ficha-titulo">
                <h4>Nova Família de Equipamentos</h4>
                <p>
                    Defina a família que será usada para gerar automaticamente o código dos equipamentos.
                    Por exemplo: <strong>04</strong> para monitores de sinais vitais.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <label for="codigoFamilia" class="form-label">Código da Família *</label>
                    <input type="text"
                           class="form-control"
                           id="codigoFamilia"
                           name="codigoFamilia"
                           maxlength="2"
                           value="<?php echo htmlspecialchars($codigoFamilia); ?>"
                           placeholder="Ex: 04"
                           required>
                </div>

                <div class="col-md-9">
                    <label for="nomeFamilia" class="form-label">Nome da Família *</label>
                    <input type="text"
                           class="form-control"
                           id="nomeFamilia"
                           name="nomeFamilia"
                           value="<?php echo htmlspecialchars($nomeFamilia); ?>"
                           placeholder="Ex: Monitores de sinais vitais"
                           required>
                </div>

                <div class="col-12">
                    <label for="descricaoFamilia" class="form-label">Descrição</label>
                    <textarea class="form-control"
                              id="descricaoFamilia"
                              name="descricaoFamilia"
                              rows="6"
                              placeholder="Ex: Família destinada a monitores multiparamétricos, monitores de sinais vitais e sistemas de monitorização clínica."><?php echo htmlspecialchars($descricaoFamilia); ?></textarea>
                </div>
            </div>
        </div>
    </form>

</main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>