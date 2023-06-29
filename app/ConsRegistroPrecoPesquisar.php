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
 * @version   Git: $Id:$
 * ---------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/10/2018
 * Objetivo: Tarefa Redmine 199575
 * ---------------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     19/09/2022
 * Objetivo: Tarefa Redmine 268893
 * ---------------------------------------------------------------------------------
 */

if (!@require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao( "templates/ConsRegistroPrecoPesquisar.html", "ConsRegistroPrecoPesquisar" );

//variáveis locais
$arrayResultado = array();
$arrayResultado2 = array();

$acao = $_POST["BotaoAcao"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$comissao = $_POST["ItemComissao"];
	$orgao    = $_POST["ItemOrgao"];
	$objeto   = $_POST["ItemObjeto"];

	

	$uri  = 'ConsRegistroPrecoResultado.php';
	header('location: ' . $uri);
}

$db = Conexao();

$sql = "SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";

$result = $db->query($sql);

if (PEAR::isError($result)) {
	$tpl->ERROR = ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
	array_push($arrayResultado, $Linha);
}

for ($i=0; $i < count($arrayResultado); $i++) {
	$tpl->ID_CONSULTA_LICITANTE   = $arrayResultado[$i][0];
	$tpl->NOME_CONSULTA_LICITANTE = $arrayResultado[$i][1];
    $tpl->block("BLOCK_OPTION");
    $tpl->block("BLOCK_OPTION_3");
}

// Grupos
$arrayGrupoMaterial = array();

$sql  = "SELECT DISTINCT CGRUMSCODI, EGRUMSDESC ";
$sql .= "FROM	SFPC.TBGRUPOMATERIALSERVICO ";
$sql .= "WHERE	FGRUMSTIPO = 'M' ";
$sql .= " 		AND FGRUMSSITU = 'A' ";
$sql .= "ORDER BY EGRUMSDESC ";

$result = $db->query($sql);

if (PEAR::isError($result)) {
	$tpl->ERROR = ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
	array_push($arrayGrupoMaterial, $Linha);
}

for ($i=0; $i < count($arrayGrupoMaterial); $i++) {
	$tpl->GRUPO_MATERIAL_KEY   = $arrayGrupoMaterial[$i][0];
	$tpl->GRUPO_MATERIAL_VALUE = $arrayGrupoMaterial[$i][1];
	$tpl->block("BLOCK_GRUPO_MATERIAL");
}

$arrayGrupoServico = array();

$sql  = "SELECT	DISTINCT CGRUMSCODI, EGRUMSDESC ";
$sql .= "FROM	SFPC.TBGRUPOMATERIALSERVICO ";
$sql .= "WHERE	FGRUMSTIPO = 'S' ";
$sql .= "		AND FGRUMSSITU = 'A' ";
$sql .= "ORDER BY EGRUMSDESC ";

$result = $db->query($sql);

if (PEAR::isError($result)) {
	$tpl->ERROR = ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {
	array_push($arrayGrupoServico, $Linha);
}

for ($i=0; $i < count($arrayGrupoServico); $i++) {
	$tpl->GRUPO_SERVICO_KEY   = $arrayGrupoServico[$i][0];
	$tpl->GRUPO_SERVICO_VALUE = $arrayGrupoServico[$i][1];
	$tpl->block("BLOCK_GRUPO_SERVICO");
}

$db->disconnect();

$db = Conexao();

$sql  = "SELECT CCOMLICODI, ECOMLIDESC, CGREMPCODI ";
$sql .= "FROM	SFPC.TBCOMISSAOLICITACAO ";
$sql .= "ORDER BY CGREMPCODI,ECOMLIDESC ";

$result = $db->query($sql);

if (PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while ($Linha = $result->fetchRow()) {

	array_push($arrayResultado2, $Linha);
	
}

for ($i=1; $i < count($arrayResultado); $i++) {
	//carona externa não deve ser exibida por isso array começando no indice 1
	if($arrayResultado2[$i]!=''){
		$tpl->ID_CONSULTA_COMISSAO   = $arrayResultado2[$i][0];
			if($arrayResultado2[$i][1] != 'CARONA EXTERNA'){
			$tpl->NOME_CONSULTA_COMISSAO = $arrayResultado2[$i][1];
			}	
		$tpl->block("BLOCK_OPTION_2");
	}
}
//var_dump($arrayResultado2);die;

$tpl->show();
?>