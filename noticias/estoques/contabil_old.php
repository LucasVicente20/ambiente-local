<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsMovimentacaoCompararContabil.php
# Autor:    Carlos Abreu
# Data:     16/02/2007
# Objetivo: Exibir as diferenças dos Valores Contábeis gerados entre o Postgree e o Oracle
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
//session_start();
//Seguranca();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$DataFim             = $_POST['DataFim'];
		$DataIni             = $_POST['DataIni'];
		$Consist             = $_POST['Consist'];
		$Exibicao            = $_POST['Exibicao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Definição da diferença máxima para considerar inconsistência entre valores do Postgree e Oracle #
$Tolerancia = 2;

$calmpocodi = 3;
$dexercanor = 2007;
$DataIni = "04/01/2007";
$DataFim = "04/01/2007";

echo "<font face=verdana size=-2>";

$db = Conexao();
$dbora = ConexaoOracle();

$sqlpost  = "SELECT ";
$sqlpost .= "       CASE WHEN A.CREQMASEQU IS NOT NULL THEN TO_DATE(TO_CHAR(J.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') ELSE A.DMOVMAMOVI END AS DATAMOV, ";
$sqlpost .= "       CASE WHEN A.CREQMASEQU IS NOT NULL THEN '1' ELSE '2' END AS FLAG, A.CALMPOCODI, ";
$sqlpost .= "       H.CCENPOSEQU, ";
$sqlpost .= "       CASE WHEN (CASE WHEN A.CREQMASEQU IS NOT NULL THEN TO_DATE(TO_CHAR(J.TSITRESITU,'YYYY'),'YYYY') ELSE TO_DATE(TO_CHAR(A.DMOVMAMOVI,'YYYY'),'YYYY') END) >= TO_DATE('2007','YYYY') THEN G.FGRUMSTIPC ELSE G.FGRUMSTIPM END AS TIPO, ";
//$sqlpost .= "       SUM( ";
$sqlpost .= "       ( ";
$sqlpost .= "       CASE WHEN A.CTIPMVCODI IN (19,20) THEN 0 ELSE A.AMOVMAQTDM END ";
$sqlpost .= "       + ";
$sqlpost .= "       CASE WHEN ( ";
$sqlpost .= "       SELECT SUM(CASE WHEN B.CTIPMVCODI = 19 THEN -B.AMOVMAQTDM ELSE B.AMOVMAQTDM END) ";
$sqlpost .= "         FROM SFPC.TBMOVIMENTACAOMATERIAL B ";
$sqlpost .= "        WHERE B.CTIPMVCODI IN (19,20) ";
$sqlpost .= "          AND B.CREQMASEQU = A.CREQMASEQU ";
$sqlpost .= "          AND B.CMATEPSEQU = A.CMATEPSEQU ";
$sqlpost .= "       ) IS NOT NULL THEN ( ";
$sqlpost .= "       SELECT CASE WHEN A.CTIPMVCODI IN (19,20) AND (SELECT COUNT(*) FROM SFPC.TBMOVIMENTACAOMATERIAL Z WHERE Z.CTIPMVCODI = 4 AND Z.CREQMASEQU = A.CREQMASEQU AND Z.CMATEPSEQU = A.CMATEPSEQU)>0 THEN 0 ELSE SUM(CASE WHEN B.CTIPMVCODI = 19 THEN -B.AMOVMAQTDM ELSE B.AMOVMAQTDM END) END ";
$sqlpost .= "         FROM SFPC.TBMOVIMENTACAOMATERIAL B ";
$sqlpost .= "        WHERE B.CTIPMVCODI IN (19,20) ";
$sqlpost .= "          AND B.CREQMASEQU = A.CREQMASEQU ";
$sqlpost .= "          AND B.CMATEPSEQU = A.CMATEPSEQU ";
$sqlpost .= "       ) ";
$sqlpost .= "       ELSE "; 
$sqlpost .= "       0 ";
$sqlpost .= "       END ";
$sqlpost .= "       ) * A.VMOVMAVALO";
//$sqlpost .= "       )";
$sqlpost .= "       AS VALOR, ";
$sqlpost .= "       E.FTIPMVTIPO, E.ETIPMVDESC, A.CMOVMACODT, a.creqmasequ, k.amvcpmcont, ";
$sqlpost .= "       k.amvcpmhist, k.amvcpmtpmc, k.fmvcpmdbcd, k.amvcpmlote ";
$sqlpost .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
$sqlpost .= " INNER JOIN SFPC.TBMATERIALPORTAL B ";
$sqlpost .= "    ON A.CMATEPSEQU = B.CMATEPSEQU ";
$sqlpost .= " INNER JOIN SFPC.TBALMOXARIFADOPORTAL C ";
$sqlpost .= "    ON A.CALMPOCODI = C.CALMPOCODI ";
$sqlpost .= " INNER JOIN SFPC.TBALMOXARIFADOORGAO D ";
$sqlpost .= "    ON A.CALMPOCODI = D.CALMPOCODI ";
$sqlpost .= " INNER JOIN SFPC.TBTIPOMOVIMENTACAO E ";
$sqlpost .= "    ON A.CTIPMVCODI = E.CTIPMVCODI ";
$sqlpost .= " INNER JOIN SFPC.TBSUBCLASSEMATERIAL F ";
$sqlpost .= "    ON B.CSUBCLSEQU = F.CSUBCLSEQU ";
$sqlpost .= " INNER JOIN SFPC.TBGRUPOMATERIALSERVICO G ";
$sqlpost .= "    ON F.CGRUMSCODI = G.CGRUMSCODI ";
$sqlpost .= "  LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL I ";
$sqlpost .= "    ON A.CREQMASEQU = I.CREQMASEQU ";
$sqlpost .= "  LEFT OUTER JOIN SFPC.TBSITUACAOREQUISICAO J ";
$sqlpost .= "    ON A.CREQMASEQU = J.CREQMASEQU ";
$sqlpost .= "   AND J.TSITREULAT IN (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO WHERE CREQMASEQU = J.CREQMASEQU) ";
$sqlpost .= "  LEFT OUTER JOIN SFPC.TBCENTROCUSTOPORTAL H ";
$sqlpost .= "    ON D.CORGLICODI = H.CORGLICODI ";
$sqlpost .= "   AND CASE WHEN A.CREQMASEQU IS NOT NULL THEN I.CCENPOSEQU = H.CCENPOSEQU ELSE H.CCENPOCENT = 799 AND H.CCENPODETA = 77 END ";

$sqlpost .= " INNER JOIN SFPC.TBMOVCONTABILALMOXARIFADOPARAM K ";
$sqlpost .= "    ON G.FGRUMSTIPM = K.FMVCPMTIPM ";
$sqlpost .= "   AND A.AMOVMAANOM = K.AMVCPMANOC ";
$sqlpost .= "   AND A.CTIPMVCODI = K.CTIPMVCODI ";

$sqlpost .= " WHERE A.CTIPMVCODI NOT IN (1,3,5,7,8,18,31,33,34) ";
$sqlpost .= "   AND A.CALMPOCODI = $calmpocodi ";
$sqlpost .= "   AND ( (E.CTIPMVCODI IN (12,13,15,30) AND A.FMOVMACORR = 'S') OR (E.CTIPMVCODI NOT IN (12,13,15,30) ) ) ";
$sqlpost .= "   AND CASE WHEN A.CREQMASEQU IS NOT NULL THEN ";
$sqlpost .= "                 TO_DATE(TO_CHAR(J.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') >= '".DataInvertida($DataIni)."' ";
$sqlpost .= "                 AND TO_DATE(TO_CHAR(J.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') <= '".DataInvertida($DataFim)."' ";
$sqlpost .= "            ELSE ";
$sqlpost .= "	                A.DMOVMAMOVI >= '".DataInvertida($DataIni)."' ";
$sqlpost .= "	                AND A.DMOVMAMOVI <= '".DataInvertida($DataFim)."' ";
$sqlpost .= "            END ";
$sqlpost .= "   AND (J.CTIPSRCODI IS NULL OR J.CTIPSRCODI = 5) ";
$sqlpost .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
//$sqlpost .= " GROUP BY FLAG, A.CALMPOCODI, H.CCENPOSEQU, TIPO, DATAMOV, E.FTIPMVTIPO, A.CTIPMVCODI ";
$sqlpost .= " ORDER BY FLAG, A.CALMPOCODI, H.CCENPOSEQU, TIPO, DATAMOV, E.FTIPMVTIPO DESC, A.CTIPMVCODI ";

$res  = $db->query($sqlpost);
if(db::isError($res)){
		EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpost");
		$db->disconnect();
}else{
		$Rows = $res->numRows();
		$Caixa = array();
		for($i=0;$i< $Rows; $i++){
				$Linha = $res->fetchRow();
				$Linha[5] = round($Linha[5],2);
				//$Caixa[][0] = implode(" - ",$Linha)."<br>\n"; 
				$Caixa[][0] = $Linha;
				list($dmovmamovi_ano,$dmovmamovi_mes,$dmovmamovi_dia) = explode("-",$Linha[0]);
				//$Flag = $Linha[1];
				//$calmpocodi = $Linha[2];
				//$CCSequencial = $Linha[3];
				//$TipoMaterial = $Linha[4];
				$Valor = $Linha[5];
				//$TipoMovimentacao = $Linha[6];
				$etipmvdesc = $Linha[7];
				$cmovmacodt = $Linha[8];
				$cmovmarequ = $Linha[9];
				$amvcpmcont = $Linha[10];
				$amvcpmhist = $Linha[11];
				$amvcpmtpmc = $Linha[12];
				$fmvcpmdbcd = $Linha[13];
				$amvcpmlote = $Linha[14];
	
				$sqloracle = "select * " .
						"from sfct.TBmovcontabilalmoxarifado " .
						"where cmvcalalmo = $calmpocodi " .
						"and APLCTAANOC = $dmovmamovi_ano " .
						"and amvcalmesm = $dmovmamovi_mes " .
						"and amvcaldiam = $dmovmamovi_dia " .
						"and AMVCALLOTE = $amvcpmlote " .
						"and CTIPMOCODI = $amvcpmtpmc " .
						"and AHMOVINUME = $amvcpmhist " .
						"and APLCTACONT = $amvcpmcont " .
						"AND FMVCALDBCD = '$fmvcpmdbcd' " .
						"and emvcaldesc = '$etipmvdesc' ";
				if ($Linha[1]==2){
					$sqloracle .= "and CMVCALCODI = $cmovmacodt ";
				} else {
					$sqloracle .= "and CMVCALREQU = $cmovmarequ ";
				}
						
				$resoracle = $dbora->query($sqloracle);
				if( db::isError($resoracle) ){
						$dbora->disconnect();
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqloracle");
				}else{
						$numCaixa = count($Caixa)-1;
						while ($roworacle = $resoracle->fetchRow()){
								if (is_array($roworacle)){
									$Caixa[$numCaixa][1][] = implode(" - ",$roworacle)."<br>\n";
								}
						}
				}
				
		}
		
}
$dbora->disconnect;
$db->disconnect();

echo "<table border=1>";
echo "<tr bgcolor=\"cccccc\">";
echo "<td colspan=\"5\"><font face=verdana size=\"-1\"><b>MOVIMENTAÇÃO SISTEMA ESTOQUE</td>";
echo "<td colspan=\"6\"><font face=verdana size=-1><b>MOVIMENTAÇÃO SISTEMA CONTÁBIL</td>";
echo "</tr>";
echo "<tr bgcolor=\"dddddd\">";
echo "<td><font face=verdana size=-2><b>Data</td>";
echo "<td><font face=verdana size=-2><b>Tipo Material</td>";
echo "<td><font face=verdana size=-2><b>Valor</td>";
echo "<td><font face=verdana size=-2><b>Movimentação</td>";
echo "<td><font face=verdana size=-2><b>Cod.Mov./ Requisição</td>";
echo "<td><font face=verdana size=-2><b>Número Conta Contábil</td>";
echo "<td><font face=verdana size=-2><b>Histórico</td>";
echo "<td><font face=verdana size=-2><b>Movimento Contábil</td>";
echo "<td><font face=verdana size=-2><b>Natureza Lançamento</td>";
echo "<td><font face=verdana size=-2><b>Número Lote Contabil</td>";
echo "<td><font face=verdana size=-2><b>VÁLIDO</td>";
echo "</tr>";
for ($Row=0;$Row<count($Caixa);$Row++){
	if ($Row%2==0){
		echo "<tr>";
	} else {
		echo "<tr bgcolor=\"eeeeee\">";
	}
	$Linha = $Caixa[$Row][0];
	list($dmovmamovi_ano,$dmovmamovi_mes,$dmovmamovi_dia) = explode("-",$Linha[0]);
	echo "<td><font face=verdana size=-2>$dmovmamovi_dia/$dmovmamovi_mes/$dmovmamovi_ano</td>";
	switch ($Linha[4]){
		case 'C':
			$Linha[4] = 'Consumo';
			break;
		case 'P':
			$Linha[4] = 'Permanente';
			break;
		case 'L':
			$Linha[4] = 'Material Limpeza';
			break;
		case 'F':
			$Linha[4] = 'Fardamento';
			break;
		case 'D':
			$Linha[4] = 'Material Didático';
			break;
	}
	echo "<td><font face=verdana size=-2>".$Linha[4]."</td>";
	echo "<td><font face=verdana size=-2>".$Linha[5]."</td>";
	echo "<td><font face=verdana size=-2>".$Linha[7]."</td>";
	if ($Linha[1]==2){
		echo "<td><font face=verdana size=-2>".$Linha[8]."</td>";
	}elseif($Linha[1]==1){
		echo "<td><font face=verdana size=-2>".$Linha[9]."</td>";
	} 
	echo "<td><font face=verdana size=-2>".$Linha[10]."</td>";
	echo "<td><font face=verdana size=-2>".$Linha[11]."</td>";
	echo "<td><font face=verdana size=-2>".$Linha[12]."</td>";
	if ($Linha[13]=='D'){
		echo "<td><font face=verdana size=-2>Débito</td>";
	} elseif($Linha[13]=='C') {
		echo "<td><font face=verdana size=-2>Crédito</td>";
	}
	echo "<td><font face=verdana size=-2>".$Linha[14]."</td>";
	
	echo "<td><font face=verdana size=-2>";
	$QtdOracle = count($Caixa[$Row][1]);
	if ($QtdOracle==0){
		echo "<font color=DD0000><b>NÃO</font>";
	} elseif($QtdOracle==1){
		echo "<font color=00DD00><b>SIM</font>";
	} else {
		for ($Row2=0;$Row2<count($Caixa[$Row][1]);$Row2++){
			echo $Caixa[$Row][1][$Row2];
		}
	}
	echo "</td>";
	
	echo "</tr>";
}
echo "</table>";
?>
