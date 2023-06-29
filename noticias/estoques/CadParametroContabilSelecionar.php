<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadParametroContabilSelecionar.php
# Autor:    Álvaro Faria
# Data:     28/12/2006
# Alterado: Álvaro Faria
# Data:     02/01/2007 - Order by no resultado da pesquisa de parâmetros cadastrados
# Alterado: Álvaro Faria
# Data:     24/01/2006 - Liberação para alteração da movimentação 25 e 28
# Alterado: Rossana Lira
# Data:     10/12/2007 - Colocar ordem de exibição por ano (Descendente)
# Objetivo: Programa de seleção de parâmetros para a Contabilidade para edição
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadParametroContabilManter.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao            = $_POST['Botao'];
		$TipoMovimentacao = $_POST['TipoMovimentacao'];
		$Movimentacao     = $_POST['Movimentacao'];
}else{
		$Mensagem         = urldecode($_GET['Mensagem']);
		$Mens             = $_GET['Mens'];
		$Tipo             = $_GET['Tipo'];
		$Troca            = $_GET['Troca'];
}

if(!$Troca) $Troca = 1;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadParametroContabilSelecionar.php";

if($Botao == "Limpar"){
		header("location: CadParametroContabilSelecionar.php");
		exit;
}elseif($Botao == "Pesquisar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if(!$TipoMovimentacao){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilSelecionar.TipoMovimentacao.focus();\" class=\"titulo2\">Tipo de Movimentação</a>";
		}
		if(!$Movimentacao){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadParametroContabilSelecionar.Movimentacao.focus();\" class=\"titulo2\">Movimentação</a>";
		}
}
?>

<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadParametroContabilSelecionar.Botao.value = valor;
	document.CadParametroContabilSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>

<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadParametroContabilSelecionar.php" method="post" name="CadParametroContabilSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="7">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Estoques > Contabilidade > Parâmetros > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="7"><?php ExibeMens($Mensagem,$Tipo,$Troca); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" colspan="7" class="titulo3">
									MANTER - PARÂMETROS
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="7">
									<p align="justify">
										Para manter um parâmetro, informe o tipo de movimento e clique no botão "Pequisar", depois selecione o parâmetro no resultado da pesquisa. Os itens obrigatórios estão com *.
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="7">
									<table class="textonormal" border="0" align="left" class="caixa">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Movimentação*</td>
											<td class="textonormal">
												<select name="TipoMovimentacao" class="textonormal" onChange="submit();">
													<option value="">Selecione o Tipo de Movimentação...</option>
													<option value="E" <?php if( $TipoMovimentacao == "E" ){ echo "selected"; }?>>ENTRADA</option>
													<option value="S" <?php if( $TipoMovimentacao == "S" ){ echo "selected"; }?>>SAÍDA</option>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Movimentação*</td>
											<td class="textonormal">
												<?php
												if($TipoMovimentacao){
														# Pega os tipos das movimentações #
														$db      = Conexao();
														$sqlMov  = "SELECT CTIPMVCODI, ETIPMVDESC FROM SFPC.TBTIPOMOVIMENTACAO ";
														$sqlMov .= " WHERE FTIPMVTIPO = '$TipoMovimentacao' AND CTIPMVCODI NOT IN(3,5,7,8,18,19,20,31)";
														$sqlMov .= " ORDER BY ETIPMVDESC ";
														$resMov  = $db->query($sqlMov);
														if( db::isError($resMov) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMov");
														}else{
																$rowsMov = $resMov->numRows();
																echo "<select name=\"Movimentacao\" class=\"textonormal\">\n";
																echo "	<option value=\"\">Selecione uma Movimentacao...</option>\n";
																for( $i=0;$i< $rowsMov; $i++ ){
																		$LinhaMov = $resMov->fetchRow();
																		if( $Movimentacao == $LinhaMov[0] ){
																				echo "<option value=\"$LinhaMov[0]\" selected>$LinhaMov[1]</option>";
																		}else{
																				echo "<option value=\"$LinhaMov[0]\">$LinhaMov[1]</option>";
																		}
																}
																echo "</select>";
														}
														$db->disconnect();
												}else{
														echo "<select name=\"Movimentacao\" class=\"textonormal\">";
														echo "	<option value=\"\">Selecione acima o Tipo de Movimentação...</option>";
														echo "</select>";
												}
												?>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="7" align="right">
									<input type="hidden" name="Botao">
									<input type="button" name="Manter" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar');">
									<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar');">
								</td>
							</tr>
							<?php
							if($Botao == "Pesquisar" and $Mens == 0){
									$db       = Conexao();
									$dbora    = ConexaoOracle();
									# Verifica os dados que estão no banco #
									$sqlPesq  = "SELECT AMVCPMANOC, AMVCPMCONT, AMVCPMHIST, AMVCPMTPMC, FMVCPMDBCD, AMVCPMLOTE, FMVCPMTIPM, ";
									/*
									* Fausto Feitosa - 22/11/2007 - Início
									*/
									$sqlPesq .= "       CMVCPMELE1, CMVCPMELE2, CMVCPMELE3, CMVCPMELE4, CMVCPMSUBE, NMVCPMNOMS ";
									# Fausto Feitosa - Fim
									$sqlPesq .= "  FROM SFPC.TBMOVCONTABILALMOXARIFADOPARAM ";
									$sqlPesq .= " WHERE CTIPMVCODI = $Movimentacao ";
			            # { Fausto Feitosa - 27/11/2007 - Alterada a ordem de exibição, que agora começa pelo ano em ordem ascendente.
									$sqlPesq .= " ORDER BY AMVCPMANOC DESC, FMVCPMTIPM, AMVCPMCONT ASC, AMVCPMHIST, AMVCPMTPMC, FMVCPMDBCD, AMVCPMLOTE ";
									# }
									$resPesq  = $db->query($sqlPesq);
									if( db::isError($resPesq) ){
											$db->disconnect();
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlPesq");
									}else{
											$qtdPesq = $resPesq->numrows();
											# Cabeçalho da tabela #
											echo "<tr>\n";
											echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"7\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
											echo "</tr>\n";
											if($qtdPesq > 0){
													echo "<tr>\n";
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"6%\" align=\"CENTER\">ANO</td>\n";
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"15%\" align=\"CENTER\">NÚMERO</td>\n";
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\" align=\"CENTER\">HISTÓRICO</td>\n";
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"16%\" align=\"CENTER\">TIPO MOV. CONTABIL</td>\n";
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"14%\" align=\"center\">NATUREZA</td>\n";
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">LOTE</td>\n";
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"15%\" align=\"center\">TIPO MATERIAL</td>\n";
													echo "</tr>\n";
													while($rowPesq = $resPesq->fetchRow()){
															$AnoConta     = $rowPesq[0];
															$NumeroConta  = $rowPesq[1];
															$Historico    = $rowPesq[2];
															$TipoMovCont  = $rowPesq[3];
															$Natureza     = $rowPesq[4];
															$Lote         = $rowPesq[5];
															$TipoMaterial = $rowPesq[6];
															$SubElemento  = urlencode($rowPesq[7]."!".$rowPesq[8]."!".$rowPesq[9]."!".$rowPesq[10]."!".$rowPesq[11]."!".$rowPesq[12]);
															# Pega a descrição do Número de Conta no Oracle #
															$sqlConta  = "SELECT NPLCTACONT ";
															$sqlConta .= "  FROM SFCT.TBPLANOCONTAS ";
															$sqlConta .= " WHERE APLCTAANOC = $AnoConta ";
															$sqlConta .= "   AND APLCTACONT = $NumeroConta ";
															$resConta  = $dbora->query($sqlConta);
															if( db::isError($resConta) ){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlConta");
															}else{
																	$LinhaConta = $resConta->fetchRow();
																	$DescConta = $LinhaConta[0];
																	# Pega a descrição do Histórico no Oracle #
																	$sqlHist  = "SELECT XTEXTCCONT ";
																	$sqlHist .= "  FROM SFCT.TBTEXTOCONTABIL ";
																	$sqlHist .= " WHERE ATEXTCNUME = $Historico ";
																	$resHist  = $dbora->query($sqlHist);
																	if( db::isError($resHist) ){
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
																	}else{
																			$LinhaHist = $resHist->fetchRow();
																			$DescHist  = $LinhaHist[0];
																			# Pega a descrição do Tipo Movimento Contábil no Oracle #
																			$sqlMovC  = "SELECT NTIPMOTABE ";
																			$sqlMovC .= "  FROM SFCT.TBTIPOMOVIMENTOCONTABIL ";
																			$sqlMovC .= " WHERE CTIPMOCODI = $TipoMovCont ";
																			$resMovC  = $dbora->query($sqlMovC);
																			if( db::isError($resMovC) ){
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovC");
																			}else{
																					$LinhaMovC = $resMovC->fetchRow();
																					$DescMovC  = $LinhaMovC[0];
																					if($Natureza == 'D')         $DescNatureza     = "DÉBITO";
																					elseif($Natureza == 'C')     $DescNatureza     = "CRÉDITO";
																					if($TipoMaterial == 'P')     $DescTipoMaterial = "PERMANENTE";
																					elseif($TipoMaterial == 'C') $DescTipoMaterial = "CONSUMO";
																					# Exibe os parâmetros cadastrados de acordo com o critério de pesquisa e o link para alterá-los #
																					$Url = "CadParametroContabilManter.php?TipoMovimentacao=$TipoMovimentacao&Movimentacao=$Movimentacao&TipoMaterial=$TipoMaterial&AnoConta=$AnoConta&NumeroConta=$NumeroConta&Historico=$Historico&TipoMovCont=$TipoMovCont&Natureza=$Natureza&Lote=$Lote&SubElemento=$SubElemento";
																					if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																					echo "<tr>\n";
																					echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">\n";
																					echo "		<a href=\"$Url\"><font color=\"#000000\">";
																					echo "			$AnoConta";
																					echo "		</font></a>";
																					echo "	</td>\n";
																					echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">\n";
																					echo "		<a href=\"$Url\"><font color=\"#000000\">";
																					echo "			$NumeroConta - $DescConta";
																					echo "		</font></a>";
																					echo "	</td>\n";
																					echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">\n";
																					echo "		<a href=\"$Url\"><font color=\"#000000\">";
																					echo "			$Historico - $DescHist";
																					echo "		</font></a>";
																					echo "	</td>\n";
																					echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">\n";
																					echo "		<a href=\"$Url\"><font color=\"#000000\">";
																					echo "			$TipoMovCont - $DescMovC";
																					echo "		</font></a>";
																					echo "	</td>\n";
																					echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">\n";
																					echo "		<a href=\"$Url\"><font color=\"#000000\">";
																					echo "			$DescNatureza";
																					echo "		</font></a>";
																					echo "	</td>\n";
																					echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">\n";
																					echo "		<a href=\"$Url\"><font color=\"#000000\">";
																					echo "			$Lote";
																					echo "		</font></a>";
																					echo "	</td>\n";
																					echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">\n";
																					echo "		<a href=\"$Url\"><font color=\"#000000\">";
																					echo "			$DescTipoMaterial";
																					echo "		</font></a>";
																					echo "	</td>\n";
																					echo "</tr>\n";
																			}
																	}
															}
													}
											}else{
													echo "<tr>\n";
													echo "	<td class=\"textonormal\" colspan=\"7\" >\n";
													echo "		Pesquisa sem Ocorrências.\n";
													echo "	</td>\n";
													echo "</tr>\n";
											}
									}
									$db->disconnect();
									$dbora->disconnect();
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
