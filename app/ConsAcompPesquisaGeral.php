<?php

/**
 * Portal da DGCO.
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
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   Git: $Id:$
 *
 * -----------------------------------------------------------------------------
 * HISTORICO DE ALTERACOES DO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data:     21/07/2015 - 08/09/2015
 * Objetivo: CR95756 - Pesquisa de Licitações por item de material ou serviço - vários programas
 * -----------------------------------------------------------------------------
 */

header('Location: ConsTodasLicitacoes.php');
exit();

// if (!@require_once dirname(__FILE__).'/TemplateAppPadrao.php') {
//     throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
// }

// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     $licitacaoSituacao = $_POST['licitacaoSituacao'];
//     $LicitacaoAno = $_POST['LicitacaoAno'];

//     $ErroPrograma = __FILE__;

//     $Mens = 0;
//     $Mensagem = '<h4>Informe:</h4>';

//     if (($licitacaoSituacao == 1) && ($LicitacaoAno == '')) {
//         $Mens = 'Mens';
//         $Tipo = 2;
//         $Mensagem .= '<li><a href="javascript:document.ConsAcompPesquisaGeral.LicitacaoAno.focus();" class="titulo2">Informe o Ano</a></li>';
//     }
// }

// $tpl = new TemplateAppPadrao('templates/ConsAcompPesquisaGeral.html', 'ConsAcompPesquisaGeral');
// //variaveis locais
// $arrayResultado = array();
// $arrayComissao = array();
// $arrayModalidade = array();
// $arraySituacao = array();
// $arrayAnoSituacao = array();

// //selects para preencher o combobox - Órgão Licitante
// $db = Conexao();
// $sql = 'SELECT CORGLICODI,EORGLIDESC,FORGLITIPO FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC';
// $result = $db->query($sql);

// if (PEAR::isError($result)) {
//     ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
// }
// while ($Linha = $result->fetchRow()) {
//     array_push($arrayResultado, $Linha);
// }

// if ($_SESSION['RetornoPesquisa'] == 1) {
//     $Mensagem = $_SESSION['Mensagem'];
//     $Mens = $_SESSION['Mens'];
//     $Tipo = $_SESSION['Tipo'];
//     $Objeto = $_SESSION['Objeto'];
//     $OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
//     $ComissaoCodigo = $_SESSION['ComissaoCodigo'];
//     $MocalidadeCodigo = $_SESSION['ModalidadeCodigo'];
//     $Selecao = $_SESSION['Selecao'];
//     $TipoItemLicitacao = $_SESSION['TipoItemLicitacao'];
//     $Item = $_SESSION['Item'];

//     $_SESSION['RetornoPesquisa'] = null;
// }

// for ($i = 0; $i < count($arrayResultado); ++$i) {
//     $tpl->DATA_TIPO = $arrayResultado[$i][2];
//     $tpl->NOME_ORGAO_LICITANTE = $arrayResultado[$i][1];
//     $tpl->VALUE_ORGAO_LICITANTE = $arrayResultado[$i][0];

//     $tpl->ORGAO_SELECIONADO = '';
//     if (!empty($OrgaoLicitanteCodigo) && $OrgaoLicitanteCodigo == $arrayResultado[$i][0]) {
//         $tpl->ORGAO_SELECIONADO = 'selected';
//     }

//     $tpl->block('BLOCK_ORGAO_LICIANTE');
// }

// //unset($arrayResultado);
// $db->disconnect();

// //selects para preencher o combobox - comissão
// $db = Conexao();
// $sql = 'SELECT CCOMLICODI,ECOMLIDESC,CGREMPCODI FROM SFPC.TBCOMISSAOLICITACAO ORDER BY CGREMPCODI,ECOMLIDESC';
// $result = $db->query($sql);

// if (PEAR::isError($result)) {
//     ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
// }
// while ($Linha = $result->fetchRow()) {
//     array_push($arrayComissao, $Linha);
// }

// for ($i = 0; $i < count($arrayComissao); ++$i) {
//     if ($arrayComissao[$i][0] != null) {
//         $tpl->VALUE_COMISSAO = $arrayComissao[$i][0];
//         $tpl->NOME_COMISSAO = $arrayComissao[$i][1];
//         $tpl->block('BLOCK_COMISSAO');
//     }
// }
// $db->disconnect();

// //select para preencher o combobox - modalidade
// $db = Conexao();
// $sql = 'SELECT CMODLICODI, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE';
// $result = $db->query($sql);

// if (PEAR::isError($result)) {
//     ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
// }
// while ($Linha = $result->fetchRow()) {
//     array_push($arrayModalidade, $Linha);
// }

// for ($i = 0; $i < count($arrayModalidade); ++$i) {
//     $tpl->VALUE_MODALIDADE = $arrayModalidade[$i][0];
//     $tpl->NOME_MODALIDADE = $arrayModalidade[$i][1];
//     $tpl->block('BLOCK_MODALIDADE');
// }
// $db->disconnect();

// //SELECT ANO SITUACAO
// $idFasesConcluidas = implode(', ', getIdFasesConcluidas());

// $db = Conexao();
// $sql = "SELECT DISTINCT TO_CHAR(TLICPODHAB,'YYYY') ";
// $sql .= ' FROM SFPC.TBLICITACAOPORTAL LP ';
// $sql .= ' INNER JOIN SFPC.TBFASELICITACAO FL ';
// $sql .= ' ON LP.clicpoproc = FL.clicpoproc ';
// $sql .= ' and LP.alicpoanop = FL.alicpoanop ';
// $sql .= ' and LP.cgrempcodi = FL.cgrempcodi ';
// $sql .= ' and LP.ccomlicodi = FL.ccomlicodi ';
// $sql .= ' and LP.corglicodi = FL.corglicodi ';
// $sql .= " and FL.cfasescodi IN($idFasesConcluidas) ";
// $sql .= ' and (
// 			EXTRACT(
//             	YEAR
// 			FROM
//             	TLICPODHAB
// 			) <= EXTRACT(
// 				YEAR
// 			FROM
// 				CURRENT_DATE
// 			)
// 		 ) ';
// $sql   .= " ORDER BY TO_CHAR(TLICPODHAB,'YYYY') DESC";
// $result = $db->query($sql);

// if (PEAR::isError($result)) {
//     ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
// }
// while ($Linha = $result->fetchRow()) {
//     array_push($arrayAnoSituacao, $Linha);
// }

// for ($i = 0; $i < count($arrayAnoSituacao); ++$i) {
//     $tpl->VALUE_ANO_SITUACAO = $arrayAnoSituacao[$i][0];
//     $tpl->block('BLOCK_ANO_SITUACAO');
// }

// ///exibe o template
// if ($Mens != 0) {
//     $tpl->exibirMensagemFeedback($Mensagem, $Tipo);
// }

// $tpl->show();
