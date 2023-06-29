<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCustoComparar.php
# Autor:    Álvaro Faria
# Data:     25/09/2006
# Objetivo: Exibir as diferenças dos Custos gerados entre o Postgree e o Oracle
#           entre o Oracle e Postgre
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
		$TipoMaterial        = $_POST['TipoMaterial'];
		//$Direcao      = $_POST['Direcao'];
}
/*else{
		$Mens                = $_GET['Mens'];
		$Tipo                = $_GET['Tipo'];
		$Mensagem            = urldecode($_GET['Mensagem']);
}*/

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: TabCustoComparar.php" );
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
				$Mensagem .= "<a href=\"javascript:document.RelSinteticoEntradasSaidas.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"TabCustoComparar");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }
		
		/*if(!$Direcao){
				if($Mens == 1){ $Mensagem.=", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.TabCustoComparar.Direcao.focus();\" class=\"titulo2\">Direção</a>";
		}*/
}
?>

<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.TabCustoComparar.Botao.value=valor;
	document.TabCustoComparar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabCustoComparar.php" method="post" name="TabCustoComparar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="8">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Estoques > Centro de Custo > Comparar
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1) {?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="8"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
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
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="8">
									COMPARAÇÃO DE MOVIMENTAÇÕES SISTEMA ESTOQUE E SISTEMA CUSTO
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="8">
									<p align="justify">
										Para comparar os Custos gerados entre os sistemas, selecione o Almoxarifado, ou todos, o Período e clique no botão "Comparar".
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="8" >
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db = Conexao();
												if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if($Almoxarifado){
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
												if(PEAR::isError($res)){
														EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if($Rows == 1){
																$Linha = $res->fetchRow();
																$Almoxarifado = $Linha[0];
																echo "$Linha[1]<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																echo $DescAlmoxarifado;
														}elseif( $Rows > 1 ){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																echo "	<option value=\"T\">TODOS</option>\n";
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
												if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
												if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=TabCustoComparar&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=TabCustoComparar&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="25%">Tipo do Material</td>
											<td class="textonormal">
												<select name="TipoMaterial" class="textonormal">
													<?php
													if($TipoMaterial == "T" or !$TipoMaterial){
															echo "<option value=\"T\" selected>Todos</option>";
															echo "<option value=\"C\">Consumo</option>";
															echo "<option value=\"P\">Permanente</option>";
													}elseif($TipoMaterial == "C"){
															echo "<option value=\"T\">Todos</option>";
															echo "<option value=\"C\" selected>Consumo</option>";
															echo "<option value=\"P\">Permanente</option>";
													}elseif($TipoMaterial == "P"){
															echo "<option value=\"T\">Todos</option>";
															echo "<option value=\"C\">Consumo</option>";
															echo "<option value=\"P\" selected>Permanente</option>";
													}
													?>
												</select>
											</td>
										</tr>
										<?php
										/*
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Direção*</td>
											<td class="textonormal">
												<select name="Direcao" class="textonormal">
													<option value="">Selecione uma Direção</option>
													<?php
													if($Direcao == "PO") echo "<option value=\"PO\" selected>POSTGRE --> ORACLE</option>"; else echo "<option value=\"PO\">POSTGRE --> ORACLE</option>";
													if($Direcao == "OP") echo "<option value=\"OP\" selected>ORACLE --> POSTGRE</option>"; else echo "<option value=\"OP\">ORACLE --> POSTGRE</option>";
													?>
												</select>
											</td>
										</tr>
										*/
										?>
										
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right" colspan="8">
									<input type="button" value="Comparar" class="botao" onclick="javascript:enviar('Comparar');">
									<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
									<input type="hidden" name="Botao" value="">
								</td>
							</tr>
							<?php
							if($Botao == "Comparar" and $Mens == 0){
									# Conecta com os bancos de dados #
									$db    = Conexao();
									$dbora = ConexaoOracle();
									# Busca somatório das movimentações por dia no período especificado no Postgree #
									# Busca somatório das movimentações por dia no período especificado no Postgree #
									$sqlpost .= " SELECT CASE WHEN MOV.CREQMASEQU IS NOT NULL THEN to_date(to_char(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') ELSE MOV.DMOVMAMOVI END AS DATAMOV, SUM(MOV.AMOVMAQTDM*MOV.VMOVMAVALO) AS VALOR ";
									$sqlpost .= "   FROM SFPC.TBMATERIALPORTAL MAT, ";
									$sqlpost .= "        SFPC.TBTIPOMOVIMENTACAO TIP, ";
									$sqlpost .= "        SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBGRUPOMATERIALSERVICO GRU, ";
									$sqlpost .= "        SFPC.TBMOVIMENTACAOMATERIAL MOV ";
									$sqlpost .= "   LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL REQ ";
									$sqlpost .= "     ON MOV.CREQMASEQU = REQ.CREQMASEQU ";
									# Busca pela situação da requisição, se esta movimentação tiver haver com requisição #
									$sqlpost .= "   LEFT OUTER JOIN SFPC.TBSITUACAOREQUISICAO SIT ";
									$sqlpost .= "     ON MOV.CREQMASEQU = SIT.CREQMASEQU ";
									$sqlpost .= "    AND TSITREULAT IN ";
									$sqlpost .= "                   (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO ";
									$sqlpost .= "                     WHERE CREQMASEQU = SIT.CREQMASEQU) ";
									$sqlpost .= "   LEFT OUTER JOIN SFPC.TBCENTROCUSTOPORTAL CEN ";
									$sqlpost .= "     ON REQ.CCENPOSEQU = CEN.CCENPOSEQU ";
									if($Almoxarifado != 'T') $sqlpost .= "  WHERE MOV.CALMPOCODI = $Almoxarifado ";
									if($TipoMovimentacao) $sqlpost .= " AND TIP.FTIPMVTIPO = '$TipoMovimentacao' ";
									if($TipoMaterial != 'T'){
											$sqlpost .= "  AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
									}
									# Não traz movimentações que não geram custo #
									$sqlpost .= "  AND   TIP.CTIPMVCODI NOT IN(0,1,3,5,7,8,18,31) ";
									# Dependendo do tipo da movimentação, sendo uma movimentação entre almoxarifados, #
									# deve requerer que esta já esteja concluída, pois só elas geram custo #
									$sqlpost .= "  AND ( (TIP.CTIPMVCODI IN(12,13,15,30) AND MOV.FMOVMACORR = 'S') OR ";
									$sqlpost .= "        (TIP.CTIPMVCODI NOT IN(12,13,15,30) ) ) ";
									$sqlpost .= "  AND ";
									$sqlpost .= "  CASE WHEN MOV.CREQMASEQU IS NOT NULL THEN ";
									$sqlpost .= "       to_date(to_char(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') >= '".DataInvertida($DataIni)."' ";
									$sqlpost .= "       AND to_date(to_char(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') <= '".DataInvertida($DataFim)."' ";
									$sqlpost .= "  ELSE MOV.DMOVMAMOVI >= '".DataInvertida($DataIni)."' ";
									$sqlpost .= "       AND MOV.DMOVMAMOVI <= '".DataInvertida($DataFim)."' ";
									$sqlpost .= "  END ";
									$sqlpost .= "  AND TIP.FTIPMVTIPO = 'S' ";
									$sqlpost .= "  AND MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
									$sqlpost .= "  AND MAT.CMATEPSEQU = MOV.CMATEPSEQU ";
									$sqlpost .= "  AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
									$sqlpost .= "  AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
									$sqlpost .= "  AND (SIT.CTIPSRCODI IS NULL OR SIT.CTIPSRCODI = 5) "; # Traz apenas requisições baixadas ou movimentações que não sejam requisição
									$sqlpost .= "  AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') "; // Apresentar só as movimentações ativas
									$sqlpost .= "GROUP BY DATAMOV ";
									$sqlpost .= "ORDER BY DATAMOV ";
									$respost = $db->query($sqlpost);
									if( PEAR::isError($respost) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpost");
									}else{
											$numresp = $respost->numRows();
											if($numresp > 0){
													while($rowpost = $respost->fetchRow()){
															$Data      = $rowpost[0];
															$Movpost   = $rowpost[1];
															$DataArray = explode("-",$Data);
															$Dia = $DataArray[2];
															$Mes = $DataArray[1];
															$Ano = $DataArray[0];
															# Busca somatório das movimentações da data especificada do loop no Oracle #
															$sqloracle  = "SELECT SUM(VMOVCUREQU) FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
															$sqloracle .= " WHERE DEXERCANOR = $Ano AND AMOVCUMESM = $Mes AND AMOVCUDIAM = $Dia ";
															$sqloracle .= "   AND CCENCPCODI = 799 AND CDETCPCODI = 77 ";
															if($Almoxarifado != 'T') $sqloracle .= " AND CMOVCUALMO = $Almoxarifado ";
															if($TipoMovimentacao) $sqloracle .= " AND FMOVCULANC = '$TipoMovimentacao' ";
															if($TipoMaterial == 'C'){
																	$sqloracle .= " AND CESPCPCODI = 3 ";
															}elseif($TipoMaterial == 'P'){
																	$sqloracle .= " AND CESPCPCODI = 27 ";
															}
															$resoracle = $dbora->query($sqloracle);
															if( PEAR::isError($resoracle) ){
																	$dbora->disconnect();
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqloracle");
															}else{
																	$roworacle = $resoracle->fetchRow();
																	$Movoracle = $roworacle[1];
																	echo "$Dia/$Mes/$Ano - ".converte_valor($Movpost)." - ".converte_valor($Movoracle)."<BR>";
															}
													}
											}else{
													echo "<tr>\n";
													echo "	<td class=\"textonormal\" colspan=\"8\">\n";
													echo "		Pesquisa sem Ocorrências.\n";
													echo "	</td>\n";
													echo "</tr>\n";
											}
											$db->disconnect;
											$dbora->disconnect;
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
</html>
