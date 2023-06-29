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
 * @version   GIT: v1.13.0-21-ga225723
 */
/**
* -----------------------------------------------------------------------------
 * HISTORICO DE ALTERACOES DO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data:     21/07/2015 - 08/09/2015
 * Objetivo: CR95756 - Pesquisa de Licitações por item de material ou serviço - vários programas
 * -----------------------------------------------------------------------------
  * Alterado: Lucas Vicente
 * Data:     04/11/2022
 * Objetivo: CR 270290 - Mover botão de Exportar para tela de Resultados
 * -----------------------------------------------------------------------------
 */
if (!@require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao("templates/ConsLicitacoesAndamento.html", "ConsLicitacoesAndamento");

//variaveis locais
$arrayResultado = array();
$arrayComissao = array();
$arrayModalidade = array();
$arraySituacao = array();
$arrayAnoSituacao = array();

//selects para preencher o combobox - Órgão Licitante
$db     = Conexao();
$sql    = "SELECT CORGLICODI,EORGLIDESC,FORGLITIPO FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
    array_push($arrayResultado, $Linha);
}

for ($i = 0; $i < count($arrayResultado); $i++) {
    $tpl->DATA_TIPO   = $arrayResultado[$i][2];
    $tpl->NOME_ORGAO_LICITANTE = $arrayResultado[$i][1];
    $tpl->VALUE_ORGAO_LICITANTE = $arrayResultado[$i][0];
    $tpl->ORGAO_SELECIONADO = "";

    if (isset($_SESSION['Mensagem']) &&
        !empty($_SESSION['Mensagem']) &&
        $arrayResultado[$i][0] == $_SESSION['OrgaoLicitanteCodigo']) {
        $tpl->ORGAO_SELECIONADO = "selected";
    }

    $tpl->block("BLOCK_ORGAO_LICIANTE");
}

$db->disconnect();

//selects para preencher o combobox - comissão
$db = Conexao();
$sql = "SELECT CCOMLICODI,ECOMLIDESC,CGREMPCODI FROM SFPC.TBCOMISSAOLICITACAO ORDER BY CGREMPCODI,ECOMLIDESC";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
    array_push($arrayComissao, $Linha);
}

for ($i = 0; $i < count($arrayComissao); $i++) {
    if ($arrayComissao[$i][0] != null) {
        $tpl->VALUE_COMISSAO = $arrayComissao[$i][0];
        $tpl->NOME_COMISSAO = $arrayComissao[$i][1];
        $tpl->COMISSAO_SELECIONADA = "";

        if (isset($_SESSION['Mensagem']) &&
            !empty($_SESSION['Mensagem']) &&
            $arrayComissao[$i][0] == $_SESSION['ComissaoCodigo']) {
            $tpl->COMISSAO_SELECIONADA = "selected";
        }

        $tpl->block("BLOCK_COMISSAO");
    }
}

$db->disconnect();

//select para preencher o combobox - modalidade
$db = Conexao();
$sql    = "SELECT CMODLICODI, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}
while ($Linha = $result->fetchRow()) {
    array_push($arrayModalidade, $Linha);
}

for ($i = 0; $i < count($arrayModalidade); $i++) {
    $tpl->VALUE_MODALIDADE = $arrayModalidade[$i][0];
    $tpl->NOME_MODALIDADE = $arrayModalidade[$i][1];
    $tpl->MODALIDADE_SELECIONADA = "";

    if (isset($_SESSION['Mensagem']) &&
        !empty($_SESSION['Mensagem']) &&
        $arrayModalidade[$i][0] == $_SESSION['ModalidadeCodigo']) {
        $tpl->MODALIDADE_SELECIONADA = "selected";
    }

    $tpl->block("BLOCK_MODALIDADE");
}

$db->disconnect();

// select combo box situação
$db = Conexao();
$sql = "SELECT CFASESCODI, EFASESDESC FROM SFPC.TBFASES ORDER BY EFASESDESC";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}
while ($Linha = $result->fetchRow()) {
    array_push($arraySituacao, $Linha);
}

$db->disconnect();

//SELECT ANO SITUACAO
$db = Conexao();
$idFasesEmAndamento = implode(', ', getIdFasesEmAndamento($db));
$sql  = "SELECT DISTINCT TO_CHAR(TLICPODHAB,'YYYY') ";
$sql .= " FROM SFPC.TBLICITACAOPORTAL LP ";
$sql .= " INNER JOIN SFPC.TBFASELICITACAO FL ";
$sql .= " ON LP.clicpoproc = FL.clicpoproc ";
$sql .= " and LP.alicpoanop = FL.alicpoanop ";
$sql .= " and LP.cgrempcodi = FL.cgrempcodi ";
$sql .= " and LP.ccomlicodi = FL.ccomlicodi ";
$sql .= " and LP.corglicodi = FL.corglicodi ";
// Na fase interna não deve aparecer na internet (valor 1)
$sql .= " and FL.cfasescodi IN($idFasesEmAndamento) AND FL.cfasescodi <> 1";
$sql   .= " ORDER BY TO_CHAR(TLICPODHAB,'YYYY') DESC";
$result = $db->query($sql);

if (db::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
    array_push($arrayAnoSituacao, $Linha);
}

if (isset($_SESSION['Mensagem']) && !empty($_SESSION['Mensagem'])) {
    $tpl->VALOR_OBJETO = $_SESSION['Objeto'];
    $tpl->VALOR_DESCRICAO_ITEM = $_SESSION['Item'];

    if ($_SESSION['TipoItemLicitacao'] == "Material") {
        $tpl->TIPO_MATERIAL = "selected";
        $tpl->TIPO_SERVICO = "";
    } elseif ($_SESSION['TipoItemLicitacao'] == "Servico") {
        $tpl->TIPO_MATERIAL = "";
        $tpl->TIPO_SERVICO = "selected";
    }

    $tpl->exibirMensagemFeedback($_SESSION['Mensagem'], $_SESSION['Tipo']);
    $_SESSION['Mensagem'] = null;
}

//exibe o template
$tpl->show();
