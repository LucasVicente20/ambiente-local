<?php
# Acesso ao arquivo de funçs #
include "../funcoes.php";

$Almoxarifados   = array(2, 5, 10, 15, 17, 19, 20, 21, 22, 24, 35, 39);
$Meses           = array("DEZEMBRO","JANEIRO","FEVEREIRO","MARCO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO","JANEIRO");
$UltimosDiasMes  = array('2006-12-31','2007-01-31','2007-02-28','2007-03-31','2007-04-30','2007-05-31','2007-06-30','2007-07-31','2007-08-31','2007-09-30','2007-10-31','2007-11-30','2007-12-31','2008-01-31');

$db = Conexao();
foreach($Almoxarifados as $AlmoxAtual){
	for($i=0; $i<=12; $i++){
		$sql  = "SELECT SUM(MOV.AMOVMAQTDM * MOV.VMOVMAUMED)- SUM(MOV.AMOVMAQTDM * MOV.VMOVMAVALO) ";
		$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL MOV, SFPC.TBALMOXARIFADOPORTAL ALM ";
		$sql .= " WHERE MOV.CTIPMVCODI IN (13) ";
		$sql .= "   AND MOV.CALMPOCODI = ALM.CALMPOCODI ";
		$sql .= "   AND MOV.VMOVMAUMED <> MOV.VMOVMAVALO ";
		$sql .= "   AND MOV.AMOVMAANOM = 2007 ";
		$sql .= "   AND MOV.CALMPOCODI = $AlmoxAtual ";
		$sql .= "   AND MOV.tmovmaulat > '".$UltimosDiasMes[$i]." 23:59:59' and MOV.tmovmaulat <= '".$UltimosDiasMes[$i+1]." 23:59:59' ";
		$res  = $db->query($sql);
	  if( db::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
			while($Linha = $res->fetchRow()){
				$Almoxarifado = $AlmoxAtual;
				$Mes          = $Meses[$i+1];
				$Diferenca    = $Linha[0];
				if($Diferenca){
					echo "Almoxarifado: $Almoxarifado, Mes: ".$Mes." (".$UltimosDiasMes[$i]." a ".$UltimosDiasMes[$i+1]."), Diferenca: $Diferenca<br>";
				}
			}
		}
	}
}
$db->disconnect();
?>
