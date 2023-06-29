<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AjaxDescricaoMaterial.php
# Autor:    Carlos Abreu
# Data:     24/11/2006
# Objetivo: Programa para retornar descricao do material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../../funcoes.php';

session_start();

$Localizacao = $_SESSION['Localizacao'];
$Codigo = (int)trim($_GET['Codigo']);

if (!$Localizacao){ $Localizacao = 0; }
if (!$Codigo){ $Codigo = 0; }

$db = Conexao();
$sql  = "SELECT A.EMATEPDESC, C.EUNIDMSIGL ";
$sql .= "  FROM SFPC.TBMATERIALPORTAL A, ( ";
$sql .= "       SELECT CMATEPSEQU ";
$sql .= "         FROM SFPC.TBARMAZENAMENTOMATERIAL ";
$sql .= "        WHERE CLOCMACODI = $Localizacao ";
$sql .= "        UNION ";
$sql .= "       SELECT CMATEPSEQU ";
$sql .= "         FROM SFPC.TBINVENTARIOMATERIAL ";
$sql .= "        WHERE CLOCMACODI = $Localizacao ";
$sql .= "       ) AS B, SFPC.TBUNIDADEDEMEDIDA AS C ";
$sql .= " WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
$sql .= "   AND A.CMATEPSEQU = $Codigo ";
$sql .= "   AND A.CUNIDMCODI = C.CUNIDMCODI";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    $db->disconnect();
}else{
		$Rows = $result->numRows();
		if ($Rows>0){
				$Linha = $result->fetchRow();
				echo str_replace(array("“","”","”","\r","\n"),array(" "," "," "," "," "),$Linha[0]." (".$Linha[1].")");
		} else {
				echo "<b>MATERIAL NÃO CADASTRADO NO ALMOXARIFADO</b>";
		}
}
$db->disconnect();

?>
