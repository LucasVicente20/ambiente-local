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
session_start();
//Seguranca();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$DataFim             = $_POST['DataFim'];
		$DataIni             = $_POST['DataIni'];
		$Consist             = $_POST['Consist'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Definição da diferença máxima para considerar inconsistência entre valores do Postgree e Oracle #
$Tolerancia = 2;

if($Botao == "Limpar"){
		header("location: contabil.php");
		exit;
}elseif($Botao == "Comparar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif ($Almoxarifado == ""){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.ConsMovimentacaoCompararContabil.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"ConsMovimentacaoCompararContabil");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }
		list(,,$AnoDataIni) = explode("/",$DataIni);
}

?>

<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.ConsMovimentacaoCompararContabil.Botao.value=valor;
	document.ConsMovimentacaoCompararContabil.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<style type="text/css"> 
div.Aguarde { 
position: absolute; 
width: 200px; 
height: 100px; 
top: 350px; 
left: 430px; 
z-index: 1;
visibility: visible;
}
</style> 

<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<div class="Aguarde" id="Aguarde"><font class=titulo2><img src="../midia/loading.gif" align="absmiddle"> Aguarde...</font></div> 
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="contabil.php" method="post" name="ConsMovimentacaoCompararContabil">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="7">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Movimentação > Comparar Contábil
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="7"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="9">
									COMPARAÇÃO DE MOVIMENTAÇÕES SISTEMA ESTOQUE E SISTEMA CONTÁBIL
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="9">
									<p align="justify">
										Para comparar os Custos gerados entre os sistemas, selecione o Almoxarifado, o Período e clique no botão "Comparar".
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="9">
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db = Conexao();
												if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if($Almoxarifado and $Almoxarifado != "T"){
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												}else{
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														if ($Almoxarifado) {
																$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
														$sql .= "   AND B.CORGLICODI = ";
														$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] .") ";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if(db::isError($res)){
														EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if($Rows == 1){
																$Linha = $res->fetchRow();
																$Almoxarifado = $Linha[0];
																echo "$Linha[1]<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																echo $DescAlmoxarifado;
														}elseif($Rows > 1){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for($i=0;$i< $Rows; $i++){
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
																		if($Linha[0] == $Almoxarifado){
																				echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
																		}else{
																				echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
																		}
																}
																echo "</select>\n";
																$CarregaAlmoxarifado = "";
														}else{
																echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período</td>
											<td class="textonormal">
												<?php
												$DataMes = DataMes();
												if($DataIni == ""){ $DataIni = $DataMes[0]; }
												if($DataFim == ""){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=ConsMovimentacaoCompararContabil&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=ConsMovimentacaoCompararContabil&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="25%">Consistência</td>
											<td class="textonormal">
												<select name="Consist" class="textonormal">
													<?php
													if($Consist == 'I'){
															echo "<option value=\"I\" selected>Só inconsistentes</option><option value=\"T\">Todos</option>";
													}elseif($Consist == 'T'){
															echo "<option value=\"I\">Só inconsistentes</option><option value=\"T\" selected>Todos</option>";
													}else{
															echo "<option value=\"I\">Só inconsistentes</option><option value=\"T\">Todos</option>";
													}
													?>
												</select>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right" colspan="9">
									<input type="button" value="Comparar" class="botao" onclick="Aguarde.style.visibility='visible';javascript:enviar('Comparar');">
									<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
									<input type="hidden" name="Botao" value="">
								</td>
							</tr>
							<?php
							if($Botao == "Comparar" and $Mens == 0){

									$db = Conexao();
									$dbora = ConexaoOracle();
									
									$sqlHist  = "SELECT ATEXTCNUME, XTEXTCCONT ";
									$sqlHist .= "  FROM SFCT.TBTEXTOCONTABIL ";
									$sqlHist .= " ORDER BY ATEXTCNUME, XTEXTCCONT ";
									$resHist  = $dbora->query($sqlHist);
									if( db::isError($resHist) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
									}else{
											while($LinhaHist = $resHist->fetchRow()){
													$Historico[0][] = $LinhaHist[0];
													$Historico[1][] = $LinhaHist[1];
											}
									}
									
									$sqlMovC  = "SELECT CTIPMOCODI, NTIPMOTABE ";
									$sqlMovC .= "  FROM SFCT.TBTIPOMOVIMENTOCONTABIL ";
									$sqlMovC .= " ORDER BY CTIPMOCODI, NTIPMOTABE ";
									$resMovC  = $dbora->query($sqlMovC);
									if( db::isError($resMovC) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovC");
									}else{
											while($LinhaMovC = $resMovC->fetchRow()){
													$MovContabil[0][] = $LinhaMovC[0];
													$MovContabil[1][] = $LinhaMovC[1];
											}
									}
									
									$sqlMovQ  = "SELECT ctipmvcodi,fmvcpmtipm,count(*) ";
									$sqlMovQ .= "  FROM SFPC.TBmovcontabilalmoxarifadoparam ";
									$sqlMovQ .= " WHERE amvcpmanoc = $AnoDataIni";
									$sqlMovQ .= " GROUP BY amvcpmanoc,ctipmvcodi,fmvcpmtipm ";
									$resMovQ  = $db->query($sqlMovQ);
									if( db::isError($resMovQ) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovQ");
									}else{
											while($LinhaMovQ = $resMovQ->fetchRow()){
													$MovQ[0][] = array($LinhaMovQ[0],$LinhaMovQ[1]);
													$MovQ[1][] = $LinhaMovQ[2];
											}
									}
									
									$sqlpost  = "SELECT ";
									$sqlpost .= "       CASE WHEN A.CREQMASEQU IS NOT NULL AND A.CTIPMVCODI NOT IN (2) THEN TO_DATE(TO_CHAR(J.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') ELSE A.DMOVMAMOVI END AS DATAMOV, ";
									$sqlpost .= "       CASE WHEN A.CREQMASEQU IS NOT NULL THEN '1' ELSE '2' END AS FLAG, ";
									$sqlpost .= "       A.CALMPOCODI, H.CCENPOSEQU, ";
									$sqlpost .= "       SUM( ";
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
									$sqlpost .= "       )";
									$sqlpost .= "       AS VALOR, ";
									$sqlpost .= "       E.FTIPMVTIPO, E.ETIPMVDESC, K.AMVCPMCONT, K.AMVCPMHIST, K.AMVCPMTPMC, ";
									$sqlpost .= "       K.FMVCPMDBCD, K.AMVCPMLOTE, A.CTIPMVCODI, G.FGRUMSTIPM ,A.CREQMASEQU, ";
									$sqlpost .= "       H.CCENPOCORG, H.CCENPOUNID, H.CCENPONRPA, H.CCENPOCENT, H.CCENPODETA, ";
									$sqlpost .= "       G.FGRUMSTIPC, ";
									$sqlpost .= "       CASE WHEN A.CTIPMVCODI IN (12,13,15,30) THEN ";
									$sqlpost .= "            ( ";
 									$sqlpost .= "            SELECT DATATRANSACAO.DMOVMAMOVI ";
 									$sqlpost .= "              FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO ";
 									$sqlpost .= "             WHERE DATATRANSACAO.CALMPOCOD1 = A.CALMPOCODI ";
 									$sqlpost .= "               AND DATATRANSACAO.AMOVMAANO1 = A.AMOVMAANOM ";
 									$sqlpost .= "               AND DATATRANSACAO.CMOVMACOD1 = A.CMOVMACODI ";
 									$sqlpost .= "               AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29) ";
 									$sqlpost .= "               AND CASE WHEN A.CTIPMVCODI = 11 THEN ";
 									$sqlpost .= "                        DATATRANSACAO.CMATEPSEQ1 = A.CMATEPSEQU ";
 									$sqlpost .= "                   ELSE ";
 									$sqlpost .= "                        DATATRANSACAO.CMATEPSEQU = A.CMATEPSEQU ";
 									$sqlpost .= "                   END ";
 									$sqlpost .= "            ) ";
									$sqlpost .= "       ELSE "; 
 									$sqlpost .= "            NULL ";
									$sqlpost .= "       END AS DATATRANSACAO, ";
									$sqlpost .= "       CASE WHEN A.CTIPMVCODI IN (12,13,15,30) THEN ";
 									$sqlpost .= "            A.CMOVMACODT ";
									$sqlpost .= "       ELSE ";
 									$sqlpost .= "            NULL ";
									$sqlpost .= "       END AS CODMOVTRANSACAO ";
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
									$sqlpost .= "   AND A.CALMPOCODI = $Almoxarifado ";
									$sqlpost .= "   AND ( (E.CTIPMVCODI IN (12,13,15,30) AND A.FMOVMACORR = 'S') OR (E.CTIPMVCODI NOT IN (12,13,15,30) ) ) ";
									$sqlpost .= "   AND CASE WHEN A.CREQMASEQU IS NOT NULL AND A.CTIPMVCODI <> 2 THEN ";
									$sqlpost .= "                 TO_DATE(TO_CHAR(J.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') >= '".DataInvertida($DataIni)."' ";
									$sqlpost .= "                 AND TO_DATE(TO_CHAR(J.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') <= '".DataInvertida($DataFim)."' ";
									$sqlpost .= "            ELSE ";
									$sqlpost .= "	                A.DMOVMAMOVI >= '".DataInvertida($DataIni)."' ";
									$sqlpost .= "	                AND A.DMOVMAMOVI <= '".DataInvertida($DataFim)."' ";
									$sqlpost .= "            END ";
									$sqlpost .= "   AND (J.CTIPSRCODI IS NULL OR J.CTIPSRCODI = 5) ";
									$sqlpost .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
									$sqlpost .= " GROUP BY A.CALMPOCODI, H.CCENPOSEQU, DATAMOV, E.FTIPMVTIPO, E.ETIPMVDESC, ";
									$sqlpost .= "          A.CREQMASEQU, K.AMVCPMCONT,K.AMVCPMHIST, K.AMVCPMTPMC, K.FMVCPMDBCD, ";
									$sqlpost .= "          K.AMVCPMLOTE, A.CTIPMVCODI, G.FGRUMSTIPM, H.CCENPOCORG, H.CCENPOUNID, ";
									$sqlpost .= "          H.CCENPONRPA, H.CCENPOCENT, H.CCENPODETA, G.FGRUMSTIPC, DATATRANSACAO, ";
									$sqlpost .= "          CODMOVTRANSACAO ";
									$sqlpost .= " ORDER BY A.CALMPOCODI, H.CCENPOSEQU, DATAMOV, VALOR, FLAG, E.FTIPMVTIPO DESC, A.CTIPMVCODI ";
									
									$res  = $db->query($sqlpost);
									if(db::isError($res)){
											EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpost");
											$db->disconnect();
									}else{
											$Rows = $res->numRows();
											$Caixa = array();
											for($i=0;$i< $Rows; $i++){
													$Linha = $res->fetchRow();
													$Linha[4] = round($Linha[4],2);
													$Caixa[][0] = $Linha;
													list($dmovmamovi_ano,$dmovmamovi_mes,$dmovmamovi_dia) = explode("-",$Linha[0]);
										
													$sqloracle  = "SELECT SUM(VMVCALVALR), CMVCALREQU, CORGORCODI, CUNDORCODI ";
													$sqloracle .= "  FROM SFCT.TBMOVCONTABILALMOXARIFADO ";
													$sqloracle .= " WHERE CMVCALALMO = $Almoxarifado ";
													$sqloracle .= "   AND APLCTAANOC = $dmovmamovi_ano ";
													$sqloracle .= "   AND AMVCALMESM = $dmovmamovi_mes ";
													$sqloracle .= "   AND AMVCALDIAM = $dmovmamovi_dia ";
													$sqloracle .= "   AND AMVCALLOTE = ".$Linha[11]." ";
													$sqloracle .= "   AND CTIPMOCODI = ".$Linha[9]." ";
													$sqloracle .= "   AND AHMOVINUME = ".$Linha[8]." ";
													$sqloracle .= "   AND APLCTACONT = ".$Linha[7]." ";
													$sqloracle .= "   AND FMVCALDBCD = '".$Linha[10]."' ";
													$sqloracle .= "   AND EMVCALDESC = '".$Linha[6]."' ";
													if ($Linha[14]){
															$sqloracle .= " AND CMVCALREQU = $Linha[14]";
													}
													$sqloracle .= " GROUP BY APLCTAANOC, AMVCALMESM, AMVCALDIAM, AMVCALLOTE, CTIPMOCODI, ";
													$sqloracle .= "          AHMOVINUME, APLCTACONT, FMVCALDBCD, CORGORCODI, DEXERCANOR, ";
													$sqloracle .= "          CUNDORCODI, EMVCALDESC, CMVCALREQU, CMVCALALMO, DMVCALAMVA ";
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
													if ($Linha[21]){
															list($DataTransacao_ano,$DataTransacao_mes,$DataTransacao_dia) = explode("-",$Linha[21]);
												
															$sqloracle  = "SELECT SUM(VMVCALVALR), CMVCALREQU, CORGORCODI, CUNDORCODI ";
															$sqloracle .= "  FROM SFCT.TBMOVCONTABILALMOXARIFADO ";
															$sqloracle .= " WHERE APLCTAANOC = $DataTransacao_ano ";
															$sqloracle .= "   AND AMVCALMESM = $DataTransacao_mes ";
															$sqloracle .= "   AND AMVCALDIAM = $DataTransacao_dia ";
															$sqloracle .= "   AND AMVCALLOTE = ".$Linha[11]." ";
															$sqloracle .= "   AND CTIPMOCODI = ".$Linha[9]." ";
															$sqloracle .= "   AND AHMOVINUME = ".$Linha[8]." ";
															$sqloracle .= "   AND APLCTACONT = ".$Linha[7]." ";
															$sqloracle .= "   AND FMVCALDBCD = '".$Linha[10]."' ";
															$sqloracle .= "   AND EMVCALDESC = '".$Linha[6]."' ";
															$sqloracle .= "   AND CMVCALCODI = ".$Linha[22]." ";
															$sqloracle .= " GROUP BY APLCTAANOC, AMVCALMESM, AMVCALDIAM, AMVCALLOTE, CTIPMOCODI, ";
															$sqloracle .= "          AHMOVINUME, APLCTACONT, FMVCALDBCD, CORGORCODI, DEXERCANOR, ";
															$sqloracle .= "          CUNDORCODI, EMVCALDESC, CMVCALREQU, CMVCALALMO, DMVCALAMVA ";
															$resoracle = $dbora->query($sqloracle);
															if( db::isError($resoracle) ){
																	$dbora->disconnect();
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqloracle");
															}else{
																	$numCaixa = count($Caixa)-1;
																	$Caixa[$numCaixa][1] = array();
																	while ($roworacle = $resoracle->fetchRow()){
																			if (is_array($roworacle)){
																				$Caixa[$numCaixa][1][] = implode(" - ",$roworacle)."<br>\n";
																			}
																	}
															}
													}
											}
									}
									
									$sqlalmox = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
									$resalmox = $db->query($sqlalmox);
									if( db::isError($resalmox) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlalmox");
									}else{
											$rowalmox     = $resalmox->fetchRow();
											$AlmoxDesc    = $rowalmox[0];
											$EscreveAlmox = 1;
									}
									
									$dbora->disconnect;
									$db->disconnect();
									
									
									/***********************/
									/* IMPRESSÃO DOS DADOS */
									/***********************/
									
									echo "<tr>\n";
									echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"9\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
									echo "</tr>\n";
									echo "<tr>\n";
									echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"9\" class=\"titulo3\">".$AlmoxDesc."</td>\n";
									echo "</tr>\n";
									
									if ($Consist == 'I'){
											for ($Row=0;$Row<count($Caixa);$Row++){
												$Linha = $Caixa[$Row][0];
												$QtdOracle = count($Caixa[$Row][1]);
												if ($QtdOracle!=1){
													$CaixaInconsistente[][0] = $Linha;
												}
											}
											$Caixa = $CaixaInconsistente;
									}
									
									for ($Row=0;$Row<count($Caixa);$Row++){

											$Linha = $Caixa[$Row][0];
	
											if ($Centro <> $Linha[3]){
													$Centro = $Linha[3];
													switch ($Linha[20]){
															case 'C':
																	$Gasto = 3;
																	break;
															case 'P':
																	$Gasto = 27;
																	break;
															case 'L':
																	$Gasto = 37;
																	break;
															case 'D':
																	$Gasto = 6;
																	break;
															case 'F':
																	$Gasto = 30;
																	break;
													}
													$Url = "ConsMovimentacaoCompararCustoDetalhe.php?Orgao=$Linha[15]&Unidade=$Linha[16]&RPA=$Linha[17]&Centro=$Linha[18]&Deta=$Linha[19]&Gasto=$Gasto";
													if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
													echo "<tr>\n";
													echo "	<td align=\"center\" bgcolor=\"#DDECF9\" colspan=\"9\" class=\"titulo3\">";
													echo "		<a href=\"javascript:AbreJanela('$Url',600,320);\"><font color=\"#000000\">Org. $Linha[15] / Unid. $Linha[16] / RPA $Linha[17] / Centro Custo $Linha[18] / Func. de Gov. $Linha[19] / Item Gasto $Gasto</font></a>";
													echo "	</td>\n";
													echo "</tr>\n";
										
													echo "<tr bgcolor=\"cccccc\">";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" colspan=\"3\" align=\"center\">MOVIMENTAÇÃO SISTEMA ESTOQUE</td>";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" colspan=\"6\" align=\"center\">MOVIMENTAÇÃO SISTEMA CONTÁBIL</td>";
													echo "</tr>";
													echo "<tr bgcolor=\"dddddd\">";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">DATA</td>";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">MOVIMENTAÇÃO</td>";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">VALOR</td>";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">NÚMERO CONTA CONTÁBIL</td>";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">HISTÓRICO</td>";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">MOVIMENTO CONTÁBIL</td>";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">NATUREZA LANÇAMENTO</td>";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">NÚMERO LOTE CONTÁBIL</td>";
													echo "<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">VÁLIDO</td>";
													echo "</tr>";
											}
											echo "<tr>";
											
											if ($LinhaAnterior <> array($Linha[0],$Linha[4],$Linha[6],$Linha[21],$Linha[22]) ){
													$LinhaAnterior = array($Linha[0],$Linha[4],$Linha[6],$Linha[21],$Linha[22]);
													list($dmovmamovi_ano,$dmovmamovi_mes,$dmovmamovi_dia) = explode("-",$Linha[0]);
													$LinhaAltura = $MovQ[1][array_search(array($Linha[12],$Linha[13]),$MovQ[0])];
													echo "<td rowspan=\"$LinhaAltura\" class=\"textonormal\" bgcolor=\"#F7F7F7\">$dmovmamovi_dia/$dmovmamovi_mes/$dmovmamovi_ano</td>";
													if ($Linha[14]){
														echo "<td rowspan=\"$LinhaAltura\" class=\"textonormal\" bgcolor=\"#F7F7F7\">$Linha[6] - $Linha[14]</td>";
													} else {
														echo "<td rowspan=\"$LinhaAltura\" class=\"textonormal\" bgcolor=\"#F7F7F7\">$Linha[6]</td>";
													}
													echo "<td rowspan=\"$LinhaAltura\" align=right class=\"textonormal\" bgcolor=\"#F7F7F7\">".converte_valor($Linha[4])."</td>";
											}
											echo "<td align=right class=\"textonormal\" bgcolor=\"#F7F7F7\">".$Linha[7]."</td>";
										
											echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\">".$Linha[8]." - ".$Historico[1][array_search($Linha[8],$Historico[0])]."</td>";
											
											echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\">".$Linha[9]." - ".$MovContabil[1][array_search($Linha[9],$MovContabil[0])]."</td>";
												
											if ($Linha[10]=='D'){
												echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\">DÉBITO</td>";
											} elseif($Linha[10]=='C') {
												echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\">CRÉDITO</td>";
											}
											echo "<td align=right class=\"textonormal\" bgcolor=\"#F7F7F7\">".$Linha[11]."</td>";
											
											echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\">";
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
									if (is_null($Caixa) or count($Caixa)==0){
											echo "<tr>\n";
											echo "	<td class=\"textonormal\">\n";
											echo "		Pesquisa sem Ocorrências.\n";
											echo "	</td>\n";
											echo "</tr>\n";
									}
							}
							?>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
<script language="JavaScript">
	Aguarde.style.visibility='hidden';
</script>
</html>
