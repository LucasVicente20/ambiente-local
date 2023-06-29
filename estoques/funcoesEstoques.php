<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: funcoesEstoques.php
# Objetivo: funções com regras do módulo Estoques
# Autor:    Ariston Cordeiro
#-----------------------
# Alterado:
# Data:
#---------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# arquivo geral de funcoes
require_once("../funcoes.php");

#
function getOrgaoUnidadeSofin($db, $idOrgaoPortal){
	assercao(!is_null($db), "Variável de banco de dados não foi inicializada");
	assercao(!is_null($idOrgaoPortal), "Parâmetro 'idOrgaoPortal' requerido");	
	$sql = "
			select distinct ccenpocorg, ccenpounid
			from tbcentrocustoportal
			where corglicodi = ".$idOrgaoPortal."
	";
	$obj = $resultObjetoUnico( executarSQL($db, $sql ) );
	assercao(!is_null($obj), "id de órgão não existe, ou ele não foi associado a um Centro de Custo.");	
	$resposta = array();
	$resposta['orgaoPortal'] = $idOrgaoPortal;
	$resposta['orgaoSofin'] = $obj->ccenpocorg;
	$resposta['unidadeSofin'] = $obj->ccenpounid; 
	return $resposta;
}
function getCentroCusto($db, $idCentroCusto){
	assercao(!is_null($db), "Variável de banco de dados não foi inicializada");
	assercao(!is_null($idCentroCusto), "Parâmetro 'idCentroCusto' requerido");	
	$sql = "
			select distinct 
				corglicodi, acenpoanoe, ccenpocorg, ccenpounid, ccenponrpa, 
				ccenpocent, ccenpodeta, ecenpodesc, ecenpodeta, fcenpositu
			from sfpc.tbcentrocustoportal
			where ccenposequ = ".$idCentroCusto."
	";
	$obj = resultObjetoUnico( executarSQL($db, $sql ) );
	assercao(!is_null($obj), "id de centro de custo não existe.");	
	$resposta = array();
	$resposta['anoPortal'] = $obj->acenpoanoe;
	$resposta['orgaoPortal'] = $obj->corglicodi;
	$resposta['centroCustoPortal'] = $idCentroCusto;
	$resposta['orgaoSofin'] = $obj->ccenpocorg;
	$resposta['unidadeSofin'] = $obj->ccenpounid; 
	$resposta['rpaSofin'] = $obj->ccenporpa; 
	$resposta['centroCustoSofin'] = $obj->ccenpocent;
	$resposta['detalhamentoSofin'] = $obj->ccenpodeta;
	$resposta['centroCustoDescricao'] = $obj->ecenpodesc;
	$resposta['detalhamentoDescricao'] = $obj->ecenpodeta;
	$resposta['situacao'] = $obj->fcenpositu;
	return $resposta;
}


?>
