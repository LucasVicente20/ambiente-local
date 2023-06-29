<?
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CorrecaoDataBaseInventario.php
# Objetivo: Programa para gerar sql com alterações nos bancos postgresql e 
#           oracle das datas de movimentacoes (33 e 34) para a data base do 
#           inventário
# Autor:    Carlos Abreu
# Data:     14/05/2007
#------------------------------------------------------------------------------

exit;

set_time_limit(500000000000);

$NovaDescricaoMovimentacao = "MOVIMENTACAO TRANSFERIDA PARA DATA BASE DO INVENTARIO";

# Acesso ao arquivo de funções #
include "../funcoes.php";

function pegaCodigoCusto($dbora, $Ano,$Mes,$Dia){
	$Codigo = 0;
	$sql  = "SELECT MAX(CMOVCUSEQU) ";
	$sql .= "  FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
	$sql .= " WHERE DEXERCANOR = ".$Ano;
	$sql .= "   AND AMOVCUMESM = ".$Mes;
	$sql .= "   AND AMOVCUDIAM = ".$Dia;
	$res = $dbora->query($sql);
	if( PEAR::isError($res) ){
			$dbora->query("ROLLBACK");
			$dbora->query("END TRANSACTION");
			$dbora->disconnect();
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			exit;
	}else{
			while ( $Linha = $res->fetchRow()){
					$Codigo = $Linha[0];
			}
	}
	$Codigo++;
	return $Codigo;
}

function VerificaNull(&$item, $key) {
	if (is_null($item)){
		$item = 'null';
	}
}

$db   = Conexao();
$dbora = ConexaoOracle();

$db->query("BEGIN TRANSACTION");
$dbora->query("BEGIN TRANSACTION");

$sql = "
SELECT B.CALMPOCODI, TO_CHAR(A.TINVCOFECH,'YYYY-MM-DD'), A.TINVCOFECH, TO_CHAR(A.TINVCOBASE,'YYYY-MM-DD'), A.TINVCOBASE
  FROM SFPC.TBINVENTARIOCONTAGEM A
 INNER JOIN SFPC.TBLOCALIZACAOMATERIAL B
   ON A.CLOCMACODI = B.CLOCMACODI
WHERE (A.CLOCMACODI,A.AINVCOANOB,A.AINVCOSEQU) IN
(
SELECT CLOCMACODI,AINVCOANOB,MAX(AINVCOSEQU)
  FROM SFPC.TBINVENTARIOCONTAGEM
 WHERE (CLOCMACODI,AINVCOANOB) IN 
(
SELECT CLOCMACODI,MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM
 WHERE FINVCOFECH IS NOT NULL
 GROUP BY CLOCMACODI
)
   AND FINVCOFECH IS NOT NULL
   AND A.TINVCOBASE IS NOT NULL
 GROUP BY CLOCMACODI, AINVCOANOB
)

--AND B.CALMPOCODI <= 1

ORDER BY 1
";
$res = $db->query($sql);
if( PEAR::isError($res) ){
	$db->disconnect();
	$dbora->disconnect();
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	exit;
}else{
	while ( $Linha = $res->fetchRow()){
		$Almoxarifados[$Linha[0]][0] = $Linha[1]; // Data de Fechamento
		$Almoxarifados[$Linha[0]][1] = $Linha[2]; // Data e Hora de Fechamento
		$Almoxarifados[$Linha[0]][2] = $Linha[3]; // Data Base
		$Almoxarifados[$Linha[0]][3] = $Linha[4]; // Data e Hora Base
	}
}

foreach ( $Almoxarifados as $Almoxarifado => $Datas ){
	echo $Almoxarifado."<br>";
	$sql  = "SELECT * ";
	$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
	$sql .= " WHERE CALMPOCODI = $Almoxarifado ";
	$sql .= "   AND CTIPMVCODI IN (33,34) ";
	$sql .= "   AND DMOVMAMOVI = '".$Datas[0]."' ";
	$sql .= " ORDER BY 1,2,3 ";
	$res = $db->query($sql);
	if( PEAR::isError($res) ){
		$db->disconnect();
		$dbora->disconnect();
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		exit;
	}else{
		while ( $Linha = $res->fetchRow() ){
			// QUERY DE UPDATE EM SFPC.TBMOVIMENTACAOMATERIAL
			$sql  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL ";
			$sql .= "   SET DMOVMAMOVI = '".$Datas[2]."', TMOVMAULAT = '".$Datas[3]."' ";
			$sql .= " WHERE CALMPOCODI = ".$Linha[0]." ";
			$sql .= "   AND AMOVMAANOM = ".$Linha[1]." ";
			$sql .= "   AND CMOVMACODI = ".$Linha[2]." ";
			$sql .= "   AND DMOVMAMOVI = '".$Datas[0]."'";
			
			$resUp = $db->query($sql);
			if( PEAR::isError($resUp) ){
				$db->query("ROLLBACK");
				$db->query("END TRANSACTION");
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
			}
			
			echo $sql;
			echo "<hr>";
		}
	}
	
	list($Ano,$Mes,$Dia) = explode("-",$Datas[0]);
	$sqloracle  = "SELECT * ";
	$sqloracle .= "  FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
	$sqloracle .= " WHERE DEXERCANOR = $Ano ";
	$sqloracle .= "   AND AMOVCUMESM = $Mes ";
	$sqloracle .= "   AND AMOVCUDIAM = $Dia ";
	$sqloracle .= "   AND EMOVCUDESC IN ('ENTRADA POR GERAÇÃO DE INVENTÁRIO','SAÍDA POR GERAÇÃO DE INVENTÁRIO') ";
	$sqloracle .= "   AND CMOVCUALMO = $Almoxarifado ";
	$resoracle = $dbora->query($sqloracle);
	if( PEAR::isError($resoracle) ){
		$db->disconnect();
		$dbora->disconnect();
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqloracle");
		exit;
	}else{
		while ( $Linha = $resoracle->fetchRow()){
			// QUERY DE INSERT EM SFCP.TBMOVCUSTOALMOXARIFADO
			array_walk($Linha,'VerificaNull');
			$Data = explode("-",$Datas[2]);
			
			$sqloracle  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
			$sqloracle .= "DEXERCANOR,AMOVCUMESM,AMOVCUDIAM,CMOVCUSEQU,CRPAAACODI, ";
			$sqloracle .= "CCENCPCODI,CESPCPCODI,CDETCPCODI,CORGORCODI,CUNDORCODI, ";
			$sqloracle .= "AMOVCUMATR,NMOVCURECE,CEMPRECODI,AUSUACMATR,CORGORCOD1, ";
			$sqloracle .= "CUNDORCOD1,VMOVCUREQU,TMOVCUULAT,FMOVCULANC,EMOVCUDESC, ";
			$sqloracle .= "CMOVCUREQU,CMOVCUALMO,CMOVCUCODI,DMOVCUAMVA ";
			$sqloracle .= ") VALUES ( ";
			$sqloracle .= "".$Data[0].",".$Data[1].",".$Data[2].",".pegaCodigoCusto($dbora,$Data[0],$Data[1],$Data[2]).",".$Linha[4].", ";
			$sqloracle .= "".$Linha[5].",".$Linha[6].",".$Linha[7].",".$Linha[8].",".$Linha[9].", ";
			$sqloracle .= "".$Linha[10].",'".$Linha[11]."',".$Linha[12].",".$Linha[13].",".$Linha[14].", ";
			$sqloracle .= "".$Linha[15].",".$Linha[16].",TO_DATE('".$Datas[3]."','YYYY-MM-DD HH24:MI:SS'),'".$Linha[18]."','".trim($Linha[19])."', ";
			$sqloracle .= "".$Linha[20].",".$Linha[21].",".$Linha[22].",'".$Linha[23]."' ";
			$sqloracle .= ") ";
			
			$rescusto = $dbora->query($sqloracle);
			if( PEAR::isError($rescusto) ){
				$db->query("ROLLBACK");
				$db->query("END TRANSACTION");
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
			}
			
			echo $sqloracle;
			echo "<hr>";
			
			// QUERY DE UPDATE EM SFCP.TBMOVCUSTOALMOXARIFADO
			$sqloracle  = "UPDATE SFCP.TBMOVCUSTOALMOXARIFADO ";
			$sqloracle .= "   SET EMOVCUDESC = '$NovaDescricaoMovimentacao', ";
			$sqloracle .= "       VMOVCUREQU = 0 ";
			$sqloracle .= " WHERE DEXERCANOR = ".$Linha[0];
			$sqloracle .= "   AND AMOVCUMESM = ".$Linha[1];
			$sqloracle .= "   AND AMOVCUDIAM = ".$Linha[2];
			$sqloracle .= "   AND CMOVCUSEQU = ".$Linha[3];
			$sqloracle .= "   AND CMOVCUALMO = $Almoxarifado ";
			
			$rescusto = $dbora->query($sqloracle);
			if( PEAR::isError($rescusto) ){
				$db->query("ROLLBACK");
				$db->query("END TRANSACTION");
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
			}
			
			echo $sqloracle;
			echo "<hr>";
		}
	}
}

$db->query("COMMIT");
$db->query("END TRANSACTION");
$dbora->query("COMMIT");
$dbora->query("END TRANSACTION");

$db->disconnect();
$dbora->disconnect();
?>
