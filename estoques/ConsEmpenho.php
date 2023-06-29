<?php
# -----------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsEmpenho.php
# Objetivo: Mostra um empenho selecionado, informando se ele é sub-empenho e quantas parceles ele possui.
# Autor:    Ariston Cordeiro
# Data:     06/08/2008
# -----------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     11/09/2008
# Objetivo: Correção em empenhos que davam erro de banco
# -----------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     12/05/2009
# Objetivo: Correção em empenhos que são do tipo que deveriam ter subempenhos mas não tem nenhum.
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     22/11/2018
# Objetivo: Tarefa Redmine 119262
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/ConsEmpenho.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao    		   = $_POST['Botao'];
	$AnoEmpenho        = $_POST['AnoEmpenho'];
	$OrgaoEmpenho      = $_POST['OrgaoEmpenho'];
	$UnidadeEmpenho    = $_POST['UnidadeEmpenho'];
	$SequencialEmpenho = $_POST['SequencialEmpenho'];
	$ParcelaEmpenho    = $_POST['ParcelaEmpenho'];
}
$Mensagem = "";
$Mens     = 0;
$Tipo     = 0;

if ($Botao=="Pesquisar") {
	if (is_null($AnoEmpenho) || ($AnoEmpenho=="")) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.AnoEmpenho.focus();\" class=\"titulo2\">Ano do Empenho</a>";
	}
	
	if (is_null($OrgaoEmpenho) || ($OrgaoEmpenho=="")) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.OrgaoEmpenho.focus();\" class=\"titulo2\">Órgão do Empenho</a>";
	}
	
	if (is_null($UnidadeEmpenho) || ($UnidadeEmpenho=="")) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.UnidadeEmpenho.focus();\" class=\"titulo2\">Unidade do Empenho</a>";
	}
	
	if (is_null($SequencialEmpenho) || ($SequencialEmpenho=="")) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadIncluirEmpenho.SequencialEmpenho.focus();\" class=\"titulo2\">Seqüencial do Empenho</a>";
	}

	if ($Mens==0) {
		$dbora = ConexaoOracle();

		$sql ="SELECT	TIP.FTPEMPSUEM, EMP.DEMPENANOO, EMP.CORGORCODI, EMP.CUNDORCODI, EMP.AEMPENSEQU,
						TO_CHAR(EMP.DEMPENEMIS,'YYYY-MM-DD HH24:MI:SS'), EMP.VEMPENEMPE, EMP.VEMPENANUL, EMP.VEMPENPAGO,
						(NVL(EMP.VEMPENEMPE,0) - (NVL(EMP.VEMPENANUL,0) + NVL(EMP.VEMPENPAGO,0)))
				FROM	SFCO.TBTIPOEMPENHO TIP, SFCO.TBEMPENHO EMP
				WHERE	EMP.DEMPENANOO = $AnoEmpenho AND
						EMP.CORGORCODI = $OrgaoEmpenho AND
						EMP.CUNDORCODI = $UnidadeEmpenho AND
						EMP.AEMPENSEQU = $SequencialEmpenho AND
						EMP.CTPEMPCODI = TIP.CTPEMPCODI
				ORDER BY EMP.DEMPENANOO, EMP.CORGORCODI, EMP.CUNDORCODI, EMP.AEMPENSEQU ";

		$res = $dbora->query($sql);

		if (PEAR::isError($res)) {
			$dbora->disconnect();
			EmailErroSQL(__FILE__."- Erro ao consultar um empenho", __FILE__, __LINE__, "Falha no SQL.", $sql, $res);
			exit(0);
		}
	}
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
		document.ConsultarEmpenho.Botao.value = valor;
		document.ConsultarEmpenho.submit();
	}
	function AbreJanela(url,largura,altura) {
		window.open(url,'pagina','status=no,scrollbars=yes,left=60,top=150,width='+largura+',height='+altura);
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="ConsEmpenho.php" method="post" name="ConsultarEmpenho">
		<br/><br/><br/><br/><br/>
		<table cellpadding="3" border="0" width="100%" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Nota Fiscal > Consultar Empenho
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" colspan="1">
					<?php if ($Mens != 0) { ExibeMens($Mensagem,$Tipo,1); }?>
				</td>
			</tr>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
									<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="1">
												CONSULTAR EMPENHO
											</td>
										</tr>
										<tr>
											<td class="textonormal" colspan="1">
												<p align="justify">
													Para ver um Empenho, preencha os campos Ano, Órgão, Unidade, Sequêncial e, se houver, Parcela.
													Depois, clique no botão "Pesquisar".<br/>
													<b>Observação:</b> Valores válidos apenas para empenhos de ano 2007 em diante.
												</p>
											</td>
										</tr>
										<tr>
											<td colspan="1">
												<table border="0" width="100%" summary="">
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Ano*</td>
														<td class="textonormal" height="20">
															<input type=text name="AnoEmpenho" class="textonormal" value="<?php echo $AnoEmpenho; ?>" size="4" maxlength="4">
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Órgão*</td>
														<td class="textonormal" height="20">
															<input type=text name="OrgaoEmpenho" class="textonormal" value="<?php echo $OrgaoEmpenho; ?>" size="2" maxlength="2">
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Unidade*</td>
														<td class="textonormal" height="20">
															<input type=text name="UnidadeEmpenho" class="textonormal" value="<?php echo $UnidadeEmpenho; ?>" size="2" maxlength="2">
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Sequencial*</td>
														<td class="textonormal" height="20">
															<input type=text name="SequencialEmpenho" class="textonormal" value="<?php echo $SequencialEmpenho; ?>" size="5" maxlength="5">
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Parcela</td>
														<td class="textonormal" height="20">
															<input type=text name="ParcelaEmpenho" class="textonormal" value="<?php echo $ParcelaEmpenho; ?>" size="3" maxlength="3">
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td colspan="4" align="right">
												<input type="button" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
												<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('');">
												<input type="hidden" name="Botao" value="">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<?php
						if (($Botao=="Pesquisar") && ($Mens == 0)) {
							$Linha = $res->fetchRow();
						?>
						<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="10">
							 		RESULTADO DA PESQUISA
								</td>
							</tr>
							<?php
							if (($Linha[1]=="") || (is_null($Linha[1]))) {
							?>
							<tr>
								<td>Nenhum Empenho ou Subempenho encontrado.</td>
							</tr>
							<?php
							} else {
							?>
								<tr>
									<td align="center" bgcolor="#F7F7F7" valign="middle" class="text" colspan="10">
								 		<b>Empenho</b>
									</td>
								</tr>
								<tr>
									<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Ano</td>
									<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Órgão</td>
									<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Unidade</td>
									<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Sequencial</td>
									<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Data de emissão</td>
									<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Valor de empenho</td>
									<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Valor anulado</td>
									<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Valor pago</td>
									<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Movimentações de Nota Fiscal</td>
									<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Valor disponível</td>
								</tr>
								<?php
								$cnt = 0;
								
								while ((!is_null($Linha)) && ($cnt<10000)) {
									$cnt++;

									$itemAno          = $Linha[1];
									$itemOrgao        = $Linha[2];
									$itemUnidade      = $Linha[3];
									$itemSequ         = $Linha[4];
									$itemData 		  = substr($Linha[5], 8, 2) . '/' . substr($Linha[5], 5, 2) . '/' . substr($Linha[5], 0, 4) . ' - ' . substr($Linha [5], 11, 5);
									$itemValor        = $Linha[6];
									$itemValorAnulado = $Linha[7];
									$itemValorPago    = $Linha[8];
									$itemDisponivel   = $Linha[9];
									$itemPar          = $Linha[10];

									if (is_null($itemValor)) {
										$itemValor=0;
									}

									if (is_null($itemValorAnulado)) {
										$itemValorAnulado=0;
									}

									if (is_null($itemValorPago)) {
										$itemValorPago=0;
									}

									if (is_null($itemDisponivel)) {
										$itemDisponivel=0;
									}

									# Pegar movimentações do empenho
									$sqlnf="SELECT SUM(VENTNFTOTA)
											FROM SFPC.TBENTRADANOTAFISCAL ENT
											WHERE (FENTNFCANC IS NULL OR FENTNFCANC = 'N') AND (ENT.CALMPOCODI, ENT.AENTNFANOE, ENT.CENTNFCODI) IN (SELECT	DISTINCT EMP.CALMPOCODI, EMP.AENTNFANOE, EMP.CENTNFCODI
																																					FROM	SFPC.TBNOTAFISCALEMPENHO EMP
																																					WHERE	EMP.ANFEMPANEM = ".$itemAno." AND
																																							EMP.CNFEMPOREM = ".$itemOrgao." AND
																																							EMP.CNFEMPUNEM = ".$itemUnidade." AND
																																							EMP.CNFEMPSEEM = ".$itemSequ.") ";
									$dbnf = Conexao();

									$resnf = $dbnf->query($sqlnf);

									if (PEAR::isError($resnf)) {
										EmailErroSQL(__FILE__."- Erro ao consultar um empenho", __FILE__, __LINE__, "Falha no SQL.", $sqlnf, $resnf);
										$dbnf->disconnect();
										exit(0);
									}
									$dbnf->disconnect();

									$LinhaNF = $resnf->fetchRow();
									$itemValorNF = $LinhaNF[0];

									if ($itemValorNF=="") {
										$itemValorNF=0;
									}

									$itemDisponivelTotal = $itemDisponivel - $itemValorNF;

									if ($itemDisponivelTotal < 0) {
										$itemDisponivelTotal=0;
									}
								?>
									<tr>
										<td class="texto" bgcolor="#F7F7D7" align="center"> <?php echo $itemAno; ?> </td>
										<td class="texto" bgcolor="#F7F7D7" align="center"><?php echo $itemOrgao; ?></td>
										<td class="texto" bgcolor="#F7F7D7" align="center"><?php echo $itemUnidade;?></td>
										<td class="texto" bgcolor="#F7F7D7" align="center"><?php echo $itemSequ;?></td>
										<td class="texto" bgcolor="#F7F7D7" align="center"><?php echo $itemData;?></td>
										<td class="texto" bgcolor="#F7F7D7" align="center"><?php echo converte_valor_estoques($itemValor);?></td>
										<td class="texto" bgcolor="#F7F7D7" align="center"><?php echo converte_valor_estoques($itemValorAnulado);?></td>
										<td class="texto" bgcolor="#F7F7D7" align="center"><?php echo converte_valor_estoques($itemValorPago);?></td>
										<td class="texto" bgcolor="#F7F7D7" align="center"><a href="javascript:AbreJanela('ConsEmpenhoDetalhe.php?Ano=<?php echo $itemAno; ?>&Orgao=<?php echo $itemOrgao; ?>&Unidade=<?php echo $itemUnidade; ?>&Sequencial=<?php echo $itemSequ; ?>',700,350);"><?php echo converte_valor_estoques($itemValorNF); ?></a></td> <!-- Movimentação empenho -->
										<td class="texto" bgcolor="#F7F7D7" align="center"><?php echo converte_valor_estoques($itemDisponivelTotal);?></td>
									</tr>
									<?php
									if ($Linha[0]=="S") {
										# Parcelas do empenho
										$sqlpar = "SELECT	TIP.FTPEMPSUEM, EMP.DEMPENANOO, EMP.CORGORCODI, EMP.CUNDORCODI, EMP.AEMPENSEQU, SUB.ASBEMPPARC,
															TO_CHAR(SUB.DSBEMPEMIS, 'YYYY-MM-DD HH24:MI:SS'), SUB.VSBEMPSUBE, SUB.VSBEMPANUL, SUB.VSBEMPPAGO,
															(NVL(SUB.VSBEMPSUBE,0) - (NVL(SUB.VSBEMPANUL,0) + NVL(SUB.VSBEMPPAGO,0)))
													FROM	SFCO.TBTIPOEMPENHO TIP, SFCO.TBEMPENHO EMP
															JOIN SFCO.TBSUBEMPENHO SUB ON (SUB.DEMPENANOO = EMP.DEMPENANOO AND EMP.AEMPENNUME = SUB.AEMPENNUME AND EMP.CORGORCODI = SUB.CORGORCODI AND EMP.CUNDORCODI = SUB.CUNDORCODI AND EMP.AEMPENSEQU = SUB.AEMPENSEQU)
													WHERE	EMP.DEMPENANOO = $itemAno AND
															EMP.CORGORCODI = $itemOrgao AND
															EMP.CUNDORCODI = $itemUnidade AND
															EMP.AEMPENSEQU = $itemSequ AND
															EMP.CTPEMPCODI = TIP.CTPEMPCODI
													ORDER BY SUB.ASBEMPPARC ";

										$respar  = $dbora->query($sqlpar);

										if (PEAR::isError($respar)) {
											$dbora->disconnect();
											EmailErroSQL(__FILE__."- Erro ao consultar um empenho", __FILE__, __LINE__, "Falha no SQL.", $sqlpar, $respar);
											exit;
										}
										$Linhapar = $respar->fetchRow();
										$cnt2 = 0;
									?>
									<tr>
										<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
											<tr>
												<td align="center" bgcolor="#F7F7F7" valign="middle" class="text" colspan="11">
													<b>Sub-Empenho(s)</b>
												</td>
											</tr>
											<tr>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Ano</td>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Órgão</td>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Unidade</td>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Sequencial</td>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Parcela</td>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Data de emissão</td>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Valor de empenho</td>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Valor anulado</td>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Valor pago</td>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Movimentações de Nota Fiscal</td>
												<td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Valor disponível</td>
											</tr>
											<?php
											
											$cnt2   = 0;

											while ((!is_null($Linhapar)) && ($cnt2<10000)) {
												$cnt2++;

												if ((is_null($ParcelaEmpenho)) || ($ParcelaEmpenho=="") || ($ParcelaEmpenho==$cnt2)) {
													$parAno          = $Linhapar[1];
													$parOrgao        = $Linhapar[2];
													$parUnidade      = $Linhapar[3];
													$parSequ         = $Linhapar[4];
													$parParcela      = $Linhapar[5];
													$parData 		 = substr($Linhapar[6], 8, 2) . '/' . substr($Linhapar[6], 5, 2) . '/' . substr($Linhapar[6], 0, 4) . ' - ' . substr($Linhapar[6], 11, 5);
													$parValor        = $Linhapar[7];
													$parValorAnulado = $Linhapar[8];
													$parValorPago    = $Linhapar[9];
													$parDisponivel   = $Linhapar[10];

													if (is_null($parValor)) {
														$parValor=0;
													}

													if (is_null($parValorAnulado)) {
														$parValorAnulado=0;
													}
												
													if (is_null($parValorPago)) {
														$parValorPago=0;
													}
												
													if (is_null($parDisponivel)) {
														$parDisponivel=0;
													}

													# Pegar movimentações da parcela do empenho
													$sqlnf="SELECT	SUM(VENTNFTOTA)
															FROM	SFPC.TBENTRADANOTAFISCAL ENT
															WHERE	(FENTNFCANC IS NULL OR FENTNFCANC = 'N') AND
																	(ENT.CALMPOCODI, ENT.AENTNFANOE, ENT.CENTNFCODI) IN (SELECT	DISTINCT EMP.CALMPOCODI, EMP.AENTNFANOE, EMP.CENTNFCODI
																														 FROM	SFPC.TBNOTAFISCALEMPENHO EMP
																														 WHERE	EMP.ANFEMPANEM = ".$itemAno." AND
																																EMP.CNFEMPOREM = ".$itemOrgao." AND
																																EMP.CNFEMPUNEM = ".$itemUnidade." AND
																																EMP.CNFEMPSEEM = ".$itemSequ." AND
																																EMP.CNFEMPPAEM = ".$parParcela.") ";
													$dbnf = Conexao();

													$resnf = $dbnf->query($sqlnf);

													if (PEAR::isError($resnf)) {
														$dbnf->disconnect();
														EmailErroSQL(__FILE__."- Erro ao consultar um empenho", __FILE__, __LINE__, "Falha no SQL.", $sqlnf, $resnf);
														exit(0);
													}
													$dbnf->disconnect();

													$LinhaNF = $resnf->fetchRow();
													$itemValorParNF = $LinhaNF[0];

													if ($itemValorParNF == "") {
														$itemValorParNF = 0;
													}
													$parDisponivelTotal = $parDisponivel - $itemValorParNF;

													if ($parDisponivelTotal < 0) {
														$parDisponivelTotal = 0;
													}
											?>
												<tr>
													<td class="texto" bgcolor="#E7F7E7" align="center"><?php echo $parAno; ?></td>
													<td class="texto" bgcolor="#E7F7E7" align="center"><?php echo $parOrgao; ?></td>
													<td class="texto" bgcolor="#E7F7E7" align="center"><?php echo $parUnidade; ?></td>
													<td class="texto" bgcolor="#E7F7E7" align="center"><?php echo $parSequ; ?></td>
													<td class="texto" bgcolor="#E7F7E7" align="center"><?php echo $parParcela; ?></td>
													<td class="texto" bgcolor="#E7F7E7" align="center"><?php echo $parData; ?></td>
													<td class="texto" bgcolor="#E7F7E7" align="center"><?php echo converte_valor_estoques($parValor); ?></td>
													<td class="texto" bgcolor="#E7F7E7" align="center"><?php echo converte_valor_estoques($parValorAnulado); ?></td>
													<td class="texto" bgcolor="#E7F7E7" align="center"><?php echo converte_valor_estoques($parValorPago); ?></td>
													<td class="texto" bgcolor="#E7F7E7" align="center"><a href="javascript:AbreJanela('ConsEmpenhoDetalhe.php?Ano=<?php echo $parAno; ?>&Orgao=<?php echo $parOrgao; ?>&Unidade=<?php echo $parUnidade; ?>&Sequencial=<?php echo $parSequ; ?>&Parcela=<?php echo $parParcela; ?>',700,350);"><?php echo converte_valor_estoques($itemValorParNF); ?></a></td> <!-- Movimentação sub-empenho -->
													<td class="texto" bgcolor="#E7F7E7" align="center"><?php echo converte_valor_estoques($parDisponivelTotal); ?></td>
												</tr>
											<?php
												}
												$Linhapar = $respar->fetchRow();
											}
											if ($cnt2 == 0) {
											?>
												<tr>
													<td colspan="11" bgcolor="#F7F7F7" width="1" align="left">Tipo do empenho determina sub-empenhos existentes, porém nenhum sub-empenho foi criado.</td>
												</tr>
											<?php
											}
											?>; 
									</tr>
									<?php
									}
									$Linha = $res->fetchRow();
								}
									?>
								</table>
								<?php
								$dbora->disconnect();
							}
						}
								?>
					</td>
				</tr>
				<!-- Fim do Corpo -->
			</table>
		</form>
	</body>
</html>