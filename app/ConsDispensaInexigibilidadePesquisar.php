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

if (!@require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao(
	CAMINHO_SISTEMA . "app/templates/ConsDispensaInexigibilidadePesquisar.html",
	"ConsDispensaInexigibilidadePesquisar"
);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$uri  = 'RotExibeDispensaInexigibilidade.php';
	header('location: ' . $uri);
}

$Ano  = date("Y");
$db   = Conexao();
$arrayResultado = array();

    $sql  = "SELECT DISTINCT CUNIDOORGA, CUNIDOCODI, EUNIDODESC ";
	$sql .= "  FROM SFPC.TBUNIDADEORCAMENTPORTAL ";
	/*$sql .= " WHERE TUNIDOEXER = 2005 ";*/
	$sql .= " WHERE TUNIDOEXER = $Ano ";
	$sql .= " ORDER BY EUNIDODESC";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
		$tpl->ERROR = ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}

	while( $Linha = $result->fetchRow() ){
		array_push($arrayResultado, $Linha);
	}

	for ($i=0; $i < count($arrayResultado) ; $i++) {
		$tpl->PRIMEIRO_ID =  $arrayResultado[$i][0];
		$tpl->SEGUNDO_ID = $arrayResultado[$i][1];
		$tpl->NOME_OPCAO = $arrayResultado[$i][2];

		$tpl->block("BLOCK_OPTION");
	}

$dateIni = '01/01/' . date('Y');
$dateFim = date('d') . '/' . date('m') . '/' . date('Y');

$tpl->VALOR_DATA_INI = $dateIni;
$tpl->VALOR_DATA_FIM = $dateFim;

$db->disconnect();


$tpl->show();
?>
