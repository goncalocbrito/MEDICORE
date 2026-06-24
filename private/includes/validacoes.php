<?php
/* =========================================================
   FUNÇÕES DE VALIDAÇÃO REUTILIZÁVEIS
   Centraliza validações genéricas usadas em múltiplos
   formulários do projeto.
   ========================================================= */


/* ---- Identificação ---- */

function validar_nif(string $nif): ?string
{
    $nif = trim($nif);
    if ($nif === '') return null;
    if (!preg_match('/^\d{9}$/', $nif)) {
        return 'O NIF deve ter exatamente 9 dígitos numéricos.';
    }
    return null;
}

function validar_cartao_cidadao(string $cc): ?string
{
    $cc = trim($cc);
    if ($cc === '') return null;
    if (!preg_match('/^\d{8}$/', $cc)) {
        return 'O N.º Cartão de Cidadão deve ter exatamente 8 algarismos.';
    }
    return null;
}


/* ---- Contactos ---- */

function validar_telefone(string $tel): ?string
{
    $tel = preg_replace('/\D/', '', $tel);
    if ($tel === '') return null;
    if (strlen($tel) !== 9) {
        return 'O telefone deve ter exatamente 9 dígitos.';
    }
    return null;
}

function validar_email(string $email, bool $rigoroso = false): ?string
{
    $email = trim($email);
    if ($email === '') return null;

    if ($rigoroso) {
        if (!preg_match('/^[^@\s]+@[^@\s]+\.(com|pt)$/i', $email)) {
            return 'O email deve conter @ e terminar em .com ou .pt.';
        }
    } else {
        if (!str_contains($email, '@')) {
            return 'O email deve conter o carácter @.';
        }
    }
    return null;
}

function validar_codigo_postal(string $cp): ?string
{
    $cp = trim($cp);
    if ($cp === '') return null;
    if (!preg_match('/^\d{4}-\d{3}$/', $cp)) {
        return 'O código postal deve ter o formato NNNN-NNN (ex: 1234-567).';
    }
    return null;
}


/* ---- Texto ---- */

function validar_apenas_letras(string $valor, string $nomeCampo): ?string
{
    $valor = trim($valor);
    if ($valor === '') return null;
    if (!preg_match('/^[\p{L}\s]+$/u', $valor)) {
        return 'O campo "' . $nomeCampo . '" só pode conter letras.';
    }
    return null;
}

function validar_sigla(string $sigla): ?string
{
    $sigla = trim($sigla);
    if ($sigla === '') return null;
    if (!preg_match('/^[A-Za-z]{1,3}$/', $sigla)) {
        return 'A sigla deve ter no máximo 3 letras (sem números ou símbolos).';
    }
    return null;
}

function validar_apenas_digitos(string $valor, string $nomeCampo, int $minDigitos = 1, int $maxDigitos = 3): ?string
{
    $valor = trim($valor);
    if ($valor === '') return null;
    $padrao = '/^\d{' . $minDigitos . ',' . $maxDigitos . '}$/';
    if (!preg_match($padrao, $valor)) {
        if ($minDigitos === $maxDigitos) {
            return '"' . $nomeCampo . '" deve ter exatamente ' . $minDigitos . ' dígito(s).';
        }
        return '"' . $nomeCampo . '" deve ser um número entre ' . $minDigitos . ' e ' . $maxDigitos . ' dígitos.';
    }
    return null;
}


/* ---- Valores ---- */

function validar_em_lista(string $valor, array $listaPermitida, string $nomeCampo): ?string
{
    if (!in_array($valor, $listaPermitida, true)) {
        return 'O campo "' . $nomeCampo . '" contém um valor não permitido.';
    }
    return null;
}

function validar_valor_positivo(string $valor, string $nomeCampo): ?string
{
    $valor = trim($valor);
    if ($valor === '') return null;
    if (!is_numeric($valor) || (float) $valor < 0) {
        return 'O campo "' . $nomeCampo . '" deve ser um número positivo.';
    }
    return null;
}


/* ---- Datas ---- */

function validar_ordem_datas(string $dataA, string $dataB, string $mensagem): ?string
{
    if ($dataA !== '' && $dataB !== '' && $dataB < $dataA) {
        return $mensagem;
    }
    return null;
}


/* ---- Ficheiros ---- */

function validar_extensao_ficheiro(string $extensao, array $permitidas = ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx']): ?string
{
    if (!in_array(strtolower($extensao), $permitidas, true)) {
        return 'Formato de ficheiro não permitido. Use: ' . implode(', ', array_map('strtoupper', $permitidas)) . '.';
    }
    return null;
}


/* ---- Localização / Mobilidade ---- */

function validar_localizacoes_diferentes(int $origem, int $destino, string $mensagem = 'A localização de destino não pode ser igual à localização atual.'): ?string
{
    if ($origem === $destino) {
        return $mensagem;
    }
    return null;
}
