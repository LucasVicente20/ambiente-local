<?php
/**
 * Portal da DGCO
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package Novo Layout
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version GIT: v1.16.1-18-g4900618
 *
 * ----------------------------------------------------------------------------
 * HISTÓRICO DE ALTERAÇÃO
 * ----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI <contato@pitang.com>
 * Data: 18/09/2015 - CR redmine 95697 - Todas as Licitações
 * Link: http://redmine.recife.pe.gov.br/issues/95697
 * Versão: 1.27.1-1-g59aaca1
 * ----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Ernesto Ferreira
 * Data:     19/07/2018
 * Objetivo: CR 96103 - [INTERNET] Licitações Concluídas e Todas as Licitações - 
 * Criar campo de pesquisa por período
 * -------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     16/09/2019
 * Objetivo: Tarefa Redmine 223800
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     04/11/2022
 * Objetivo: CR 270290 - Mover botão de Exportar para tela de Resultados
 * -----------------------------------------------------------------------------
 */

ini_set('display_errors', 0);
error_reporting(E_ALL ^ E_NOTICE);

if (! @require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

/**
 */
class Pitang_App_ConsTodasLicitacoes
{

    /**
     */
    public function __construct()
    {
        unset($_SESSION['RetornoPesquisa'], $_SESSION['Pesquisar'], $_SESSION['Objeto'], $_SESSION['OrgaoLicitanteCodigo'], $_SESSION['ComissaoCodigo'], $_SESSION['ModalidadeCodigo'], $_SESSION['TipoItemLicitacao'], $_SESSION['Item'], $_SESSION['LicitacaoAno'], $_SESSION['GrupoCodigoDet'], $_SESSION['ProcessoDet'], $_SESSION['ProcessoAnoDet'], $_SESSION['ComissaoCodigoDet'], $_SESSION['OrgaoLicitanteCodigoDet']);
    }
}

new Pitang_App_ConsTodasLicitacoes();
$ErroPrograma = __FILE__;

// variaveis locais
$arrayResultado = array();
$arrayComissao = array();
$arrayModalidade = array();
$arraySituacao = array();
$arrayAnoSituacao = array();

// selects para preencher o combobox - Órgão Licitante
$db = Conexao();
$sql = "SELECT CORGLICODI,EORGLIDESC,FORGLITIPO FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
    array_push($arrayResultado, $Linha);
}
$tpl = new TemplateAppPadrao("templates/ConsTodasLicitacoes.html", "ConsLicitacoesAndamento");
for ($i = 0; $i < count($arrayResultado); $i ++) {
    $tpl->DATA_TIPO = $arrayResultado[$i][2];
    $tpl->NOME_ORGAO_LICITANTE = $arrayResultado[$i][1];
    $tpl->VALUE_ORGAO_LICITANTE = $arrayResultado[$i][0];
    $tpl->block("BLOCK_ORGAO_LICIANTE");
}

//$db->disconnect();

// selects para preencher o combobox - comissão
$db = Conexao();
$sql = "SELECT CCOMLICODI,ECOMLIDESC,CGREMPCODI FROM SFPC.TBCOMISSAOLICITACAO ORDER BY CGREMPCODI,ECOMLIDESC";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
    array_push($arrayComissao, $Linha);
}

for ($i = 0; $i < count($arrayComissao); $i ++) {
    if ($arrayComissao[$i][0] != null) {
        $tpl->VALUE_COMISSAO = $arrayComissao[$i][0];
        $tpl->NOME_COMISSAO = $arrayComissao[$i][1];
        $tpl->block("BLOCK_COMISSAO");
    }
}

$db->disconnect();

// select para preencher o combobox - modalidade
$db = Conexao();
$sql = "SELECT CMODLICODI, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
}
while ($Linha = $result->fetchRow()) {
    array_push($arrayModalidade, $Linha);
}

for ($i = 0; $i < count($arrayModalidade); $i ++) {
    $tpl->VALUE_MODALIDADE = $arrayModalidade[$i][0];
    $tpl->NOME_MODALIDADE = $arrayModalidade[$i][1];
    $tpl->block("BLOCK_MODALIDADE");
}

$db->disconnect();

// select combo box situação
$db = Conexao();
$sql = "SELECT CFASESCODI, EFASESDESC FROM SFPC.TBFASES ORDER BY EFASESDESC";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
}
while ($Linha = $result->fetchRow()) {
    array_push($arraySituacao, $Linha);
}

$db->disconnect();

// SELECT ANO SITUACAO
$db = Conexao();
$sql = "SELECT DISTINCT TO_CHAR(TLICPODHAB,'YYYY') ";
$sql .= " FROM SFPC.TBLICITACAOPORTAL LP ";
$sql .= " INNER JOIN SFPC.TBFASELICITACAO FL ";
$sql .= " ON LP.clicpoproc = FL.clicpoproc ";
$sql .= " and LP.alicpoanop = FL.alicpoanop ";
$sql .= " and LP.cgrempcodi = FL.cgrempcodi ";
$sql .= " and LP.ccomlicodi = FL.ccomlicodi ";
$sql .= " and LP.corglicodi = FL.corglicodi ";
$sql .= " and (
            EXTRACT(
                YEAR
            FROM
                TLICPODHAB
            ) <= EXTRACT(
                YEAR
            FROM
                CURRENT_DATE
            )
         ) ";
$sql .= " ORDER BY TO_CHAR(TLICPODHAB,'YYYY') DESC";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {

    array_push($arrayAnoSituacao, $Linha);
}

if (isset($_SESSION['Mensagem']) && !empty($_SESSION['Mensagem'])) {
    $tpl->exibirMensagemFeedback($_SESSION['Mensagem'], $_SESSION['Tipo']);
    $_SESSION['Mensagem'] = null;
}
$anoLista = count($arrayAnoSituacao);
// var_dump($arrayAnoSituacao[1]);die;
for ($i = 0; $i < $anoLista; $i ++) {
    $tpl->VALUE_ANO_SITUACAO = $arrayAnoSituacao[$i][0];
    if (date('Y') == $arrayAnoSituacao[$i][0]) {
        $tpl->ANO_SELECIONADO = 'selected';
    } else {
        $tpl->clear('ANO_SELECIONADO');
    }
    $tpl->block("BLOCK_ANO_SITUACAO");
}

// exibe o template
$tpl->show();
