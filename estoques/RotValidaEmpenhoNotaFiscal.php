<?php
#--------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotValidaEmpenhoNotaFiscal.php
# Autor:    Rodrigo Melo
# Data:     28/07/2008
# Objetivo: página para testar a busca do valor do empenho.
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
//Seguranca();

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
		$Url = "estoques/CorrecaoCadIncluirEmpenho.php?Mens=1&Tipo=1&Mensagem=".urlencode($Mensagem)."&ProgramaOrigem=$ProgramaOrigem";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		RedirecionaPost($Url);
}
 

/*$AnoEmpenho        = 2008;
$OrgaoEmpenho      = 36;
$UnidadeEmpenho    = 1;
$SequencialEmpenho = 270;
#$ParcelaEmpenho    = $Emp[4];*/

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Abre a Conexão com Oracle #
$dbora  = ConexaoOracle();

# Monta a Query
$sql = "SELECT EMP.AEMPENSEQU, ";

if ($ParcelaEmpenho != null && trim($ParcelaEmpenho) != '') {  
  $sql .= " TO_CHAR(SUB.DSBEMPEMIS, 'YYYY-MM-DD HH24:MI:SS'), "; // PARA SUBEMPENHO
} else {
  $sql .= " TO_CHAR(EMP.DEMPENEMIS, 'YYYY-MM-DD HH24:MI:SS'), "; //PARA EMPENHO  
}

if ($ParcelaEmpenho != null && trim($ParcelaEmpenho) != '') {  
  $sql .= " (NVL(SUB.VSBEMPSUBE,0) - (NVL(SUB.VSBEMPANUL,0) + NVL(SUB.VSBEMPPAGO,0))) AS VALOR "; //VALOR DO SUBEMPENHO
} else {
  $sql .= " (NVL(EMP.VEMPENEMPE,0) - (NVL(EMP.VEMPENANUL,0) + NVL(EMP.VEMPENPAGO,0))) AS VALOR "; //VALOR DO EMPENHO
}

$sql .= " FROM SFCO.TBTIPOEMPENHO TIP, SFCO.TBEMPENHO EMP ";

if ($ParcelaEmpenho != null && trim($ParcelaEmpenho) != '') {
  $sql .= "   , SFCO.TBSUBEMPENHO SUB ";
}

$sql .= " WHERE EMP.DEMPENANOO = $AnoEmpenho ";
$sql .= " AND EMP.CORGORCODI = $OrgaoEmpenho ";
$sql .= " AND EMP.CUNDORCODI = $UnidadeEmpenho ";
$sql .= " AND EMP.AEMPENSEQU = $SequencialEmpenho ";

if ($ParcelaEmpenho != null && trim($ParcelaEmpenho) != '') {
  $sql .= " AND SUB.ASBEMPSEQU = $ParcelaEmpenho ";

  $sql .= " AND EMP.DEMPENANOO = SUB.DEMPENANOO ";
  $sql .= " AND EMP.AEMPENNUME = SUB.AEMPENNUME ";
  $sql .= " AND EMP.CORGORCODI = SUB.CORGORCODI ";
  $sql .= " AND EMP.CUNDORCODI = SUB.CUNDORCODI ";
  $sql .= " AND EMP.AEMPENSEQU = SUB.AEMPENSEQU ";
}

if ($ParcelaEmpenho != null && trim($ParcelaEmpenho) != '') {
  $sql .= " AND (NVL(SUB.VSBEMPSUBE,0) - (NVL(SUB.VSBEMPANUL,0) + NVL(SUB.VSBEMPPAGO,0))) > 0 "; //PARA SUBEMPENHO  
} else {
  $sql .= " AND (NVL(EMP.VEMPENEMPE,0) - (NVL(EMP.VEMPENANUL,0) + NVL(EMP.VEMPENPAGO,0))) > 0 "; //PARA EMPENHO
}

$sql .= " AND EMP.CTPEMPCODI = TIP.CTPEMPCODI ";

if ($ParcelaEmpenho != null && trim($ParcelaEmpenho) != '') {
  $sql .= " AND TIP.FTPEMPSUEM = 'S' "; //PARA SUBEMPENHO
} else {  
  $sql .= " AND TIP.FTPEMPSUEM = 'N' "; //PARA EMPENHO
}

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
    
    echo "<html><body>";
    echo "qtdres: $qtdres";
    echo "<BR>";
    echo "DataEmissao: $DataEmissao";
    echo "<BR>";
    echo "Valor: $Valor";
    echo "<BR>";
    echo "SQL: $sql";
    echo "<BR>";
    echo "Empenho: $Empenho";
    echo "<BR>";
    
    
    
}

# Fecha a Conexão com Oracle #
$dbora->disconnect();



# Verifica se houve retorno de empenho válido no banco Oracle
if($qtdres){
		$Url = "estoques/CorrecaoCadIncluirEmpenho.php?EmpenhoChk=1&DataEmissao=".urlencode($DataEmissao)."&Valor=".urlencode($Valor)."&EmpenhoOK=1&Empenho=$Empenho&Botao=$Botao&ProgramaOrigem=$ProgramaOrigem";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		RedirecionaPost($Url);
}else{
		$Url = "estoques/CorrecaoCadIncluirEmpenho.php?EmpenhoChk=1&EmpenhoOK=0&Empenho=$Empenho&Botao=$Botao&ProgramaOrigem=$ProgramaOrigem";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		RedirecionaPost($Url);
}

echo "Url: $Url";
echo "<BR>";
echo "</body></html>";
//exit;

?>
