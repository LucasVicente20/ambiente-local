<?php
# -----------------------------------------------------------------------------------------
# Prefeitura do Recife
# Portal de Compras
# Programa: funcoesMateriais.php
# Objetivo: funções com regras do módulo Materiais
# Autor:    Ariston Cordeiro
# -----------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     27/12/2018
# Objetivo: Tarefa Redmine 208773
# -----------------------------------------------------------------------------------------

# arquivo geral de funcoes
require_once("../funcoes.php");

define("TIPO_ITEM_MATERIAL", 1);
define("TIPO_ITEM_SERVICO", 2);
$GLOBALS["TIPO_ITEM_MATERIAL"] = 1;
$GLOBALS["TIPO_ITEM_SERVICO"] = 2;

function getGrupoDeMaterialServico($db, $materialServico, $tipoMaterialServico) {
	assercao(!is_null($db), "Variável do banco de dados Oracle não foi inicializada");
	assercao(!is_null($materialServico), "Variável 'materialServico' requerida");
	assercao(!is_null($tipoMaterialServico), "Variável 'tipoMaterialServico' requerida");
	
	if ($tipoMaterialServico==TIPO_ITEM_SERVICO) {
		$sql = "SELECT	CGRUMSCODI
				FROM	SFPC.TBSERVICOPORTAL S
				WHERE	S.CSERVPSEQU = " . $materialServico . " ";
	} else {
		$sql = "SELECT	CGRUMSCODI
				FROM	SFPC.TBSUBCLASSEMATERIAL SC, SFPC.TBMATERIALPORTAL M
				WHERE	M.CMATEPSEQU = " . $materialServico . "
						AND SC.CSUBCLSEQU = M.CSUBCLSEQU ";
	}
		
	$grupoMaterial = resultValorUnico( executarSQL($db, $sql) ) ;
	
	return $grupoMaterial;
}

# Pega sub elemento de despesa de grupo de material
function getSubElementoDespesaDeGrupoMaterial($db, $ano, $grupoMaterial, $ocultarSubElemento = false) {
	assercao(!is_null($db), "Variável de banco de dados não foi inicializado");
	assercao(!is_null($ano), "Parâmetro 'ano' requerido");
	assercao(!is_null($grupoMaterial), "Parâmetro 'grupoMaterial' requerido");
	
	$sql = "SELECT	CGRUSEELE1, CGRUSEELE2, CGRUSEELE3, CGRUSEELE4, CGRUSESUBE
			FROM	SFPC.TBGRUPOSUBELEMENTODESPESA SUB
			WHERE	SUB.AGRUSEANOI = $ano
					AND SUB.CGRUMSCODI = $grupoMaterial
					AND SUB.FGRUSESITU = 'A' ";
	
	$linha = resultLinhaUnica( executarSQL($db, $sql ) );
	
	$resposta = null;
	
	if (!is_null($linha)) {
		$resposta = array();
		$resposta['elemento1'] = $linha[0];
		$resposta['elemento2'] = $linha[1];
		$resposta['elemento3'] = $linha[2];
		$resposta['elemento4'] = $linha[3];
		$resposta['subElemento'] = $linha[4];
		$resposta['elementoDespesa'] = $linha[0].".".$linha[1].".".$linha[2].".".$linha[3];
		
		if(!is_null($resposta['subElemento']) and $resposta['subElemento']!='' and !$ocultarSubElemento) {
			$resposta['elementoDespesa'] .= ".".$resposta['subElemento']; //sub elemento
		}
	}
	return $resposta;
}

function isObras($db, $materialServico, $tipoMaterialServico) {
	assercao(!is_null($db), "Variável do banco de dados Oracle não foi inicializada");
	assercao(!is_null($materialServico), "Variável 'materialServico' requerida");
	assercao(!is_null($tipoMaterialServico), "Variável 'tipoMaterialServico' requerida");
	
	$grupo = getGrupoDeMaterialServico($db, $materialServico, $tipoMaterialServico);
	$ano = Date("Y");
	$retorna = false;
	$elementoDespesa = getSubElementoDespesaDeGrupoMaterial($db, $ano, $grupo);
	
	if(!is_null($elementoDespesa)){
		if (($elementoDespesa['elemento1'] == '4' and $elementoDespesa['elemento2'] == '4' and $elementoDespesa['elemento3'] == '90' and $elementoDespesa['elemento4'] == '51')
			or
			($elementoDespesa['elemento1'] == '3' and $elementoDespesa['elemento2'] == '3' and $elementoDespesa['elemento3'] == '90' and $elementoDespesa['elemento4'] == '39')
			){
			$retorna = true;
		}
	}
	return $retorna;
}
?>