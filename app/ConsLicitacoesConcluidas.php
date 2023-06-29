<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version GIT: v1.21.0-16-g300d38d
 *
 * -----------------------------------------------------------------------------
 * HISTORICO DE ALTERAÇÕES NO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado:  Pitang Agile IT
 * Data:      21/07/2015
 * Objetivo:  CR76836 - Licitações Concluídas
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Ernesto Ferreira
 * Data:     19/07/2018
 * Objetivo: CR 96103 - [INTERNET] Licitações Concluídas e Todas as Licitações - 
 * Criar campo de pesquisa por período
 * -------------------------------------------------------------------------
 * Alterado: João Madson Felix Bezerra de Carvalho  
 * Data:     21/01/21
 * Objetivo: CR #243043
 * -------------------------------------------------------------------------
 * # Alterado : Lucas Vicente
 *  # Data: 09/06/2022
 *  # Objetivo: CR # 264494
 *  #---------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     04/11/2022
 * Objetivo: CR 270290 - Mover botão de Exportar para tela de Resultados
 * -----------------------------------------------------------------------------
 * 
 */
if (!@require_once dirname(__FILE__).'/TemplateAppPadrao.php') {
    throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
}
$tpl = new TemplateAppPadrao('templates/ConsLicitacoesConcluidas.html', 'ConsLicitacoesAndamento');

//variaveis locais
$arrayResultado = array();
$arrayComissao = array();
$arrayModalidade = array();
$arraySituacao = array();
$arrayAnoSituacao = array();

//selects para preencher o combobox - Órgão Licitante
$db = Conexao();
$sql = 'SELECT CORGLICODI,EORGLIDESC,FORGLITIPO FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC';
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
    array_push($arrayResultado, $Linha);
}

for ($i = 0; $i < count($arrayResultado); ++$i) {
    $tpl->DATA_TIPO = $arrayResultado[$i][2];
    $tpl->NOME_ORGAO_LICITANTE = $arrayResultado[$i][1];
    $tpl->VALUE_ORGAO_LICITANTE = $arrayResultado[$i][0];
    $tpl->block('BLOCK_ORGAO_LICIANTE');
}

$db->disconnect();

//selects para preencher o combobox - comissão
$db = Conexao();
$sql = 'SELECT CCOMLICODI,ECOMLIDESC,CGREMPCODI FROM SFPC.TBCOMISSAOLICITACAO ORDER BY CGREMPCODI,ECOMLIDESC';
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
    array_push($arrayComissao, $Linha);
}

for ($i = 0; $i < count($arrayComissao); ++$i) {
    if ($arrayComissao[$i][0] != null) {
        $tpl->VALUE_COMISSAO = $arrayComissao[$i][0];
        $tpl->NOME_COMISSAO = $arrayComissao[$i][1];
        $tpl->block('BLOCK_COMISSAO');
    }
}

$db->disconnect();

//select para preencher o combobox - modalidade
$db = Conexao();
$sql = 'SELECT CMODLICODI, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE';
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}
while ($Linha = $result->fetchRow()) {
    array_push($arrayModalidade, $Linha);
}

for ($i = 0; $i < count($arrayModalidade); ++$i) {
    $tpl->VALUE_MODALIDADE = $arrayModalidade[$i][0];
    $tpl->NOME_MODALIDADE = $arrayModalidade[$i][1];
    $tpl->block('BLOCK_MODALIDADE');
}

$db->disconnect();

// select combo box situação
$db = Conexao();
$sql = 'SELECT CFASESCODI, EFASESDESC FROM SFPC.TBFASES ORDER BY EFASESDESC';
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}
while ($Linha = $result->fetchRow()) {
    array_push($arraySituacao, $Linha);
}

$db->disconnect();

//SELECT ANO SITUACAO
$idFasesConcluidas = implode(', ', getIdFasesConcluidas());

$db = Conexao();
$sql = "SELECT DISTINCT TO_CHAR(TLICPODHAB,'YYYY') ";
$sql .= ' FROM SFPC.TBLICITACAOPORTAL LP ';
$sql .= ' INNER JOIN SFPC.TBFASELICITACAO FL ';
$sql .= ' ON LP.clicpoproc = FL.clicpoproc ';
$sql .= ' and LP.alicpoanop = FL.alicpoanop ';
$sql .= ' and LP.cgrempcodi = FL.cgrempcodi ';
$sql .= ' and LP.ccomlicodi = FL.ccomlicodi ';
$sql .= ' and LP.corglicodi = FL.corglicodi ';
$sql .= " and FL.cfasescodi IN($idFasesConcluidas) ";
$sql .= ' and (
			EXTRACT(
            	YEAR
			FROM
            	TLICPODHAB
			) <= EXTRACT(
				YEAR
			FROM
				CURRENT_DATE
			)
		 ) ';
$sql   .= " ORDER BY TO_CHAR(TLICPODHAB,'YYYY') DESC";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
    array_push($arrayAnoSituacao, $Linha);
}

if(!in_array('2022', $arrayAnoSituacao[0])){
    $aux = $arrayAnoSituacao;
    $tamanho = count($arrayAnoSituacao) + 1;
    $j=0;
    for($i = 0; $i < $tamanho; $i++){
        if($i == 0){
            $arrayAnoSituacao[0][0] = "2022";
        }else{
            $arrayAnoSituacao[$i][0] = $aux[$j][0];
            $j++;
        }
    }
}

if (isset($_SESSION['Mensagem']) && !empty($_SESSION['Mensagem'])) {
    $tpl->exibirMensagemFeedback($_SESSION['Mensagem'], $_SESSION['Tipo']);
    $_SESSION['Mensagem'] = null;
}

for ($i = 0; $i < count($arrayAnoSituacao); ++$i) {
    $tpl->VALUE_ANO_SITUACAO = $arrayAnoSituacao[$i][0];
    $tpl->block('BLOCK_ANO_SITUACAO');
}

//exibe o template
$tpl->show();
