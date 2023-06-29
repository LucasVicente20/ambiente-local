<?php
/**
 * Portal de Compras
 * 
 * Programa:   RotDadosFornecedor.php
 * Autor:      Ariston Cordeiro
 * Data:       22/08/2011
 * Objetivo:   Rotina de consulta de nome de fornecedor, para chamadas assíncronas Javascript (tipo AJAX)
 * Observação: Note que a rotina foi feita para funcionar se for chamada de outra página em php (isto é, funciona tanto em chamadas síncronas quanto assíncronas)
 * ----------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     27/07/2018
 * Objetivo: Tarefa Redmine 95900
 * ----------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     20/05/2019
 * Objetivo: Tarefa Redmine 210696
 * ----------------------------------------------------------------------------------------------------------------------
 */

$fecharBanco = false;

if (is_null($GLOBALS["DNS_DOMINIO"])) {
	# Acesso ao arquivo de funções #
	require_once("funcoesCompras.php");

	# Executa o controle de segurança #
	session_start();
	Seguranca();

	$db = Conexao();
	$fecharBanco = true;
}

if (!isset($CPFCNPJ) and $_SERVER['REQUEST_METHOD'] == "GET") {
	$CPFCNPJ                  = $_GET['CPFCNPJ'];
	$materialServicoFornecido = $_GET['materialServicoFornecido'];
	$tipoMaterialServico      = $_GET['tipoMaterialServico'];
	$origem                   = $_GET['origem'];
}

$exclusivo = false;

if ($_GET['exclusivo'] and $_SERVER['REQUEST_METHOD'] == "GET") {
    $exclusivo = true;
}

$labelAtencao = "<blink><font bgcolor='DCEDF7' class='titulo2'>Atenção!</font></blink> ";
$labelErro = "<blink><font class='titulo1'>Erro!</font></blink> ";

$resposta = checaSituacaoFornecedor($db, $CPFCNPJ, $exclusivo);

if (!is_null($resposta) and !is_null($resposta["razao"]) and $resposta["razao"] != "" ) {
	if ($resposta["situacao"] == 1) {
		echo $resposta["razao"];
	} else {
		if ($origem != 'scc') {
			echo "<font color='#ff0000'>FORNECEDOR COM SANÇÕES NO SICREF</font>";
		} else {
			echo $resposta["razao"];
			echo " - <font color='#ff0000'>FORNECEDOR COM SANÇÕES NO SICREF</font>";
		}
	}
}

if ($resposta["erro"] == 4 && $exclusivo) {
    echo $resposta["mensagem"];
}

if ($fecharBanco) {
	$db->disconnect();
}
?>