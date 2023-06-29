<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Autor:    Lucas Baracho
 * Data:     16/02/2023
 * Objetivo: Tarefa Redmine 279282
 * -----------------------------------------------------------------------------------------
 */
if (! @require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao("templates/ConsDocumento2.html");

$db = Conexao();
$sql  = "SELECT CDOCPOCODI, EDOCPOARQU, EDOCPOTITU, EDOCPODESC, ";
$sql .= "       CDOCPOUSAL, CDOCPOGRAL, TDOCPOULAT, EDOCPOARQS ";
$sql .= "FROM   SFPC.TBDOCUMENTACAOPORTAL ";
$sql .= "WHERE  FDOCPOTIPO LIKE 'L' ";
$sql .= "       AND FDOCPONLEG LIKE 'S' ";
$sql .= "ORDER BY EDOCPOTITU, EDOCPODESC ";

$resDocs = $db->query($sql);

if ($resDocs->numRows() > 0) {
    resetArquivoAcesso();
    
    while ($linha = $resDocs->fetchRow(DB_FETCHMODE_OBJECT)) {
        $doccod     = $linha->cdocpocodi;
        $docarq     = $linha->edocpoarqu;
        $doctit     = $linha->edocpotitu;
        $docdesc    = $linha->edocpodesc;
        $docusr     = $linha->cdocpousal;
        $docgrp     = $linha->cdocpogral;
        $docdata    = $linha->tdocpoulat;
        $docarqserv = $linha->edocpoarqs;

        $arquivo = 'institucional/' . $docarqserv;
        addArquivoAcesso($arquivo);
        $filename = $GLOBALS['CAMINHO_UPLOADS'] . $arquivo;
        
        $tpl->VALOR_TITULO = $doctit;
        $tpl->VALOR_DESCRICAO = $docdesc;
        
        if (file_exists($filename)) {
            $tpl->VALOR_IMAGEM = "templates/_assets/themes/default/img/save-disk_32x32.png";
            $tpl->VALOR_ARQUIVO = '../carregarArquivo.php?arq=' . $arquivo;
            $tpl->TARGET = "_blank";
            $tpl->block("BLOCO_TABELA");
        } else {
            $tpl->VALOR_IMAGEM = "templates/_assets/themes/default/img/disqueteInexistente.gif";
            $tpl->block("BLOCO_TABELA_SEMARQUIVO");
        }
    }
}

$tpl->show();