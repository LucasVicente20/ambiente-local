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
 */

if (!@require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao(
    "templates/ConsRegistroPrecoPesquisar2017.html",
    "ConsRegistroPrecoPesquisar"
);
//variáveis locais
$arrayResultado = array();
$arrayResultado2 = array();

$acao = $_POST["BotaoAcao"];

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$comissao = $_POST["ItemComissao"];
	$orgao = $_POST["ItemOrgao"];
	$objeto = $_POST["ItemObjeto"];


	$uri  = 'ConsRegistroPrecoResultado.php';
	header('location: ' . $uri);
}


$db     = Conexao();
$sql    = "SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";
$result = $db->query($sql);


if( PEAR::isError($result) ){
	$tpl->ERROR = ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}
while (  $Linha = $result->fetchRow()) {
	array_push($arrayResultado, $Linha);

}

for ($i=0; $i < count($arrayResultado); $i++) {
	$tpl->ID_CONSULTA_LICITANTE   = $arrayResultado[$i][0];
	$tpl->NOME_CONSULTA_LICITANTE = $arrayResultado[$i][1];
	$tpl->block("BLOCK_OPTION");
}

$db->disconnect();


$db     = Conexao();
$sql   = "SELECT CCOMLICODI, ECOMLIDESC, CGREMPCODI ";
$sql   .= "FROM SFPC.TBCOMISSAOLICITACAO ORDER BY CGREMPCODI,ECOMLIDESC";
$result = $db->query($sql);

if( PEAR::isError($result) ){
ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

while (  $Linha = $result->fetchRow()) {
	array_push($arrayResultado2, $Linha);

}

for ($i=0; $i < count($arrayResultado); $i++) {
	$tpl->ID_CONSULTA_COMISSAO   = $arrayResultado2[$i][0];
	$tpl->NOME_CONSULTA_COMISSAO = $arrayResultado2[$i][1];
	$tpl->block("BLOCK_OPTION_2");
}


$tpl->show();

?>