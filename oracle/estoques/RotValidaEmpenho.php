<?php
#--------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotValidaEmpenho.php
# Autor:    Álvaro Faria
# Data:     13/10/2005
# Objetivo: Validar numeração de Empenho ou Subempenho no SFCO.TBEMPENHO ou SFCO.TBSUBEMPENHO
#--------------------
# Alterado: Álvaro Faria
# Data:     26/07/2006
# Alterado: Rodrigo Melo
# Data:     27/02/2008 - Alteração para obter a data de emissão do empenho para validação na entrada e alteração da nota fiscal.
# Alterado: Rodrigo Melo
# Data:     09/07/2008 - Alteração para o obter empenhos válidos, ou seja, não nulos e que não sejam subempenhos. Além de obter o valor do empenho - valor anulado do empenho, caso este seja > 0.
# Alterado: Ariston Cordeiro / Rodrigo Melo
# Data:     30/07/2008 	- Alteração para obter empenhos / Subempenhos válidos para alterar uma nota fiscal (empenho/subempenho não anulado completamente)
#--------------------
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$ProgramaOrigem    = $_GET['ProgramaOrigem'];
		$Empenho           = $_GET['Empenho'];
		$Botao             = $_GET['Botao'];
}
# Divide o Empenho em partes #
if($Empenho){
		$Emp               = explode(".",$Empenho);
		$AnoEmpenho        = $Emp[0];
		$OrgaoEmpenho      = $Emp[1];
		$UnidadeEmpenho    = $Emp[2];
		$SequencialEmpenho = $Emp[3];
		$ParcelaEmpenho    = $Emp[4];
}else{
		$Mensagem = "Empenho";
		$Url = "estoques/CadIncluirEmpenho.php?Mens=1&Tipo=1&Mensagem=".urlencode($Mensagem)."&ProgramaOrigem=$ProgramaOrigem";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		RedirecionaPost($Url);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Abre a Conexão com Oracle #
$dbora  = ConexaoOracle();

# Monta a Query
$sql = "SELECT EMP.AEMPENSEQU, ";

if ($ParcelaEmpenho != null && trim($ParcelaEmpenho) != '') {  
  $sql .= " TO_CHAR(SUB.DSBEMPEMIS, 'YYYY-MM-DD HH24:MI:SS'), "; // PARA SUBEMPENHO
	if($ProgramaOrigem=="CadNotaFiscalMaterialIncluir"){
		$sql .= " ( NVL(SUB.VSBEMPSUBE,0) - (NVL(SUB.VSBEMPANUL,0) + NVL(SUB.VSBEMPPAGO,0)) ) AS VALOR "; //VALOR DO SUBEMPENHO
	}else{
		$sql .= " ( NVL(SUB.VSBEMPSUBE,0) - NVL(SUB.VSBEMPANUL,0) ) AS VALOR "; //VALOR DO SUBEMPENHO
	}
} else {
  $sql .= " TO_CHAR(EMP.DEMPENEMIS, 'YYYY-MM-DD HH24:MI:SS'), "; //PARA EMPENHO  
	if($ProgramaOrigem=="CadNotaFiscalMaterialIncluir"){
		$sql .= " ( NVL(EMP.VEMPENEMPE,0) - (NVL(EMP.VEMPENANUL,0) + NVL(EMP.VEMPENPAGO,0)) ) AS VALOR "; //VALOR DO EMPENHO
	}else{
		$sql .= " ( NVL(EMP.VEMPENEMPE,0) - NVL(EMP.VEMPENANUL,0) ) AS VALOR "; //VALOR DO EMPENHO
	}
}

$sql .= " FROM SFCO.TBTIPOEMPENHO TIP, SFCO.TBEMPENHO EMP ";

if ($ParcelaEmpenho != null && trim($ParcelaEmpenho) != '') {
  $sql .= "   , SFCO.TBSUBEMPENHO SUB ";
}

$sql .= " WHERE EMP.DEMPENANOO = $AnoEmpenho ";
$sql .= " AND EMP.CORGORCODI = $OrgaoEmpenho ";
$sql .= " AND EMP.CUNDORCODI = $UnidadeEmpenho ";
$sql .= " AND EMP.AEMPENSEQU = $SequencialEmpenho ";
$sql .= " AND EMP.CTPEMPCODI = TIP.CTPEMPCODI ";

# verifica se recebe parcela  (se é subempenho)
if ($ParcelaEmpenho != null && trim($ParcelaEmpenho) != '') {
  $sql .= " AND SUB.ASBEMPSEQU = $ParcelaEmpenho ";
  $sql .= " AND EMP.DEMPENANOO = SUB.DEMPENANOO ";
  $sql .= " AND EMP.AEMPENNUME = SUB.AEMPENNUME ";
  $sql .= " AND EMP.CORGORCODI = SUB.CORGORCODI ";
  $sql .= " AND EMP.CUNDORCODI = SUB.CUNDORCODI ";
  $sql .= " AND EMP.AEMPENSEQU = SUB.AEMPENSEQU ";
  $sql .= " AND TIP.FTPEMPSUEM = 'S' "; //PARA SUBEMPENHO
	if($ProgramaOrigem=="CadNotaFiscalMaterialIncluir"){
		# Caso seja entrada de nota fiscal, o valor total do subempenho não deve estar nem pago nem anulado 
		$sql .= " AND (NVL(SUB.VSBEMPSUBE,0) - (NVL(SUB.VSBEMPANUL,0) + NVL(SUB.VSBEMPPAGO,0))) > 0 "; //PARA SUBEMPENHO  
	}else{
		# caso seja uma alteração (CadNotaFiscalMaterialManterIncluir ou CadNotaFiscalMaterialManterExcluir) o valor total do subempenho não pode estar anulado
		$sql .= " AND ( NVL(SUB.VSBEMPSUBE,0) - NVL(SUB.VSBEMPANUL,0) ) > 0 "; //PARA SUBEMPENHO  
	}
} else{
	
	if($ProgramaOrigem=="CadNotaFiscalMaterialIncluir"){
		# Caso seja entrada de nota fiscal, o valor total do empenho não deve estar nem pago nem anulado 
		$sql .= " AND (NVL(EMP.VEMPENEMPE,0) - (NVL(EMP.VEMPENANUL,0) + NVL(EMP.VEMPENPAGO,0))) > 0 "; //PARA EMPENHO
	}else{
		# caso seja uma alteração (CadNotaFiscalMaterialManterIncluir ou CadNotaFiscalMaterialManterExcluir) o valor total do empenho não pode estar anulado
		$sql .= " AND ( NVL(EMP.VEMPENEMPE,0) - NVL(EMP.VEMPENANUL,0) ) > 0 "; //PARA EMPENHO
	}
		
  $sql .= " AND TIP.FTPEMPSUEM = 'N' "; //PARA EMPENHO
}
echo $sql;

# Roda a Query
$res  = $dbora->query($sql);
if( PEAR::isError($res) ){
		$dbora->disconnect();
		ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		exit;
}else{
  $row  = $res->fetchRow();
  $qtdres = $row[0];
  $DataEmissao = $row[1];
  $Valor = $row[2];
}

# Fecha a Conexão com Oracle #
$dbora->disconnect();

# Verifica se houve retorno de empenho válido no banco Oracle
if($qtdres != null){
		$Url = "estoques/CadIncluirEmpenho.php?EmpenhoChk=1&DataEmissao=".urlencode($DataEmissao)."&Valor=".urlencode($Valor)."&EmpenhoOK=1&Empenho=$Empenho&Botao=$Botao&ProgramaOrigem=$ProgramaOrigem";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		RedirecionaPost($Url);
}else{
		$Url = "estoques/CadIncluirEmpenho.php?EmpenhoChk=1&EmpenhoOK=0&Empenho=$Empenho&Botao=$Botao&ProgramaOrigem=$ProgramaOrigem";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		RedirecionaPost($Url);
}

?>
