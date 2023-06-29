<?php

// -------------------------------------------------------------------------
// Portal de Compras
// Programa: carregarArquivo.php
// Objetivo: Carregar programas postados pelo usuário, no diretório de uploads
// Os programas devem ser inclusos previamente na Sessão, pela função addArquivoAcesso($ArqUpload).
// Usar resetArquivoAcesso() para apagar esta lista na sessão.
// Variáveis HTTP GET:
// arq- Nome do arquivo no servidor, inclundo seu caminho dentro do diretório uploads (Ex.: materiais/ESPECIFICACAO_234_38_1)
// arq_nome- Nome visto pelo usuário. Opcional, só é necessário caso o nome no servidor não é o nome real do arquivo.
// Autor: Ariston Cordeiro
// ----------------
// Alterado: Pitang Agile TI
// Data: 01/07/2015
// Objetivo: CR87378
// versão: v1.21.0-19-g490a5c2
// -------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 16/07/2015
// Objetivo: Bug CR 100458 Mensagem de erro recorrente
// versão: 1.27.0-2-g133f78b

/**
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data: 08/04/2019
 * Objetivo: 214320
 */
require_once 'vendor/autoload.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $arquivo = urldecode($_GET['arq']); // nome do arquivo no servidor, incluindo diretório desde a raiz de 'uploads/'
    $arquivo_nome = urldecode($_GET['arq_nome']); // nome do arquivo no link, sem diretório (opcional), caso o nome do arquivo no link seja diferente do nome do arquivo no servidor
}

assercao($arquivo, "Parâmetro 'arquivo' não foi informado");
// assercao($diretorio, "Parâmetro 'diretorio' não foi informado");

$caminho_arquivo = $GLOBALS["CAMINHO_UPLOADS"] . $arquivo;

if (is_null($arquivo_nome) or $arquivo_nome == "") {
    // separando arquivo de diretório
    $tmp = explode('/', $arquivo);
    $arquivo_nome = $tmp[count($tmp) - 1];
}
$arquivoExiste = file_exists($caminho_arquivo);
$cr100458 = new LoggerPortalCompras($caminho_arquivo);
assercao($arquivoExiste, 'Arquivo informado não existe <br /><br />' . $cr100458->getMensagem());
if (is_null($_SESSION['arquivo']) || ! (@in_array($arquivo, $_SESSION['arquivo'])) && !$arquivoExiste) { // Remover !$arquivoExiste caso não baixar aquivo
    echo "Você não possui permissão para acessar este arquivo, ou o link do arquivo expirou na sessão. Favor acessar o arquivo pela página do Portal de Compras pelos links apropriados.";
    exit();
}
$filesize = filesize($caminho_arquivo);

switch (strtolower(substr(strrchr(basename($arquivo_nome), "."), 1))) { // verifica a extensão do arquivo para pegar o tipo
    case "pdf":
        $tipo = "application/pdf";
        break;
    case "exe":
        $tipo = "application/octet-stream";
        break;
    case "zip":
        $tipo = "application/zip";
        break;
    case "doc":
        $tipo = "application/msword";
        break;
    case "docx":
        $tipo = "application/msword";
        break;
    case "xls":
        $tipo = "application/vnd.ms-excel";
        break;
    case "ppt":
        $tipo = "application/vnd.ms-powerpoint";
        break;
    case "gif":
        $tipo = "image/gif";
        break;
    case "png":
        $tipo = "image/png";
        break;
    case "jpg":
        $tipo = "image/jpg";
        break;
    case "mp3":
        $tipo = "audio/mpeg";
        break;
    case "php": // deixar vazio por seurança
    case "htm": // deixar vazio por seurança
    case "html": // deixar vazio por seurança
}
header("Content-Type: " . $tipo); // informa o tipo do arquivo ao navegador
                                  // header("Content-Length: ".filesize($arquivo)); // informa o tamanho do arquivo ao navegador

header("Content-Length: " . $filesize);
header("Content-Disposition: attachment; filename=" . $arquivo_nome);
readfile($caminho_arquivo);
exit();
