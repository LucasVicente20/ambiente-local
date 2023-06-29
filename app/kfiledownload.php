<?php
require_once '../vendor/autoload.php';
session_start();
$ArquivoNomeServidor = $_REQUEST['arq'];
$NomeArquivo = $_REQUEST['arq_nome'];
addArquivoAcesso($ArquivoNomeServidor);
if(empty($_SESSION['arquivo'])) {
    $_SESSION['arquivo'] = $ArquivoNomeServidor;
}
$ArquivoNomeServidor = str_replace('/', '%2F', $ArquivoNomeServidor);
$url = '../carregarArquivo.php?arq=' . $ArquivoNomeServidor . '&arq_nome=' . urlencode($NomeArquivo);
header("Location: $url ");
exit();