<?php

require_once("../funcoes.php");
require_once (CAMINHO_SISTEMA . "app/TemplateAppPadrao.php");

$tpl = new TemplateAppPadrao("templates/ConsRegistroPrecoResultado2017.html");

$arrayResultado = array();
$objeto = $_POST["ItemObjeto"];
$comissao = $_POST["ItemComissao"];
$orgao = $_POST["ItemOrgao"];

$tpl->ITEM_OBJETO  = $objeto;
$tpl->ITEM_COMISSAO  = $comissao;
$tpl->ITEM_ORGAO  = $orgao;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao                = $_POST['Botao'];
	$Critica              = $_POST['Critica'];
	$OrgaoLicitanteCodigo = $orgao;
	$ComissaoCodigo       = $comissao;
	$ModalidadeCodigo     = '';
	$Objeto               = strtoupper2($objeto);
	$LicitacaoAno         = '';
} else {
	$Objeto               = strtoupper2($objeto);
	$OrgaoLicitanteCodigo = $orgao;
	$ComissaoCodigo       = $comissao;
	$ModalidadeCodigo     = '';
	$LicitacaoAno         = '';
}

$db   = Conexao();
$Data = date("Y-m-d");
$sql  = "SELECT DISTINCT C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.CLICPOPROC, ";
$sql .= "       A.ALICPOANOP, A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, ";
$sql .= "       A.TLICPODHAB, B.EORGLIDESC, A.CGREMPCODI, A.CCOMLICODI, ";
$sql .= "       A.CORGLICODI ";
$sql .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBGRUPOEMPRESA C, ";
$sql .= "       SFPC.TBCOMISSAOLICITACAO D, SFPC.TBMODALIDADELICITACAO E, SFPC.TBFASELICITACAO F, SFPC.TBATAREGISTROPRECO G ";
$sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.FLICPOSTAT = 'A' ";
$sql .= "   AND A.CGREMPCODI = C.CGREMPCODI AND A.CCOMLICODI = D.CCOMLICODI ";
$sql .= "   AND A.CMODLICODI = E.CMODLICODI AND A.TLICPODHAB <= '$Data 23:59:59' ";
$sql .= "   AND A.CLICPOPROC = F.CLICPOPROC AND A.ALICPOANOP = F.ALICPOANOP ";
$sql .= "   AND A.CORGLICODI = F.CORGLICODI AND A.CGREMPCODI = F.CGREMPCODI ";
$sql .= "   AND A.CCOMLICODI = F.CCOMLICODI AND F.CFASESCODI = 13 ";
$sql .= "   AND A.CGREMPCODI = G.CGREMPCODI AND A.CCOMLICODI = G.CCOMLICODI ";
$sql .= "   AND A.CLICPOPROC = G.CLICPOPROC AND G.ALICPOANOP = F.ALICPOANOP ";
$sql .= "   AND A.CORGLICODI = G.CORGLICODI ";

if ($Objeto != "") {
	$sql .= " AND TRANSLATE(A.XLICPOOBJE,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($Objeto))."%' ";
}

if ($ComissaoCodigo != "") {
	$sql .= " AND A.CCOMLICODI = $ComissaoCodigo ";
}

if ($ModalidadeCodigo != "") {
	$sql .= " AND A.CMODLICODI = $ModalidadeCodigo ";
}

if ($OrgaoLicitanteCodigo != "") { 
	$sql .= " AND A.CORGLICODI = $OrgaoLicitanteCodigo ";
}

$sql .= " ORDER BY C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.ALICPOANOP, A.CLICPOPROC ";
$result = $db->query($sql);


while ($cols = $result->fetchRow()) {  
	array_push($arrayResultado, $cols);
}

if (count($arrayResultado) === 0) {
	$tpl->exibirMensagemFeedback("Nenhuma ocorrência foi encontrada", "1");
}

$ultimaModalidadePlotada = "";
$ultimaComissaoPlotada = "";
$ultimoGrupoPlotado = "";

for ($i = 0; $i < count($arrayResultado) ; $i++) {
	if ($arrayResultado[$i][0] != '' && $ultimoGrupoPlotado != $arrayResultado[$i][0]) {
		$tpl->GRUPO_DESCRICAO = $arrayResultado[$i][0];
		$ultimoGrupoPlotado = $arrayResultado[$i][0];
		$tpl->block("BLOCO_GRUPO");
		$ultimaModalidadePlotada = "";
	}
           
	if ($ultimaModalidadePlotada != $arrayResultado[$i][1]) {
		$tpl->MODALIDADE_DESCRICAO = $arrayResultado[$i][1];
		$tpl->block("BLOCO_MODALIDADE");
		$ultimaModalidadePlotada = $arrayResultado[$i][1];
		$ultimaComissaoPlotada = "";
	}

	if ($ultimaComissaoPlotada != $arrayResultado[$i][2]) {
		$tpl->COMISSAO_DESCRICAO = $arrayResultado[$i][2];
		$tpl->block("BLOCO_COMISSAO");
	}
			
	if ($ultimaComissaoPlotada != $arrayResultado[$i][2]) {
		$tpl->block("BLOCO_CABECALHO");
		$ultimaComissaoPlotada = $arrayResultado[$i][2];
	}

	$tpl->CODIGO_GRUPO = $arrayResultado[$i][10];
	$tpl->CODIGO_PROCESSO = $arrayResultado[$i][3];
	$tpl->ANO_PROCESSO = $arrayResultado[$i][4];
	$tpl->CODIGO_COMISSAO = $arrayResultado[$i][11];
	$tpl->CODIGO_ORGAO = $arrayResultado[$i][12];
	$tpl->PROCESSO = str_pad($arrayResultado[$i][3], 3, "0", STR_PAD_LEFT).'/'.$arrayResultado[$i][6];
	$tpl->LICITACAO = $arrayResultado[$i][5].'/'.$arrayResultado[$i][6];
	$tpl->OBJETO = $arrayResultado[$i][7];
	$tpl->DATA_HORA_ABERTURA = $arrayResultado[$i][8];
	$tpl->ORGAO_LICITANTE =$arrayResultado[$i][9];
	       
	if ($i + 1 < count($arrayResultado)) {
		if ($ultimaComissaoPlotada != $arrayResultado[$i+1][2]) {
			$tpl->block("BLOCO_SEPARATOR");
		}
	}
	       
	$tpl->block("BLOCO_VALORES");
	$tpl->block("BLOCO_CORPO");
}

$tpl->show();
