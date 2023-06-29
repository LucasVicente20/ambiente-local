<?php
require_once "../funcoes.php";
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $arquivo = $_GET['doc'];
    $path = $GLOBALS["CAMINHO_UPLOADS"]."publicacaoPCA/";
    $filePath = $path.$arquivo;
    
    if (file_exists($filePath)) {
        header("Content-Type: application/pdf");
        readfile($filePath);
        exit;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "Erro 404: Arquivo não encontrado.";
        exit;
    }
}else {
    header("HTTP/1.0 404 Not Found");
    echo "Erro 404: Arquivo não encontrado.";
    exit;
}

?>