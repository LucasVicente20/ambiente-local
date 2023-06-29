<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCentroCustoComparar.php
# Autor:    Álvaro Faria
# Data:     13/09/2006
# Objetivo: Exibir as diferenças da estrutura de Centro de Custo
#           entre o Oracle e Postgre
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

$Ano = date("Y");

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao    = $_POST['Botao'];
		$OrgUnid  = $_POST['OrgUnid'];
		$Direcao  = $_POST['Direcao'];
}else{
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
		$Mensagem = urldecode($_GET['Mensagem']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: TabCentroCustoComparar.php" );
		exit;
}elseif($Botao == "Comparar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if(!$OrgUnid){
				if($Mens == 1){ $Mensagem.=", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.TabCentroCustoComparar.OrgUnid.focus();\" class=\"titulo2\">Unidade Orçamentaria</a>";
		}
		if(!$Direcao){
				if($Mens == 1){ $Mensagem.=", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.TabCentroCustoComparar.Direcao.focus();\" class=\"titulo2\">Direção</a>";
		}
		if($Mens == 0){
				# Conecta com os bancos de dados #
				$db    = Conexao();
				$dbora = ConexaoOracle();
				if($Direcao == "OP"){
						# Inicia o array que guardará os C/C inexistentes #
						$InexistenteArray = array();
						# Resgata os dados os Centros de Custo no Oracle #
						$sqlO  = "SELECT DISTINCT A.DEXERCANOR, A.CORGORCODI, A.CRPAAACODI, A.CUNDORCODI, ";
						$sqlO .= "       A.CCENCPCODI, A.CDETCPCODI, B.ECENCPDESC, C.EDETCPDESC";
						$sqlO .= "  FROM SFCP.TBESTRUTURACUSTO A, SFCP.TBCENTROCUSTOPUBLICO B, SFCP.TBDETALHAMENTOCUSTO C";
						$sqlO .= " WHERE A.CDETCPCODI = C.CDETCPCODI AND A.CCENCPCODI = B.CCENCPCODI ";
						$sqlO .= "   AND A.DEXERCANOR = $Ano ";
						if($OrgUnid != "T"){
								$OrgUnidArray = explode("_",$OrgUnid);
								$Org = $OrgUnidArray[0];
								$Uni = $OrgUnidArray[1];
								$sqlO .= "   AND A.CORGORCODI = ".$Org." AND A.CUNDORCODI = ".$Uni." ";
						}
						$sqlO .= " ORDER BY A.DEXERCANOR, A.CORGORCODI, A.CRPAAACODI, A.CUNDORCODI, A.CCENCPCODI, A.CDETCPCODI ";
						$resO  = $dbora->query($sqlO);
						if( PEAR::isError($resO) ){
								$db->disconnect;
								$dbora->disconnect;
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlO");
						}else{
								while($LinhaO = $resO->fetchRow()){
										$cont++;
										$Exercicio        = $LinhaO[0];
										$Orgao            = $LinhaO[1];
										$RPA              = $LinhaO[2];
										$Unidade          = $LinhaO[3];
										$CentroCusto      = $LinhaO[4];
										$Detalhamento     = $LinhaO[5];
										$Descricao        = str_replace("'","",$LinhaO[6]);
										$DescDetalhamento = str_replace("'","",$LinhaO[7]);
										# Procura este Centro de Custo no PostGre #
										$sqlP  = "SELECT COUNT(*) FROM SFPC.TBCENTROCUSTOPORTAL ";
										$sqlP .= " WHERE ACENPOANOE = $Exercicio AND CCENPOCORG = $Orgao ";
										$sqlP .= "   AND CCENPONRPA = $RPA AND CCENPOUNID = $Unidade ";
										$sqlP .= "   AND CCENPOCENT = $CentroCusto AND CCENPODETA = $Detalhamento";
										$sqlP .= "   AND (FCENPOSITU IS NULL OR FCENPOSITU = 'A') ";
										$resP  = $db->query($sqlP);
										if( PEAR::isError($resP) ){
												$db->disconnect;
												$dbora->disconnect;
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlP");
										}else{
												$Qtd = $resP->fetchRow();
												if($Qtd[0] == 0){
														$InexistenteArray[] = $Exercicio."æ".$Orgao."æ".$RPA."æ".$Unidade."æ".$CentroCusto."æ".$Detalhamento."æ".$Descricao."æ".$DescDetalhamento;
												}
										}
								}
						}
				}elseif($Direcao == "PO"){
						# Inicia o array que guardará os C/C inexistentes #
						$InexistenteArray = array();
						# Resgata os dados os Centros de Custo no Postgre #
						$sqlP  = "SELECT ACENPOANOE, CCENPOCORG, CCENPONRPA, CCENPOUNID, ";
						$sqlP .= "       CCENPOCENT, CCENPODETA, ECENPODESC, ECENPODETA";
						$sqlP .= "  FROM SFPC.TBCENTROCUSTOPORTAL ";
						$sqlP .= " WHERE (FCENPOSITU IS NULL OR FCENPOSITU = 'A') ";
						$sqlP .= "   AND ACENPOANOE = $Ano ";
						if($OrgUnid != "T"){
								$OrgUnidArray = explode("_",$OrgUnid);
								$Org = $OrgUnidArray[0];
								$Uni = $OrgUnidArray[1];
								$sqlP .= " AND CCENPOCORG = ".$Org." AND CCENPOUNID = ".$Uni." ";
						}
						$sqlP .= " ORDER BY ACENPOANOE, CCENPOCORG, CCENPONRPA, CCENPOUNID, CCENPOCENT, CCENPODETA ";
						$resP  = $db->query($sqlP);
						if( PEAR::isError($resP) ){
								$db->disconnect;
								$dbora->disconnect;
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlP");
						}else{
								while($LinhaP = $resP->fetchRow()){
										$cont++;
										$Exercicio        = $LinhaP[0];
										$Orgao            = $LinhaP[1];
										$RPA              = $LinhaP[2];
										$Unidade          = $LinhaP[3];
										$CentroCusto      = $LinhaP[4];
										$Detalhamento     = $LinhaP[5];
										$Descricao        = str_replace("'","",$LinhaP[6]);
										$DescDetalhamento = str_replace("'","",$LinhaP[7]);
										# Procura este Centro de Custo no Oracle #
										$sqlO  = "SELECT COUNT(*) ";
										$sqlO .= "  FROM SFCP.TBESTRUTURACUSTO A, SFCP.TBCENTROCUSTOPUBLICO B, SFCP.TBDETALHAMENTOCUSTO C";
										$sqlO .= " WHERE A.CCENCPCODI = B.CCENCPCODI ";
										$sqlO .= "   AND A.CDETCPCODI = C.CDETCPCODI ";
										$sqlO .= "   AND A.DEXERCANOR = $Exercicio AND A.CORGORCODI = $Orgao ";
										$sqlO .= "   AND A.CRPAAACODI = $RPA AND A.CUNDORCODI = $Unidade ";
										$sqlO .= "   AND A.CCENCPCODI = $CentroCusto AND A.CDETCPCODI = $Detalhamento";
										$resO  = $dbora->query($sqlO);
										if( PEAR::isError($resO) ){
												$db->disconnect;
												$dbora->disconnect;
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlO");
										}else{
												$Qtd = $resO->fetchRow();
												if($Qtd[0] == 0){
														$InexistenteArray[] = $Exercicio."æ".$Orgao."æ".$RPA."æ".$Unidade."æ".$CentroCusto."æ".$Detalhamento."æ".$Descricao."æ".$DescDetalhamento;
												}
										}
								}
						}
				}
				$db->disconnect;
				$dbora->disconnect;
		}
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
	document.TabCentroCustoComparar.Botao.value=valor;
	document.TabCentroCustoComparar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabCentroCustoComparar.php" method="post" name="TabCentroCustoComparar">
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
									COMPARAÇÃO DE CENTROS DE CUSTO NOS BANCOS
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="8">
									<p align="justify">
										Para comparar a tabela de Centro de Custo do Portal de Compras a partir do SOFIN, selecione a Unidade Orçamentária, ou todas, e clique no botão "Comparar".
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="8" >
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade Orçamentária*</td>
											<td class="textonormal">
												<select name="OrgUnid" class="textonormal">
													<option value="">Selecione uma Unidade Orçamentária</option>
													<?php
													if($OrgUnid == T){
															echo "<option value=\"T\" selected>TODAS</option>";
													}else{
															echo "<option value=\"T\">TODAS</option>";
													}
													$db     = Conexao();
													# Mostra as Unidades Orçamentárias #
													$sql    = "SELECT CUNIDOORGA, CUNIDOCODI, EUNIDODESC ";
													$sql   .= "  FROM SFPC.TBUNIDADEORCAMENTPORTAL WHERE TUNIDOEXER = $Ano";
													$sql   .= " ORDER BY EUNIDODESC";
													$result = $db->query($sql);
													if (PEAR::isError($result)) {
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
																	$Orgao       = $Linha[0];
																	$Unidade     = $Linha[1];
																	$DescUnidade = $Linha[2];
																	if($OrgUnid == $Orgao."_".$Unidade){
																			echo "<option value=\"".$Orgao."_".$Unidade."\" selected>".substr($DescUnidade,0,55)."</option>\n";
																	}else{
																			echo "<option value=\"".$Orgao."_".$Unidade."\">".substr($DescUnidade,0,55)."</option>\n";
																	}
															}
													}
													$db->disconnect();
													?>
												</select>
											</td>
										</tr>
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
									echo "<tr>\n";
									echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"8\" class=\"titulo3\">DIFERENÇAS ENCONTRADAS</td>\n";
									echo "</tr>\n";
									if($InexistenteArray){
											echo "<tr>\n";
											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"8%\" align=\"center\">ANO</td>\n";
											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"8%\" align=\"center\">ÓRG</td>\n";
											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"8%\" align=\"center\">UNID</td>\n";
											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"8%\" align=\"center\">RPA</td>\n";
											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"8%\" align=\"center\">C/C</td>\n";
											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"8%\" align=\"center\">DET</td>\n";
											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"52%\" align=\"center\">DESCRIÇÃO</td>\n";
											echo "</tr>\n";
											foreach($InexistenteArray as $Inexistente){
													$InexistenteColunas = explode("æ",$Inexistente);
													echo "<tr>\n";
													echo "	<td class=\"textonormal\" align=\"right\">\n";
													echo "		$InexistenteColunas[0]";
													echo "	</td>\n";
													echo "	<td class=\"textonormal\" align=\"right\">\n";
													echo "		$InexistenteColunas[1]";
													echo "	</td>\n";
													echo "	<td class=\"textonormal\" align=\"right\">\n";
													echo "		$InexistenteColunas[3]";
													echo "	</td>\n";
													echo "	<td class=\"textonormal\" align=\"right\">\n";
													echo "		$InexistenteColunas[2]";
													echo "	</td>\n";
													echo "	<td class=\"textonormal\" align=\"right\">\n";
													echo "		$InexistenteColunas[4]";
													echo "	</td>\n";
													echo "	<td class=\"textonormal\" align=\"right\">\n";
													echo "		$InexistenteColunas[5]";
													echo "	</td>\n";
													echo "	<td class=\"textonormal\">\n";
													echo "		$InexistenteColunas[6] - $InexistenteColunas[7]";
													echo "	</td>\n";
													echo "</tr>\n";
											}
									}else{
											echo "<tr>\n";
											echo "	<td class=\"textonormal\" colspan=\"8\">\n";
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
</html>
