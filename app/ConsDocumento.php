<?php
/**
 * Portal da DGCO
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Novo Layout
 * @package   App
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REL-COD-20160630-0940
 *
 *
 * HISTORICO DE ALTERAÇÔES
 * -----------------------------------------------------------------------
 *  Alterado: Pitang Agile TI
 *  Data:     04/07/2016
 *  Objetivo: Requisito 136739: Cartilhas, Guias e Manuais - Nova funcionalidade internet e intranet (#446)
 */
if (! @require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao("templates/ConsDocumento.html");

$db = Conexao();

$sql  = "SELECT CDOCPOCODI, EDOCPOARQU, EDOCPOTITU, EDOCPODESC, ";
$sql .= "       CDOCPOUSAL, CDOCPOGRAL, TDOCPOULAT, EDOCPOARQS ";
$sql .= "FROM   SFPC.TBDOCUMENTACAOPORTAL ";
$sql .= "WHERE  FDOCPOTIPO LIKE 'L' ";
$sql .= "       AND (FDOCPONLEG IS NULL OR FDOCPONLEG = 'N') ";
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